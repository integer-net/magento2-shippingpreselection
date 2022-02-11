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

    public function __construct(
        ShippingMethodManagement $methodManagement,
        AddressResetConditions $addressReset,
        AddressSetMockdata $addressSetMockdata,
        ShippingAddressAssignment $addressAssignment
    ) {
        $this->methodManagement = $methodManagement;
        $this->addressSetMockData = $addressSetMockdata;
        $this->addressReset = $addressReset;
        $this->addressAssignment = $addressAssignment;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $subject, Quote $result): Quote
    {
        if (!$this->isPreselectionAllowed($result)) {
            return $result;
        }
        $address = $result->getShippingAddress();
        if ($this->shouldMockAddress($address)) {
            $this->addressSetMockData->setMockDataOnAddress($address);
            $this->addressAssignment->setAddress($result, $address);
        }
        $this->preselectShippingMethod($result);
        return $result;
    }
    
    public function shouldMockAddress(Address $address): bool
    {
        return (true !== $address->validate());
    }
    
    public function preselectShippingMethod(Quote $quote): void
    {
        $quote->getShippingAddress()->requestShippingRates(); // load new rates
        if (!$rate = $this->getCheapestShippingRate($quote)) {
            return;
        }
        try {
            $this->methodManagement->set(
                $quote->getId(),
                $rate->getCarrier(),
                $rate->getMethod()
            );
        } catch (\Exception $e) {
            $quote->addErrorInfo('error', null, $e->getCode(), $e->getMessage());
        }
    }
    
    public function getCheapestShippingRate(Quote $quote): ?Rate
    {
        $selectedRate = null;
        foreach ($this->getShippingRates($quote) as $rate) {
            /** @var Rate $rate */
            if ($selectedRate === null || $rate->getPrice() < $selectedRate->getPrice()) {
                $selectedRate = $rate;
            }
        }
        return $selectedRate;
    }
    
    public function getShippingRates(Quote $quote)
    {
        return $quote->getShippingAddress()->getShippingRatesCollection();
    }
    
    public function isPreselectionAllowed(Quote $quote): bool
    {
        return $this->validateShippingResetConditions() &&
            $this->validateQuoteConditions($quote) &&
            $this->validateShippingConditions($quote);
    }
    
    public function validateShippingResetConditions(): bool
    {
        return !$this->addressReset->isAddressResetRequest() &&
            !$this->addressReset->isAddressIgnoreRequest();
    }
    
    public function validateQuoteConditions(Quote $quote): bool
    {
        return !$quote->getIsVirtual() && $quote->getItemsCount();
    }
    
    public function validateShippingConditions(Quote $quote): bool
    {
        $address = $quote->getShippingAddress();
        $shippingIsFine = $address->validate() && !empty($address->getShippingMethod());
        return !$shippingIsFine;
    }
}
