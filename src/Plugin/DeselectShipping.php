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

class DeselectShipping
{
    private AddressUnsetMockdata $addressUnsetMockData;
    private AddressResetConditions $addressReset;
    private ShippingAddressAssignment $addressAssignment;
    private AddressFactory $addressFactory;
    private ?Quote $quote;

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
        $this->quote = $result;
        if (!$this->isDeselectionAllowed()) {
            return $this->quote;
        }
        $address = $this->quote->getShippingAddress();
        if ($this->addressUnsetMockData->isMockedAddress($address)) {
            $this->addressAssignment->setAddress($this->quote, $this->getNewAddress(), true);
        } else {
            $this->addressUnsetMockData->checkForEmptyAddressFields($address);
            $this->unsetShippingMethod($address);
            $this->addressAssignment->setAddress($this->quote, $address);
        }
        return $this->quote;
    }

    public function isDeselectionAllowed(): bool
    {
        $isResetRequest = $this->addressReset->isAddressResetRequest();
        $quoteIsValid = !$this->quote->getIsVirtual() && $this->quote->getItemsCount();
        return $isResetRequest && $quoteIsValid;
    }
    
    public function getNewAddress(): Address
    {
        return $this->addressFactory->create()->setAddressType(Address::TYPE_SHIPPING);
    }
    
    public function unsetShippingMethod(Address $address): void
    {
        $address->setShippingAmount(0)
            ->setBaseShippingAmount(0)
            ->setShippingMethod('')
            ->setShippingDescription('');
    }
}
