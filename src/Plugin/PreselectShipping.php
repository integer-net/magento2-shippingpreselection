<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\Plugin;

use IntegerNet\ShippingPreselection\Service\AddressSetMockdata;
use IntegerNet\ShippingPreselection\Service\AddressResetConditions;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Quote\Model\ShippingAddressAssignment;

class PreselectShipping
{
    private ShippingMethodManagement $methodManagement;
    private AddressSetMockdata $addressSetMockData;
    private AddressResetConditions $addressReset;
    private ShippingAddressAssignment $addressAssignment;
    private ?Quote $quote;

    public function __construct(
        ShippingMethodManagement $methodManagement,
        AddressResetConditions $addressReset,
        AddressSetMockdata $addressSetMockData,
        ShippingAddressAssignment $addressAssignment
    ) {
        $this->methodManagement = $methodManagement;
        $this->addressSetMockData = $addressSetMockData;
        $this->addressReset = $addressReset;
        $this->addressAssignment = $addressAssignment;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $subject, Quote $result): Quote
    {
        $this->quote = $result;
        if (!$this->isPreselectionAllowed()) {
            return $this->quote;
        }
        $this->preselectShippingAddress();
        $this->preselectShippingMethod();
        return $this->quote;
    }
    
    public function shouldMockAddress(Address $address): bool
    {
        return (true !== $address->validate());
    }

    public function preselectShippingAddress(): void
    {
        $address = $this->quote->getShippingAddress();
        if ($this->shouldMockAddress($address)) {
            $this->addressSetMockData->setMockDataOnAddress($address);
            $this->addressAssignment->setAddress($this->quote, $address);
        }
    }
    
    public function preselectShippingMethod(): void
    {
        $this->quote->getShippingAddress()->requestShippingRates(); // load new rates
        if (!$rate = $this->getCheapestShippingRate()) {
            return;
        }
        try {
            $this->methodManagement->set(
                $this->quote->getId(),
                $rate->getCarrier(),
                $rate->getMethod()
            );
        } catch (\Exception $e) {
            $this->quote->addErrorInfo('error', null, $e->getCode(), $e->getMessage());
        }
    }
    
    public function getCheapestShippingRate(): ?Rate
    {
        $selectedRate = null;
        foreach ($this->getShippingRates() as $rate) {
            /** @var Rate $rate */
            if ($selectedRate === null || $rate->getPrice() < $selectedRate->getPrice()) {
                $selectedRate = $rate;
            }
        }
        return $selectedRate;
    }
    
    public function getShippingRates()
    {
        return $this->quote->getShippingAddress()->getShippingRatesCollection();
    }
    
    public function isPreselectionAllowed(): bool
    {
        return $this->validateShippingResetConditions() &&
            $this->validateQuoteConditions() &&
            $this->validateShippingConditions();
    }
    
    public function validateShippingResetConditions(): bool
    {
        return !$this->addressReset->isAddressResetRequest() &&
            !$this->addressReset->isAddressIgnoreRequest();
    }
    
    public function validateQuoteConditions(): bool
    {
        return !$this->quote->getIsVirtual() && $this->quote->getItemsCount();
    }
    
    public function validateShippingConditions(): bool
    {
        $address = $this->quote->getShippingAddress();
        $shippingIsFine = $address->validate() && !empty($address->getShippingMethod());
        return !$shippingIsFine;
    }
}
