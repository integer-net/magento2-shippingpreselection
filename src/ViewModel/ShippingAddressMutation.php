<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\ViewModel;

use Magento\Directory\Model\Country;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Hyva\Theme\ViewModel\Cart\GraphQlQueries;

class ShippingAddressMutation implements ArgumentInterface
{
    private GraphQlQueries $graphQlQueries;

    public function __construct(
        GraphQlQueries $graphQlQueries)
    {
        $this->graphQlQueries = $graphQlQueries;
    }

    public function getMutateAddressQuery()
    {
        return 'setShippingAddressesOnCart(
                        input: {
                        cart_id: "${this.cartId}"
                        shipping_addresses: [
                            {
                                address: {
                                    firstname: "${addressDataFromCart.firstname}"
                                    lastname: "${addressDataFromCart.lastname}"
                                    street: "${addressDataFromCart.street}"
                                    city: "${addressDataFromCart.city}"
                                    region: "${addressDataFromCart.region.code}"
                                    country_code: "${countryCode}"
                                    telephone: "${addressDataFromCart.telephone}"
                                    postcode: "${addressDataFromCart.postcode}"
                                    save_in_address_book: false
                                }
                            }
                        ]
                    }
                ){
               cart {
                  ' . $this->graphQlQueries->getCartGraphQlQuery() . '
                }
            }';
    }
}
