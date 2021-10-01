<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong

namespace IntegerNet\ShippingPreselection\Service;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class AddressUnsetMockdata
{
    public $customerAttributesToCheckForMockData = [
        AddressInterface::FIRSTNAME,
        AddressInterface::LASTNAME,
        AddressInterface::STREET,
        AddressInterface::CITY,
        AddressInterface::TELEPHONE,
    ];

    public $customerAttributesToCheckForNullData = [
        AddressInterface::REGION_ID,
        AddressInterface::REGION,
    ];

    private ScopeConfigInterface $storeConfig;

    public function __construct(ScopeConfigInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    public function isMockedAddress(Address $address): bool
    {
        $prefill = $this->storeConfig->getValue(AddressSetMockdata::CONFIG_PATH_MOCK_DATASET, 'store');

        $matchesNeeded = count($this->customerAttributesToCheckForMockData);
        $matches = 0;

        foreach ($this->customerAttributesToCheckForMockData as $attributeCode) {
            if ($attributeCode === 'street') {
                $matches = $matches + (int)(is_string($address->getStreet()) && $address->getStreet() === $prefill || is_array($address->getStreet()) && count($address->getStreet()) && $address->getStreet()[0] === $prefill);
            } else {
                $matches = $matches + (int)($address->getData($attributeCode) === $prefill);
            }
        }

        return $matches === $matchesNeeded;
    }

    public function checkForEmptyAddressFields(Address $address): void
    {
        /* ensure attributes filled with string "null" are emptied. edge case when starting paypal express checkout, returning to cart and switching the delivery country */
        $customerAttributes =
            array_merge($this->customerAttributesToCheckForMockData, $this->customerAttributesToCheckForNullData);
        foreach ($customerAttributes as $attributeCode) {
            if ($address->getData($attributeCode) === 'null') {
                $address->setData($attributeCode, '');
            }
        }
    }

    public function resetShippingAddress(Quote $quote): void
    {
        $quote->getShippingAddress()->delete();
    }
}
