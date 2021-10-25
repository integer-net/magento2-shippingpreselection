<?php
declare(strict_types=1);

namespace IntegerNet\ShippingPreselection\ViewModel\Cart;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class GraphQlQueries implements ArgumentInterface
{
    /**
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCartGraphQlQuery()
    {
        return '
              total_quantity
              is_virtual
              items {
                id
                errors
                prices {
                  price {
                    value
                  }
                  row_total {
                    value
                    currency
                  }
                  row_total_incl_tax {
                    value
                    currency
                  }
                  price_incl_tax{
                    value
                  }
                }
                product_type
                product {
                  id
                  name
                  sku
                  small_image {
                    label
                    url
                  }
                  url_key
                  url_suffix
                  price_tiers {
                      quantity
                      final_price {
                        value
                      }
                      discount {
                        amount_off
                        percent_off
                      }
                  }
                }
                quantity
                ... on SimpleCartItem {
                  customizable_options {
                    label
                      values {
                        label
                        value
                        price {
                        value
                        type
                      }
                    }
                  }
                }
                ... on VirtualCartItem {
                  customizable_options {
                    label
                      values {
                        label
                        value
                        price {
                        value
                        type
                      }
                    }
                  }
                }
                ... on DownloadableCartItem {
                  customizable_options {
                    label
                      values {
                        label
                        value
                        price {
                        value
                        type
                      }
                    }
                  }
                }

                ... on ConfigurableCartItem {
                  configurable_options {
                    id
                    option_label
                    value_label
                  }
                }
                ... on BundleCartItem {
                  bundle_options {
                    id
                    label
                    values {
                      quantity
                      label
                    }
                  }
                  customizable_options {
                    label
                      values {
                        label
                        value
                        price {
                        value
                        type
                      }
                    }
                  }
                }
              }
              available_payment_methods {
                code
                title
              }
              selected_payment_method {
                code
                title
              }
              applied_coupons {
                code
              }
              billing_address {
                country {
                  code
                }
                region {
                  label
                  region_id
                }
                postcode
              }
              shipping_addresses {
                country {
                  code
                }
                region {
                  label
                  region_id
                }
                postcode
                selected_shipping_method {
                  amount {
                    value
                    currency
                  }
                  carrier_title
                  carrier_code
                  method_title
                  method_code
                }
                available_shipping_methods {
                  price_excl_tax {
                    value
                    currency
                  }
                  price_incl_tax {
                    value
                    currency
                  }
                  carrier_title
                  carrier_code
                  method_title
                  method_code
                }
              }
              prices {
                grand_total {
                  value
                  currency
                }
                subtotal_excluding_tax {
                  value
                  currency
                }
                subtotal_including_tax {
                  value
                  currency
                }
                applied_taxes {
                  amount {
                      value
                      currency
                  }
                  label
                }
                discounts {
                  amount {
                      value
                      currency
                  }
                  label
                }
              }
          ';
    }
}
