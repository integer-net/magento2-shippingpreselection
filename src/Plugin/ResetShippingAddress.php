<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use IntegerNet\ShippingPreselection\Service\AddressUnsetMockdata;
use IntegerNet\ShippingPreselection\Service\AddressResetConditions;

class ResetShippingAddress
{

    private AddressUnsetMockdata   $addressUnsetMockdata;
    private AddressResetConditions $addressResetConditions;

    public function __construct(
        AddressUnsetMockdata $addressUnsetMockdata,
        AddressResetConditions $addressResetConditions)
    {
        $this->addressUnsetMockdata = $addressUnsetMockdata;
        $this->addressResetConditions = $addressResetConditions;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $subject, Quote $result): Quote
    {
        if ($this->addressResetConditions->isAddressResetRequest()) {
            if (!$result->getIsVirtual() && $result->getItemsCount()) {
                $shippingAddress = $result->getShippingAddress();

                if ($this->addressUnsetMockdata->isMockedAddress($shippingAddress)) {
                    $this->addressUnsetMockdata->resetShippingAddress($result);
                } else {
                    $this->addressUnsetMockdata->checkForEmptyAddressFields($shippingAddress);
                }
            }
        }

        return $result;
    }
}
