<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong

namespace IntegerNet\ShippingPreselection\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;

class AddressResetConditions
{
    private const MOCKDATA_UNSET_URLS  = 'integernet/shipping_preselection/mock_unset_urls';
    private const MOCKDATA_IGNORE_URLS = 'integernet/shipping_preselection/mock_ignore_urls';

    private Http                 $http;
    private ScopeConfigInterface $storeConfig;
    private                      $resetUrls  = null;
    private                      $ignoreUrls = null;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $http)
    {
        $this->http = $http;
        $this->storeConfig = $scopeConfig;
    }

    public function isAddressResetRequest(): bool
    {
        return in_array($this->http->getFullActionName(), $this->getResetUrls());
    }

    public function isAddressIgnoreRequest(): bool
    {
        return in_array($this->http->getFullActionName(), $this->getIgnoreUrls());
    }

    public function getResetUrls(): ?array
    {
        if (!$this->resetUrls) {
            $this->resetUrls = explode(',', $this->storeConfig->getValue(self::MOCKDATA_UNSET_URLS));
        }

        return $this->resetUrls;
    }

    public function getIgnoreUrls(): ?array
    {
        if (!$this->ignoreUrls) {
            $this->ignoreUrls = explode(',', $this->storeConfig->getValue(self::MOCKDATA_IGNORE_URLS));
        }

        return $this->ignoreUrls;
    }
}
