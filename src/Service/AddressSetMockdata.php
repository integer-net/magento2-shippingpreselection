<?php

declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\AddressInterface;

class AddressSetMockdata
{

    public const CONFIG_PATH_DEFAULT_COUNTRY_ID = 'general/country/default';
    public const CONFIG_PATH_DEFAULT_REGION_ID  = 'general/store_information/region_id';
    public const CONFIG_PATH_DEFAULT_POSTCODE   = 'general/store_information/postcode';
    public const CONFIG_PATH_MOCK_DATASET       = 'integernet/shipping_preselection/mock_data';

    private ScopeConfigInterface $storeConfig;

    public function __construct(ScopeConfigInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    public function setMockDataOnAddress(AddressInterface $address): void
    {
        $prefill = (string) $this->storeConfig->getValue(self::CONFIG_PATH_MOCK_DATASET, 'store');

        $address->setFirstname($address->getFirstname() ?: $prefill);
        $address->setLastname($address->getLastname() ?: $prefill);
        $address->setPostcode($address->getPostcode() ?: $this->storeConfig->getValue(self::CONFIG_PATH_DEFAULT_POSTCODE, 'store'));
        $address->setCity($address->getCity() ?: $prefill);
        $address->setTelephone($address->getTelephone() ?: $prefill);
        $address->setRegionId($address->getRegionId() ?: $this->storeConfig->getValue(self::CONFIG_PATH_DEFAULT_REGION_ID, 'store'));
        $address->setCountryId($address->getCountryId() ?: $this->storeConfig->getValue(self::CONFIG_PATH_DEFAULT_COUNTRY_ID, 'store'));
        $address->setStreet($this->mockStreet($address, $prefill));
    }

    /**
     * @return array<string>|string
     */
    private function mockStreet(AddressInterface $address, string $prefill)
    {
        if (is_array($address->getStreet()) && count($address->getStreet()) && $address->getStreet()[0] !== '') {
            return $address->getStreet();
        }
        if (is_string($address->getStreet()) && $address->getStreet() !== '') {
            return $address->getStreet();
        }
        return [$prefill];
    }
}
