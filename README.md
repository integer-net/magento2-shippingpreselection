# IntegerNet_ShippingPreselection Magento Module
<div align="center">

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
![Supported Magento Versions][ico-compatibility]

[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Maintainability][ico-maintainability]][link-maintainability]
</div>

---

This module provides methods to preselect a shipping method upon quote creation of the customer. It fetches all available countries from Magento Config and will preselect the default country/region/postcode of the current storeview.



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

## Usage

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Testing

### Unit Tests

```
./vendor/bin/phpunit tests/unit
```

### Magento Integration Tests

0. Configure test database in `dev/tests/integration/etc/install-config-mysql.php`. [Read more in the Magento docs.](https://devdocs.magento.com/guides/v2.4/test/integration/integration_test_execution.html) 

1. Copy `tests/integration/phpunit.xml.dist` from the package to `dev/tests/integration/phpunit.xml` in your Magento installation.

2. In that directory, run
    ``` bash
    ../../../vendor/bin/phpunit
    ```


## Security

If you discover any security related issues, please email lb@integer-net.de instead of using the issue tracker.

## Credits

- [Lisa Buchholz][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/integer-net/magento2-shippingpreselection
.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/integer-net/magento2-shippingpreselection
/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/integer-net/magento2-shippingpreselection
?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/integer-net/magento2-shippingpreselection
.svg?style=flat-square
[ico-maintainability]: https://img.shields.io/codeclimate/maintainability/integer-net/magento2-shippingpreselection
?style=flat-square
[ico-compatibility]: https://img.shields.io/badge/magento-2.4-brightgreen.svg?logo=magento&longCache=true&style=flat-square

[link-packagist]: https://packagist.org/packages/integer-net/magento2-shippingpreselection

[link-travis]: https://travis-ci.org/integer-net/magento2-shippingpreselection

[link-scrutinizer]: https://scrutinizer-ci.com/g/integer-net/magento2-shippingpreselection
/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/integer-net/magento2-shippingpreselection

[link-maintainability]: https://codeclimate.com/github/integer-net/magento2-shippingpreselection

[link-author]: https://github.com/lbuchholz
[link-contributors]: ../../contributors
