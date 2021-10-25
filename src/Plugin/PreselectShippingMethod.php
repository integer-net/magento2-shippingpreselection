<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\Plugin;

use IntegerNet\ShippingPreselection\Service\AddressSetMockdata;
use IntegerNet\ShippingPreselection\Service\AddressResetConditions;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PreselectShippingMethod
{
    private ShippingMethodManagement $shippingMethodManagement;
    private AddressFactory           $addressFactory;
    private AddressSetMockdata       $addressSetMockdata;
    private AddressResetConditions   $addressResetConditions;

    public function __construct(
        ShippingMethodManagement $shippingMethodManagement,
        AddressFactory $addressFactory,
        AddressResetConditions $addressResetConditions,
        AddressSetMockdata $addressSetMockdata)
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->addressFactory = $addressFactory;
        $this->addressSetMockdata = $addressSetMockdata;
        $this->addressResetConditions = $addressResetConditions;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $subject, Quote $result): Quote
    {
        if (!$this->addressResetConditions->isAddressResetRequest() && !$this->addressResetConditions->isAddressIgnoreRequest()) {
            if (!$result->getIsVirtual() && $result->getItemsCount()) {
                $shippingAddress = $result->getShippingAddress();
                if (!$shippingAddress || !$shippingAddress->validate() || !$shippingAddress->getShippingMethod()) {
                    $this->prepareShippingAddress($shippingAddress, $result);
                }

                $this->prepareShippingRates($result);
            }
        }

        return $result;
    }

    /**
     * GraphQl requires fully valid address data to work with in cart, so we need to make up data if
     * it has not been set yet
     *
     * @param Address $quote
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareShippingAddress(Address $shippingAddress, Quote $quote): void
    {
        if (!$shippingAddress) {
            $shippingAddress = $this->addressFactory->create();
            $shippingAddress->setQuote($quote);
        }

        if ($shippingAddress->validate() !== true) {
            $this->addressSetMockdata->setMockDataOnAddress($shippingAddress);
        }
    }

    /**
     * try setting cheapest shipping rate available for customer
     *
     * @param Quote $quote
     */
    private function prepareShippingRates(Quote $quote): void
    {
        $quote->getShippingAddress()->requestShippingRates();
        $rates = $quote->getShippingAddress()->getShippingRatesCollection();

        /** @var Rate|null $selectedRate */
        $selectedRate = null;
        foreach ($rates as $rate) {
            /** @var Rate $rate */
            if ($selectedRate === null || $rate->getPrice() < $selectedRate->getPrice()) {
                $selectedRate = $rate;
            }
        }

        if ($selectedRate) {
            try {
                $this->shippingMethodManagement->set(
                    $quote->getId(),
                    $selectedRate->getCarrier(),
                    $selectedRate->getMethod()
                );
            } catch (\Exception $e) {
                $quote->addErrorInfo('error', null, $e->getCode(), $e->getMessage());
            }
        }
    }
}
