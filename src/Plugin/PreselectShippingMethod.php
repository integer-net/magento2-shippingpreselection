<?php
declare(strict_types=1);

// phpcs:disable PSR2.Methods.FunctionCallSignature.Indent

namespace IntegerNet\ShippingPreselection\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PreselectShippingMethod
{
    private ScopeConfigInterface     $storeConfig;
    private ShippingMethodManagement $shippingMethodManagement;
    private AddressFactory           $addressFactory;

    private const CONFIG_PATH_DEFAULT_COUNTRY_ID = 'general/country/default';
    private const CONFIG_PATH_DEFAULT_REGION_ID  = 'general/store_information/region_id';
    private const CONFIG_PATH_DEFAULT_POSTCODE   = 'general/store_information/postcode';
    private const CONFIG_PATH_MOCK_DATASET       = 'integernet/shipping_preselection/mock_data';

    public function __construct(
        ScopeConfigInterface $storeConfig,
        ShippingMethodManagement $shippingMethodManagement,
        AddressFactory $addressFactory)
    {
        $this->storeConfig = $storeConfig;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $subject, Quote $result): Quote
    {
        if (!$result->getIsVirtual() && $result->getItemsCount()) {
            if (!$result->getShippingAddress()
                || $result->getShippingAddress() && !$result->getShippingAddress()->getShippingMethod()) {
                $this->prepareShippingAddress($result);
            }

            $this->prepareShippingRates($result);
        }

        return $result;
    }

    /**
     * GraphQl requires fully valid address data to work with in cart, so we need to make up data if
     * it has not been set yet
     *
     * @param Quote $quote
     * @return Quote
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareShippingAddress(Quote $quote): Quote
    {
        $address = $quote->getShippingAddress();

        if (!$address) {
            $address = $this->addressFactory->create();
            $address->setQuote($quote);
        }

        if ($address->validate() !== true) {
            $prefill = $this->storeConfig->getValue(self::CONFIG_PATH_MOCK_DATASET, 'store');

            $address->setFirstname($address->getFirstname() ?: $prefill);
            $address->setLastname($address->getLastname() ?: $prefill);
            $address->setPostcode(
                $address->getPostcode() ?: $this->storeConfig->getValue(self::CONFIG_PATH_DEFAULT_POSTCODE, 'store')
            );
            $address->setCity($address->getCity() ?: $prefill);
            $address->setTelephone($address->getTelephone() ?: $prefill);
            $address->setRegion(
                $address->getRegion() ?: $this->storeConfig->getValue(self::CONFIG_PATH_DEFAULT_REGION_ID, 'store')
            );
            $address->setCountryId(
                $address->getData('country_id')
                    ?: $this->storeConfig->getValue(
                    self::CONFIG_PATH_DEFAULT_COUNTRY_ID,
                    'store'
                )
            );
            $address->setStreet(
                (is_array($address->getStreet()) && count($address->getStreet()) && $address->getStreet()[0] !== '')
                || is_string($address->getStreet()) && strlen($address->getStreet()) ? $address->getStreet()
                    : [$prefill]
            );
        }

        return $quote;
    }

    /**
     * try setting cheapest shipping rate available for customer
     *
     * @param Quote $quote
     * @return Quote
     */
    private function prepareShippingRates(Quote $quote): Quote
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

        return $quote;
    }
}
