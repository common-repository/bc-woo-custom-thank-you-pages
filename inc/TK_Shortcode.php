<?php

/**
 * Contains functions that generate UI for the shortcode
 */

namespace BinaryCarpenter\BC_TK;

use BinaryCarpenter\BC_TK\Config as Config;
use WC_Order;

class TK_Shortcode
{

    const BCTK_FORMATTED_ORDER_TOTAL = 'bctk_formatted_order_total';
    const BCTK_ORDER_DETAILS = 'bctk_order_details';
    const BCTK_ORDER_NUMBER = 'bctk_order_number';
    const BCTK_ORDER_KEY = 'bctk_order_key';
    const BCTK_CUSTOMER_ID = 'bctk_customer_id';
    const BCTK_USER_ID = 'bctk_user_id';
    const BCTK_USER = 'bctk_user';
    const BCTK_BILLING_DETAILS = 'bctk_billing_details';
    const BCTK_BILLING_FIRST_NAME = 'bctk_billing_first_name';
    const BCTK_BILLING_LAST_NAME = 'bctk_billing_last_name';
    const BCTK_BILLING_COMPANY = 'bctk_billing_company';
    const BCTK_BILLING_ADDRESS_1 = 'bctk_billing_address_1';
    const BCTK_BILLING_ADDRESS_2 = 'bctk_billing_address_2';
    const BCTK_BILLING_CITY = 'bctk_billing_city';
    const BCTK_BILLING_STATE = 'bctk_billing_state';
    const BCTK_BILLING_POSTCODE = 'bctk_billing_postcode';
    const BCTK_BILLING_COUNTRY = 'bctk_billing_country';
    const BCTK_BILLING_EMAIL = 'bctk_billing_email';
    const BCTK_BILLING_PHONE = 'bctk_billing_phone';
    const BCTK_SHIPPING_FIRST_NAME = 'bctk_shipping_first_name';
    const BCTK_SHIPPING_LAST_NAME = 'bctk_shipping_last_name';
    const BCTK_SHIPPING_COMPANY = 'bctk_shipping_company';
    const BCTK_SHIPPING_ADDRESS_1 = 'bctk_shipping_address_1';
    const BCTK_SHIPPING_ADDRESS_2 = 'bctk_shipping_address_2';
    const BCTK_SHIPPING_CITY = 'bctk_shipping_city';
    const BCTK_SHIPPING_STATE = 'bctk_shipping_state';
    const BCTK_SHIPPING_POSTCODE = 'bctk_shipping_postcode';
    const BCTK_SHIPPING_COUNTRY = 'bctk_shipping_country';
    const BCTK_PAYMENT_METHOD = 'bctk_payment_method';
    const BCTK_PAYMENT_METHOD_TITLE = 'bctk_payment_method_title';
    const BCTK_TRANSACTION_ID = 'bctk_transaction_id';
    const BCTK_ORDER_ID = 'bctk_order_id';
    const BCTK_CUSTOMER_IP_ADDRESS = 'bctk_customer_ip_address';
    const BCTK_CUSTOMER_USER_AGENT = 'bctk_customer_user_agent';
    const BCTK_CREATED_VIA = 'bctk_created_via';
    const BCTK_CUSTOMER_NOTE = 'bctk_customer_note';
    const BCTK_DATE_COMPLETED = 'bctk_date_completed';
    const BCTK_DATE_PAID = 'bctk_date_paid';
    const BCTK_DATE_CREATED = 'bctk_date_created';
    const BCTK_CART_HASH = 'bctk_cart_hash';
    const BCTK_SHIPPING_ADDRESS_MAP_URL = 'bctk_shipping_address_map_url';
    const BCTK_FORMATTED_BILLING_FULL_NAME = 'bctk_formatted_billing_full_name';
    const BCTK_FORMATTED_SHIPPING_FULL_NAME = 'bctk_formatted_shipping_full_name';
    const BCTK_FORMATTED_BILLING_ADDRESS = 'bctk_formatted_billing_address';
    const BCTK_FORMATTED_SHIPPING_ADDRESS = 'bctk_formatted_shipping_address';
    const BCTK_DOWNLOADABLE_ITEMS = 'bctk_downloadable_items';
    const BCTK_CHECKOUT_PAYMENT_URL = 'bctk_checkout_payment_url';
    const BCTK_CHECKOUT_ORDER_RECEIVED_URL = 'bctk_checkout_order_received_url';
    const BCTK_CANCEL_ORDER_URL = 'bctk_cancel_order_url';
    const BCTK_CANCEL_ORDER_URL_RAW = 'bctk_cancel_order_url_raw';
    const BCTK_CANCEL_ENDPOINT = 'bctk_cancel_endpoint';
    const BCTK_VIEW_ORDER_URL = 'bctk_view_order_url';
    const BCTK_EDIT_ORDER_URL = 'bctk_edit_order_url';
    const BCTK_CUSTOMER_ORDER_NOTES = 'bctk_customer_order_notes';
    const BCTK_TOTAL_REFUNDED = 'bctk_total_refunded';
    const BCTK_TOTAL_TAX_REFUNDED = 'bctk_total_tax_refunded';
    const BCTK_TOTAL_SHIPPING_REFUNDED = 'bctk_total_shipping_refunded';
    const BCTK_ITEM_COUNT_REFUNDED = 'bctk_item_count_refunded';
    const BCTK_TOTAL_QTY_REFUNDED = 'bctk_total_qty_refunded';
    const BCTK_QTY_REFUNDED_FOR_ITEM = 'bctk_qty_refunded_for_item';
    const BCTK_TOTAL_REFUNDED_FOR_ITEM = 'bctk_total_refunded_for_item';
    const BCTK_TAX_REFUNDED_FOR_ITEM = 'bctk_tax_refunded_for_item';
    const BCTK_TOTAL_TAX_REFUNDED_BY_RATE_ID = 'bctk_total_tax_refunded_by_rate_id';
    const BCTK_REMAINING_REFUND_AMOUNT = 'bctk_remaining_refund_amount';
    const BCTK_REMAINING_REFUND_ITEMS = 'bctk_remaining_refund_items';
    const BCTK_PAYMENT_BACS_ACCOUNT_NUMBER = 'bctk_payment_bacs_account_number';
    const BCTK_PAYMENT_BACS_ACCOUNT_NAME = 'bctk_payment_bacs_account_name';
    const BCTK_PAYMENT_BACS_BANK_NAME = 'bctk_payment_bacs_bank_name';
    const BCTK_PAYMENT_BACS_ROUTING_NUMBER = 'bctk_payment_bacs_routing_number';
    const BCTK_PAYMENT_BACS_IBAN = 'bctk_payment_bacs_iban';
    const BCTK_PAYMENT_BACS_SWIFT = 'bctk_payment_bacs_swift';

    // const BCTK_ORDER_ITEM_TOTALS = 'bctk_order_item_totals';


    /**
     * get the order key from session, if available then return a WC_Order object upon request
     * @return WC_Order
     */
    public static function get_order()
    {

        $order_key = isset($_COOKIE[Config::SESSION_KEY]) ? $_COOKIE[Config::SESSION_KEY] : 0;


        if ($order_key === 0) {

            return null;
        }

        return new WC_Order(wc_get_order_id_by_order_key($order_key));
    }


    public static function bctk_formatted_order_total()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_formatted_order_total();
    }

    // public static function bctk_data()
    // {
    //     $order = self::get_order();
    //     if ($order == null) {
    //         return '';
    //     }
    //     return $order->get_data();
    // }

    // public static function bctk_changes()
    // {
    //     $order = self::get_order();
    //     if ($order == null) {
    //         return '';
    //     }
    //     return json_encode($order->get_changes());
    // }

    public static function bctk_order_number()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_order_number();
    }

    public static function bctk_order_key()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_order_key();
    }

    public static function bctk_customer_id()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_customer_id();
    }

    public static function bctk_user_id()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_user_id();
    }

    public static function bctk_user()
    {
        // $order = self::get_order();
        // if ($order == null) {
        //     return '';
        // }
        return '';

    }

    public static function bctk_billing_first_name()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_first_name();
    }

    public static function bctk_billing_last_name()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_last_name();
    }

    public static function bctk_billing_company()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_company();
    }

    public static function bctk_billing_address_1()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_address_1();
    }

    public static function bctk_billing_address_2()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_address_2();
    }

    public static function bctk_billing_city()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_city();
    }

    public static function bctk_billing_state()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_state();
    }

    public static function bctk_billing_postcode()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_postcode();
    }

    public static function bctk_billing_country()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_country();
    }

    public static function bctk_billing_email()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_email();
    }

    public static function bctk_billing_phone()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_billing_phone();
    }

    public static function bctk_billing_details()
    {
        $lines = "<div class='bctk-billing-details'><div>" . self::bctk_billing_first_name() . " " . self::bctk_billing_last_name() . "</div>" .
            "<div>" . self::bctk_billing_company() . "</div>" .
            "<div>" . self::bctk_billing_address_1() . "</div>" .
            "<div>" . self::bctk_billing_address_2() . "</div>" .
            "<div>" . self::bctk_billing_city() . " " . self::bctk_billing_country() . "</div>" .
            "<div>" . self::bctk_billing_phone() . "</div>" .
            "<div>" . self::bctk_billing_email() . "</div></div>";
        return $lines;
    }


    public static function bctk_shipping_first_name()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_first_name();
    }

    public static function bctk_shipping_last_name()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_last_name();
    }

    public static function bctk_shipping_company()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_company();
    }

    public static function bctk_shipping_address_1()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_address_1();
    }

    public static function bctk_shipping_address_2()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_address_2();
    }

    public static function bctk_qty_refunded_for_item()
    {
        return '';
    }

    public static function bctk_shipping_city()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_city();
    }

    public static function bctk_shipping_state()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_state();
    }

    public static function bctk_shipping_postcode()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_postcode();
    }

    public static function bctk_shipping_country()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_country();
    }

    public static function bctk_payment_method()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_payment_method();
    }

    public static function bctk_payment_method_title()
    {

        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_payment_method_title();
    }

    private static function bctk_get_bacs_details($atts)
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        if ($order->get_payment_method() == "bacs") {
            $bacs = get_option('woocommerce_bacs_accounts');
            if (!is_array($bacs) || empty($bacs)) {
                return '';
            }

            if (count($bacs) == 1) {
                return $bacs[0];
            }

            if (!isset($atts['position']) || !is_int(intval($atts['position']))) {
                return $bacs[0];
            }

            if (!isset($bacs[intval($atts['position'])])) {
                return $bacs[0];
            }

            return $bacs[intval($atts['position'])];
        }

        return [];
    }

    public static function bctk_payment_bacs_account_name($atts)
    {
        $bacs = self::bctk_get_bacs_details($atts);

        if (empty($bacs) || !isset($bacs['account_name'])) {
            return '';
        }

        return self::print_data($atts, $bacs['account_name']);

    }

    public static function bctk_payment_bacs_account_number($atts)
    {
        $bacs = self::bctk_get_bacs_details($atts);

        if (empty($bacs) || !isset($bacs['account_number'])) {
            return '';
        }

        return self::print_data($atts, $bacs['account_number']);

    }


    public static function bctk_payment_bacs_bank_name($atts)
    {
        $bacs = self::bctk_get_bacs_details($atts);

        if (empty($bacs) || !isset($bacs['bank_name'])) {
            return '';
        }

        return self::print_data($atts, $bacs['bank_name']);

    }


    public static function bctk_payment_bacs_routing_number($atts)
    {
        $bacs = self::bctk_get_bacs_details($atts);

        if (empty($bacs) || !isset($bacs['sort_code'])) {
            return '';
        }

        return self::print_data($atts, $bacs['sort_code']);

    }


    public static function bctk_payment_bacs_iban($atts)
    {
        $bacs = self::bctk_get_bacs_details($atts);

        if (empty($bacs) || !isset($bacs['iban'])) {
            return '';
        }

        return self::print_data($atts, $bacs['iban']);
    }

    public static function bctk_payment_bacs_swift($atts)
    {
        $bacs = self::bctk_get_bacs_details($atts);

        if (empty($bacs) || !isset($bacs['bic'])) {
            return '';
        }

        return self::print_data($atts, $bacs['bic']);
    }


    public static function bctk_order_id()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_id();
    }

    public static function bctk_transaction_id()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_transaction_id();
    }

    public static function bctk_customer_ip_address()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_customer_ip_address();
    }

    public static function bctk_customer_user_agent()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_customer_user_agent();
    }

    public static function bctk_created_via()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_created_via();
    }

    public static function bctk_customer_note()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_customer_note();
    }

    public static function bctk_date_completed()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_date_completed() == null ? '25' : $order->get_date_completed()->date_i18n();
    }


    public static function bctk_date_paid()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_date_paid() == null ? '2' : $order->get_date_paid()->date_i18n();
    }

    public static function bctk_date_created()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_date_created() == null ? '' : $order->get_date_created()->date_i18n();
    }

    public static function bctk_cart_hash()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_cart_hash();
    }


    public static function bctk_shipping_address_map_url()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_shipping_address_map_url();
    }

    public static function bctk_formatted_billing_full_name()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_formatted_billing_full_name();
    }

    public static function bctk_formatted_shipping_full_name()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_formatted_shipping_full_name();
    }

    public static function bctk_formatted_billing_address($atts)
    {
        $order = self::get_order();
        if ($order == null || !$order->has_billing_address()) {
            error_log('order is null or billing address is not available, cannot get formatted billing address');
            return '';
        }

        $billing_address = self::render_heading($atts);
        $billing_address .= $order->get_formatted_billing_address();

        return $billing_address;
    }

    public static function bctk_formatted_shipping_address($atts)
    {
        $order = self::get_order();
        if ($order == null || !$order->has_shipping_address()) {
            error_log('order is null or shipping address is not available, cannot get formatted shipping address');
            return '';
        }
        $shipping_address = self::render_heading($atts);

        $shipping_address .= $order->get_formatted_shipping_address();

        return $shipping_address;
    }

    public static function bctk_downloadable_items($atts)
    {
        $order = self::get_order();
        if ($order == null) {
            error_log('order is null, cannot get downloadable items');
            return '';
        }

        if (!$order->is_download_permitted() || !$order->has_downloadable_item()) {
            error_log('Download not permitted or not having downloadable items');
            return '';
        }

        $items = $order->get_downloadable_items();

        $heading = self::render_heading($atts);

        $itemHtml = '';

        foreach ($items as $item) {
            $itemHtml .= "<div class='bctk-downloadable-item'><a href='" . $item['download_url'] . "'>" . $item['download_name'] . "</a></div>";
        }

        return $heading . $itemHtml;

    }

    public static function bctk_checkout_payment_url()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_checkout_payment_url();
    }

    public static function bctk_checkout_order_received_url()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_checkout_order_received_url();
    }

    public static function bctk_cancel_order_url()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_cancel_order_url();
    }

    public static function bctk_cancel_order_url_raw()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_cancel_order_url_raw();
    }

    public static function bctk_cancel_endpoint()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_cancel_endpoint();
    }

    public static function bctk_view_order_url()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_view_order_url();
    }

    public static function bctk_edit_order_url()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_edit_order_url();
    }

    public static function bctk_customer_order_notes()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        $notes = $order->get_customer_order_notes();
        if (is_array($notes) && count($notes) > 0) {
            return implode(". ", $notes);
        }

        return '';
    }

    // public static function bctk_refunds()
    // {
    //     $order = self::get_order();
    //     if ($order == null) {
    //         return '';
    //     }
    //     return $order->get_refunds();
    // }

    public static function bctk_total_refunded()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_total_refunded();
    }

    public static function bctk_total_tax_refunded()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_total_tax_refunded();
    }

    public static function bctk_total_shipping_refunded()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_total_shipping_refunded();
    }

    public static function bctk_item_count_refunded()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_item_count_refunded();
    }

    public static function bctk_total_qty_refunded()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_total_qty_refunded();
    }

    public static function bctk_remaining_refund_amount()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_remaining_refund_amount();
    }

    public static function bctk_remaining_refund_items()
    {
        $order = self::get_order();
        if ($order == null) {
            return '';
        }
        return $order->get_remaining_refund_items();
    }

    public static function bctk_order_details($args)
    {

        if (self::get_order() == null)
            return '';

        if (wp_is_mobile()) {
            return self::print_order_details_mobile();

        }
        return self::print_order_details_full();

    }


    private static function get_order_lines_html($order, $is_mobile = false)
    {
        $html = '';
        foreach ($order->get_items() as $item_key => $item):
            ## Access Order Items data properties (in an array of values) ##
            $item_data = $item->get_data();

            $product_name = $item_data['name'];
            $quantity = $item_data['quantity'];
            $line_total = $item_data['total'] + ($item_data['total_tax'] > 0 ? $item_data['total_tax'] : 0);

            // Get data from The WC_product object using methods (examples)
            $product = $item->get_product(); // Get the WC_Product object
            if (is_object($product)) {
                $product_price = $product->get_price();
            } else {
                $product_price = "";
            }
            $template = $is_mobile ? '<tr><td>%1$s</td> <td>%2$s x %3$s</td> <td>%4$s</td> <td>%5$s</td> </tr>' : '<tr><td>%1$s</td> <td>%2$s</td> <td>%3$s</td> <td>%4$s</td> <td>%5$s</td>  </tr>';
            $html .= sprintf($template, $product_name, $quantity, wc_price($product_price), wc_price($item_data['total_tax']), wc_price($line_total));
        endforeach;

        return $html;
    }

    /**
     * @param $atts
     * @return string
     */
    public static function render_heading($atts): string
    {
        $atts = shortcode_atts(
            array(
                'heading_content' => '',
                'heading_level' => 'h1',
            ),
            $atts
        );

        $shipping_address = '';

        if (trim($atts['heading_content']) != '') {
            $shipping_address .= sprintf('<%1$s>%2$s</%1$s>', $atts['heading_level'], $atts['heading_content']);
        }
        return $shipping_address;
    }

    private static function print_data($atts, $mainString)
    {

        $prefix = isset($atts['prefix']) ? $atts['prefix'] : '';
        $suffix = isset($atts['suffix']) ? $atts['suffix'] : '';
        return sprintf($prefix . '%s' . $suffix, $mainString);
    }

    /**
     * @return string
     */

    public static function print_order_details_mobile()
    {
        $table_head = sprintf(
            '<thead><tr> <th>%1$s</th> <th>%2$s</th> <th>%3$s</th> <th>%4$s</th> </tr></thead>',
            __('Product', 'woocommerce'),
            __('Quantity', 'woocommerce') . ' x ' . __('Price', 'woocommerce'),
            __('Tax', 'woocommerce'),
            __('Total', 'woocommerce')
        );


        //get subtotal (total items, not including shipping)
        $subtotal_line = sprintf('<tr><td colspan="3">%1$s</td> <td>%2$s</td></tr>', __('Subtotal', 'woocommerce'), self::get_order()->get_subtotal_to_display());
        //shipping total line
        $shipping_line = sprintf('<tr><td colspan="3">%1$s</td> <td>%2$s</td></tr>', __('Shipping', 'woocommerce'), self::get_order()->get_shipping_to_display());


        //tax line

        $taxes = self::get_order()->get_tax_totals();
        $tax_line = '';
        if (count($taxes) > 0) {
            foreach ($taxes as $t) {
                $tax_line .= sprintf(
                    '<tr><td colspan="3">%1$s</td> <td>%2$s</td></tr>',
                    __($t->label, 'woocommerce'),
                    $t->formatted_amount
                );
            }
        }

        //total line
        $total_line = sprintf('<tr><td colspan="3">%1$s</td> <td>%2$s</td></tr>', __('Total', 'woocommerce'), self::get_order()->get_formatted_order_total());

        $table = sprintf(
            '<table>%1$s<tbody>%2$s %3$s %4$s %5$s %6$s</tbody></table></div>',
            $table_head,
            self::get_order_lines_html(self::get_order(), true),
            $subtotal_line,
            $shipping_line,
            $tax_line,
            $total_line
        );

        return '<div style="max-width: 100%; overflow-x: auto;">'  .$table . '</div>';
    }

    public static function print_order_details_full(): string
    {
        $table_head = sprintf(
            '<thead><tr> <th>%1$s</th> <th>%2$s</th> <th>%3$s</th> <th>%4$s</th> <th>%5$s</th> </tr></thead>',
            __('Product', 'woocommerce'),
            __('Quantity', 'woocommerce'),
            __('Price', 'woocommerce'),
            __('Tax', 'woocommerce'),
            __('Total', 'woocommerce')
        );


        //get subtotal (total items, not including shipping)
        $subtotal_line = sprintf('<tr><td colspan="4">%1$s</td> <td>%2$s</td></tr>', __('Subtotal', 'woocommerce'), self::get_order()->get_subtotal_to_display());
        //shipping total line
        $shipping_line = sprintf('<tr><td colspan="4">%1$s</td> <td>%2$s</td></tr>', __('Shipping', 'woocommerce'), self::get_order()->get_shipping_to_display());


        //tax line

        $taxes = self::get_order()->get_tax_totals();
        $tax_line = '';
        if (count($taxes) > 0) {
            foreach ($taxes as $t) {
                $tax_line .= sprintf(
                    '<tr><td colspan="4">%1$s</td> <td>%2$s</td></tr>',
                    __($t->label, 'woocommerce'),
                    $t->formatted_amount
                );
            }
        }


        //total line
        $total_line = sprintf('<tr><td colspan="4">%1$s</td> <td>%2$s</td></tr>', __('Total', 'woocommerce'), self::get_order()->get_formatted_order_total());

        return sprintf(
            '<table>%1$s<tbody>%2$s %3$s %4$s %5$s %6$s</tbody></table>',
            $table_head,
            self::get_order_lines_html(self::get_order(), false),
            $subtotal_line,
            $shipping_line,
            $tax_line,
            $total_line
        );
    }
}