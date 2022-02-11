<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\ShippingAddressAssignment;
use IntegerNet\ShippingPreselection\Service\AddressUnsetMockdata;
use IntegerNet\ShippingPreselection\Service\AddressResetConditions;

class UnselectShipping
{
    private AddressUnsetMockdata $addressUnsetMockData;
    private AddressResetConditions $addressReset;
    private ShippingAddressAssignment $addressAssignment;
    private AddressFactory $addressFactory;

    public function __construct(
        AddressUnsetMockdata $addressUnsetMockdata,
        AddressResetConditions $addressReset,
        ShippingAddressAssignment $addressAssignment,
        AddressFactory $addressFactory
    ) {
        $this->addressUnsetMockData = $addressUnsetMockdata;
        $this->addressReset = $addressReset;
        $this->addressAssignment = $addressAssignment;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $subject, Quote $result): Quote
    {
        $isResetRequest = $this->addressReset->isAddressResetRequest();
        $quoteIsValid = !$result->getIsVirtual() && $result->getItemsCount();
        if (!$isResetRequest || !$quoteIsValid) {
            return $result;
        }
        
        $address = $result->getShippingAddress();
        if ($this->addressUnsetMockData->isMockedAddress($address)) {
            $this->addressAssignment->setAddress($result, $this->getNewShippingAddress(), true); // deletion included
        } else {
            $this->addressUnsetMockData->checkForEmptyAddressFields($address);
            $this->unsetShippingMethod($address);
            $this->addressAssignment->setAddress($result, $address);
        }
        return $result;
    }
    
    public function getNewShippingAddress(): Address
    {
        return $this->addressFactory->create()->setAddressType(Address::TYPE_SHIPPING);
    }
    
    public function unsetShippingMethod(Address $address): void
    {
        $address->setShippingAmount(0)->setBaseShippingAmount(0)->setShippingMethod('')->setShippingDescription('');
    }
}
