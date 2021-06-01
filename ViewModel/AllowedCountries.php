<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\ViewModel;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\AllowedCountries as DirectoryAllowedCountries;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AllowedCountries implements ArgumentInterface
{
    private DirectoryAllowedCountries $allowedCountries;
    private Country                   $country;

    public function __construct(
        Country $country,
        DirectoryAllowedCountries $allowedCountries)
    {
        $this->allowedCountries = $allowedCountries;
        $this->country = $country;
    }

    /**
     * provide country id and name to work with in cart
     *
     * @return array|null
     */
    public function getAllowedCountries(): ?array
    {
        $allowedCountries = $this->allowedCountries->getAllowedCountries("store");
        $countries = [];
        foreach ($allowedCountries as $allowedCountry) {
            $countries[$allowedCountry] = $this->country->loadByCode($allowedCountry)->getName();
        }

        uasort($countries, [$this, 'sortArray']);

        return $countries;
    }

    public function getAllowedCountriesJson(): string
    {
        return json_encode($this->getAllowedCountries());
    }

    /**
     * @param $a
     * @param $b
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function sortArray($a, $b): int
    {
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
}
