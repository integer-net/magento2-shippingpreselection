<div align="center">

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
![Supported Magento Versions][ico-compatibility]

[![Maintainability][ico-maintainability]][link-maintainability]
</div>

# IntegerNet ShippingPreselection (AutoShipping)

This module preselects the cheapest shipping method by filling shipping address with mock data and default country/region/postcode of the current storeview.

Upon entering checkout, PayPal Express or PayOne pages, the mock data is removed from shipping address.



## Installation

1. Install it into your Magento 2 project with composer:
    ```
    composer require integer-net/magento2-shippingpreselection

    ```

2. Enable module
    ```
    bin/magento setup:upgrade
    ```

## Configuration

In general, make sure your configuration settings meet requirements:

- postcode, region and country set in General > Store Information
- all available countries need to have at least one shipping method available
- available countries cannot have mandatory region setting

1) Add selectedShippingCountry select to `Mage_Checkout::cart.phtml`


```
     <?= $block->getChildHtml('shipping_country') ?>
 
```
2. Add shipping country script to `Mage_Checkout::cart/js/cart.phtml`

```
     updateCartDataDependencies() {
         [...]
         this.selectedShippingCountry = this.cartData && this.cartData.shipping_addresses && this.cartData.shipping_addresses[0] && this.cartData.shipping_addresses[0].country && this.cartData.shipping_addresses[0].country.code || null
     },
     <?= $block->getChildHtml('shipping_country_js') ?>
```


3) Set config value for the mock data `integernet/shipping_preselection/mock_data` to custom value if desired

4) If you have an altered cart query for GraphQl, you need to override `IntegerNet\ShippingPreselection\ViewModel\ShippingAddressMutation` accordingly.


.

Please mind: *The template provided is supposed to work with table rates / one shipping method.*

If you have several shipping methods and through changes made in cart - like quantity changes - the best available method changes, this will not be recognized by the module (because a shipping method is already in place). You can solve this by checking client-side in the cart, or adding a check backend-wise. 

## Extending configuration

Override config values for integernet/shipping_preselection/mock_clearance_urls or hook into the service to modify behaviour for pages where the shipping address should be (un-)mocked.

## Known issues

**1. When aborting PayPal Express and return to cart, the shipping method isn't displayed anymore**

Paypal doesn't properly set the telephone address attribute, as it is called differently in its response. You can make the telephone field nullable in a `etc/schema.graphql` of your own


    interface CartAddressInterface {
        telephone: String
    }
    
    input CartAddressInput {
        telephone: String
    }




## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@integer-net.de instead of using the issue tracker.

## Credits

- [integer_net GmbH][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/integer-net/magento2-shippingpreselection.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-maintainability]: https://img.shields.io/codeclimate/maintainability/integer-net/magento2-shippingpreselection?style=flat-square
[ico-compatibility]: https://img.shields.io/badge/magento-2.4-brightgreen.svg?logo=magento&longCache=true&style=flat-square

[link-packagist]: https://packagist.org/packages/integer-net/magento2-shippingpreselection
[link-maintainability]: https://codeclimate.com/github/integer-net/magento2-shippingpreselection
[link-author]: https://github.com/integer_net
[link-contributors]: ../../contributors



[ico-version]: https://img.shields.io/packagist/v/integer-net/magento2-shippingpreselection.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-maintainability]: https://img.shields.io/codeclimate/maintainability/integer-net/magento2-shippingpreselection?style=flat-square
[ico-compatibility]: https://img.shields.io/badge/magento-2.4-brightgreen.svg?logo=magento&longCache=true&style=flat-square

[link-packagist]: https://packagist.org/packages/integer-net/magento2-shippingpreselection
[link-maintainability]: https://codeclimate.com/github/integer-net/magento2-shippingpreselection
[link-author]: https://github.com/lbuchholz
[link-contributors]: ../../contributors

