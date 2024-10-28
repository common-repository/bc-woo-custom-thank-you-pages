<?php

/**
 * Plugin Name: BC Woo Custom Thank You Pages
 * Plugin URI: https://www.binarycarpenter.com/app/bc-thank-you-page-builder-for-woocommerce/
 * Description: Helps you create custom thank you pages for products, categories
 * Version: 1.4.18
 * Author: WooCommerce & WordPress Tutorials
 * Author URI: https://www.binarycarpenter.com
 * License: GPL2
 * Text Domain: bc-custom-thank-you
 * Tested up to: 6.6.2.
 * WC requires at least: 3.0.0
 * WC tested up to: 9.3.3
 */


namespace BinaryCarpenter\BC_TK;

include_once 'inc/bc_core.php';
include_once 'inc/TK_Shortcode.php';
include_once 'inc/Options_Name.php';
include_once 'inc/BC_Options.php';
include_once 'inc/BC_Options_Form.php';
include_once 'inc/BC_Static_UI.php';
include_once 'inc/Config.php';
include_once 'inc/Activation.php';


use BinaryCarpenter\BC_TK\Activation as Activation;
use BinaryCarpenter\BC_TK\BC_Static_UI as StaticUI;
use BinaryCarpenter\BC_TK\Config as Config;
use BinaryCarpenter\BC_TK\OptionsName as Oname;
use BinaryCarpenter\BC_TK\TK_Shortcode as Shortcode;
use WC_Order;
use WC_Product;

class Initiator
{
    private static $instance;

    public static function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new Initiator();

        return self::$instance;
    }

    const OPTION_NAME = 'bctk_option'; //option name to store the option in wp

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_to_bc'));
        $this->shortcodes();


        add_action('admin_enqueue_scripts', array(&$this, 'load_scripts_styles_backend')); // Load backend script


        add_action('template_redirect', array($this, 'redirect_pages'));

        add_action('wp_ajax_' . BC_Options_Form::BC_OPTION_COMMON_AJAX_ACTION, array(BC_Options_Form::class, 'handle_post_save_options'));

        add_action('init', array($this, 'globalize_options'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'action_links'));
        add_action('wp_ajax_bc_tk_activate_license', array(Activation::class, 'activation_callback'));

        add_action('before_woocommerce_init', function () {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            }
        });
        if (isset($_GET['bctk']) && isset($_GET['key'])) {
            add_filter('woocommerce_is_order_received_page', '__return_true', 99);
        }

        add_action('woocommerce_thankyou', array($this, 'thank_you_page_auto_complete'));
    }

    public function thank_you_page_auto_complete($order_id)
    {
        if (!$order_id) {
            error_log("order id is empty");
        }
        global $bcw_cp_options;
        $option = $bcw_cp_options;

        if (!$option->get_bool(Oname::AUTO_COMPLETE_ORDER)) {
            error_log("auto complete order is not enabled");
            return;
        }

        $order = new WC_Order($order_id);
        $order->update_status('completed');
    }

    public function action_links($links)
    {
        $custom_links = array();
        $custom_links[] = '<a href="' . admin_url('admin.php?page=' . Config::PLUGIN_SLUG) . '">' . __('Get started', Config::PLUGIN_TEXT_DOMAIN) . '</a>';
        $custom_links[] = '<a target="_blank" href="https://tickets.binarycarpenter.com/open.php">' . __('Supports', Config::PLUGIN_TEXT_DOMAIN) . '</a>';

        if ($this->has_new_update()) {
            $custom_links[] = '<a style="color: #eb6d00; font-weight: bold;" target="_blank" href="' . Config::PRO_DOWNLOAD_LATEST_URL . '">' . __('Update available! Click here', Config::PLUGIN_TEXT_DOMAIN) . '</a>';
        }
        return array_merge($custom_links, $links);
    }

    public function has_new_update()
    {
        //only check for pro user since free users has wordpress done it for them
        $response = wp_remote_get(Config::PRO_NEW_VERSION_CHECK);
        if (is_wp_error($response)) {
            error_log("error checking for new version for wp thank you page builder");
            return false;
        }

        try {
            $body = json_decode(wp_remote_retrieve_body($response));
            error_log("body is " . print_r($body, true));
            if (!isset($body->versionNumber)) {
                error_log("error checking for new version for wp thank you page builder");
                return false;
            }

            $latest_version_code = $body->versionNumber;

            error_log("latest version code is " . $latest_version_code);

            if ($latest_version_code > Config::PLUGIN_VERSION_NUMBER) {
                error_log("new version found");
                return true;
            }
        } catch (\Exception $e) {
            error_log("error checking for new version for wp thank you page builder");
            return false;
        }

        return false;
    }

    public function globalize_options()
    {
        global $bcw_cp_options;

        $page_options = BC_Options::get_all_options(self::OPTION_NAME);


        $option_id = 0;
        if ($page_options->have_posts())
            $option_id = $page_options->get_posts()[0]->ID;

        //if there isn't any option set, return the default template
        if ($option_id == 0)
            return;


        $bcw_cp_options = new BC_Options(self::OPTION_NAME, $option_id);
    }

    //load css,
    public function load_scripts_styles_backend()
    {
        global $current_screen;
        if (stripos($current_screen->base, Config::PLUGIN_SLUG) !== false) {
            wp_enqueue_script(Config::PLUGIN_SLUG . '_admin_scripts', plugins_url('bundle/js/backend-bundle.js', __FILE__), array('jquery', 'underscore'), false, false);
            wp_enqueue_style(Config::PLUGIN_SLUG . '_admin_styles', plugins_url('bundle/css/backend.css', __FILE__), array());
        }
    }


    /**
     * -- Redirect the customers to appropriate thank you page
     * -- The thank you page will be based on the characteristic of the first product in cart
     * 1. Get the product ID
     * 2. Check if there is a thank you page associated with the product
     * 3. If there is a page, redirect customer to that page
     * 4. If there isn't a page for this product, get its categories
     * 5. Check if there is a page associated with the categories (stop and the first found)
     * 6. If found, redirect customer to that page
     * 7. If not found, find the generic page and redirect to that page
     * 8. If generic page not found, return normal page
     */
    function redirect_pages()
    {
        global $bcw_cp_options;
        $option = $bcw_cp_options;

        if (!$option) {
            error_log("error redirecting pages because $option is null");
            return;
        }


        //if the customer is going to the thank you page
        if (is_wc_endpoint_url('order-received')) {
            if (empty($_GET['key']))
                return;

            //check if the cart is empty
            if (!WC()->cart->is_empty()) {
                //clear the cart
                WC()->cart->empty_cart();
            }

            $order_key = sanitize_text_field($_GET['key']);

            error_log("logging order key: " . $order_key);

            setcookie(Config::SESSION_KEY, $order_key, (time() + 3600), "/");

            $order = new WC_Order(wc_get_order_id_by_order_key($order_key));

            $products = $order->get_items();

            $first_product = null;
            //get the first product in cart to get its thank you page
            foreach ($products as $product) {
                $first_product = $product->get_data();
                break;
            }
            $first_product_id = $first_product['product_id'];


            //get the default thank you page
            $thank_you_page_id = $option->get_int(Oname::GENERAL_THANK_YOU_PAGE);

            error_log('general thank you page ' . $thank_you_page_id);

            $product_thank_you_pages = $option->get_array(Oname::PER_PRODUCT_THANK_YOU_PAGE);
            $category_thank_you_pages = $option->get_array(Oname::PER_CATEGORY_THANK_YOU_PAGE);

            $first_product = new WC_Product($first_product_id);


            $product_cats_ids = $first_product->get_category_ids();

            //if the product has a specific thank you page set for it, then take that thank you page
            if (isset($product_thank_you_pages["$first_product_id"])) {
                $thank_you_page_id = $product_thank_you_pages["$first_product_id"];
            } else {
                //in case there isn't a thank you page set for the product, we
                //search for a thank you page that set to one of its category
                foreach ($product_cats_ids as $product_cat_id) {
                    if (isset($category_thank_you_pages["$product_cat_id"])) {
                        $thank_you_page_id = $category_thank_you_pages["$product_cat_id"];
                        break;
                    }
                }
            }

            error_log("final thank you page id " . $thank_you_page_id);


            if ($thank_you_page_id > 0) {
                $thank_you_page_url = add_query_arg(['key' => $order_key, 'bctk' => 1], get_permalink($thank_you_page_id));

                error_log("apply filters thank you page is: " . $thank_you_page_url);
                wp_redirect($thank_you_page_url, 301);
                exit;
            }
        }
    }

    /**
     * Add menu page
     */
    public function add_menu_to_bc()
    {

        $core = new Core();
        $core->admin_menu();
        add_submenu_page(
            Core::MENU_SLUG,
            __(Config::PLUGIN_NAME, Config::PLUGIN_TEXT_DOMAIN),
            __('<img style="width: 14px; height: 14px;" src="data:image/png;base64,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" > ' . Config::PLUGIN_MENU_NAME, Config::PLUGIN_TEXT_DOMAIN),
            'manage_options',
            Config::PLUGIN_SLUG,
            array($this, 'setting_page_ui')
        );
    }


    public function shortcodes()
    {
        add_shortcode(Shortcode::BCTK_FORMATTED_ORDER_TOTAL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_formatted_order_total'));
        add_shortcode(Shortcode::BCTK_ORDER_DETAILS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_order_details'));
        add_shortcode(Shortcode::BCTK_ORDER_NUMBER, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_order_number'));
        add_shortcode(Shortcode::BCTK_ORDER_KEY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_order_key'));
        add_shortcode(Shortcode::BCTK_CUSTOMER_ID, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_customer_id'));
        add_shortcode(Shortcode::BCTK_USER_ID, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_user_id'));
        add_shortcode(Shortcode::BCTK_USER, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_user'));
        add_shortcode(Shortcode::BCTK_BILLING_DETAILS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_details'));
        add_shortcode(Shortcode::BCTK_BILLING_FIRST_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_first_name'));
        add_shortcode(Shortcode::BCTK_BILLING_LAST_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_last_name'));
        add_shortcode(Shortcode::BCTK_BILLING_COMPANY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_company'));
        add_shortcode(Shortcode::BCTK_BILLING_ADDRESS_1, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_address_1'));
        add_shortcode(Shortcode::BCTK_BILLING_ADDRESS_2, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_address_2'));
        add_shortcode(Shortcode::BCTK_BILLING_CITY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_city'));
        add_shortcode(Shortcode::BCTK_BILLING_STATE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_state'));
        add_shortcode(Shortcode::BCTK_BILLING_POSTCODE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_postcode'));
        add_shortcode(Shortcode::BCTK_BILLING_COUNTRY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_country'));
        add_shortcode(Shortcode::BCTK_BILLING_EMAIL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_email'));
        add_shortcode(Shortcode::BCTK_BILLING_PHONE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_billing_phone'));
        add_shortcode(Shortcode::BCTK_SHIPPING_FIRST_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_first_name'));
        add_shortcode(Shortcode::BCTK_SHIPPING_LAST_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_last_name'));
        add_shortcode(Shortcode::BCTK_SHIPPING_COMPANY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_company'));
        add_shortcode(Shortcode::BCTK_SHIPPING_ADDRESS_1, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_address_1'));
        add_shortcode(Shortcode::BCTK_SHIPPING_ADDRESS_2, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_address_2'));
        add_shortcode(Shortcode::BCTK_SHIPPING_CITY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_city'));
        add_shortcode(Shortcode::BCTK_SHIPPING_STATE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_state'));
        add_shortcode(Shortcode::BCTK_SHIPPING_POSTCODE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_postcode'));
        add_shortcode(Shortcode::BCTK_SHIPPING_COUNTRY, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_country'));
        add_shortcode(Shortcode::BCTK_PAYMENT_METHOD, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_method'));
        add_shortcode(Shortcode::BCTK_PAYMENT_METHOD_TITLE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_method_title'));
        add_shortcode(Shortcode::BCTK_TRANSACTION_ID, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_transaction_id'));
        add_shortcode(Shortcode::BCTK_ORDER_ID, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_order_id'));
        add_shortcode(Shortcode::BCTK_CUSTOMER_IP_ADDRESS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_customer_ip_address'));
        add_shortcode(Shortcode::BCTK_CUSTOMER_USER_AGENT, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_customer_user_agent'));
        add_shortcode(Shortcode::BCTK_CREATED_VIA, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_created_via'));
        add_shortcode(Shortcode::BCTK_CUSTOMER_NOTE, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_customer_note'));
        add_shortcode(Shortcode::BCTK_DATE_COMPLETED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_date_completed'));
        add_shortcode(Shortcode::BCTK_DATE_PAID, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_date_paid'));
        add_shortcode(Shortcode::BCTK_DATE_CREATED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_date_created'));
        add_shortcode(Shortcode::BCTK_CART_HASH, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_cart_hash'));
        // add_shortcode(Shortcode::BCTK_ADDRESS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_address'));
        add_shortcode(Shortcode::BCTK_SHIPPING_ADDRESS_MAP_URL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_shipping_address_map_url'));
        add_shortcode(Shortcode::BCTK_FORMATTED_BILLING_FULL_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_formatted_billing_full_name'));
        add_shortcode(Shortcode::BCTK_FORMATTED_SHIPPING_FULL_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_formatted_shipping_full_name'));
        add_shortcode(Shortcode::BCTK_FORMATTED_BILLING_ADDRESS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_formatted_billing_address'));
        add_shortcode(Shortcode::BCTK_FORMATTED_SHIPPING_ADDRESS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_formatted_shipping_address'));
        add_shortcode(Shortcode::BCTK_DOWNLOADABLE_ITEMS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_downloadable_items'));
        add_shortcode(Shortcode::BCTK_CHECKOUT_PAYMENT_URL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_checkout_payment_url'));
        add_shortcode(Shortcode::BCTK_CHECKOUT_ORDER_RECEIVED_URL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_checkout_order_received_url'));
        add_shortcode(Shortcode::BCTK_CANCEL_ORDER_URL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_cancel_order_url'));
        add_shortcode(Shortcode::BCTK_CANCEL_ORDER_URL_RAW, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_cancel_order_url_raw'));
        add_shortcode(Shortcode::BCTK_CANCEL_ENDPOINT, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_cancel_endpoint'));
        add_shortcode(Shortcode::BCTK_VIEW_ORDER_URL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_view_order_url'));
        add_shortcode(Shortcode::BCTK_EDIT_ORDER_URL, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_edit_order_url'));
        add_shortcode(Shortcode::BCTK_CUSTOMER_ORDER_NOTES, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_customer_order_notes'));
        // add_shortcode(Shortcode::BCTK_REFUNDS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_refunds'));
        add_shortcode(Shortcode::BCTK_REMAINING_REFUND_ITEMS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_remaining_refund_items'));
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_ACCOUNT_NUMBER, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_account'));
        // add_shortcode(Shortcode::BCTK_ORDER_ITEM_TOTALS, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_order_item_totals'));
        add_shortcode(Shortcode::BCTK_TOTAL_REFUNDED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_total_refunded'));

        add_shortcode(Shortcode::BCTK_TOTAL_TAX_REFUNDED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_total_tax_refunded'));
        add_shortcode(Shortcode::BCTK_TOTAL_SHIPPING_REFUNDED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_total_shipping_refunded'));
        add_shortcode(Shortcode::BCTK_ITEM_COUNT_REFUNDED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_item_count_refunded'));
        add_shortcode(Shortcode::BCTK_TOTAL_QTY_REFUNDED, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_total_qty_refunded'));
        // add_shortcode(Shortcode::BCTK_QTY_REFUNDED_FOR_ITEM, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_qty_refunded_for_item'));
        // add_shortcode(Shortcode::BCTK_TOTAL_REFUNDED_FOR_ITEM, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_total_refunded_for_item'));
        // add_shortcode(Shortcode::BCTK_TAX_REFUNDED_FOR_ITEM, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_tax_refunded_for_item'));
        // add_shortcode(Shortcode::BCTK_TOTAL_TAX_REFUNDED_BY_RATE_ID, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_total_tax_refunded_by_rate_id'));
        add_shortcode(Shortcode::BCTK_REMAINING_REFUND_AMOUNT, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_remaining_refund_amount'));

        //BACS
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_ACCOUNT_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_account_name'));
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_ACCOUNT_NUMBER, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_account_number'));
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_BANK_NAME, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_bank_name'));
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_ROUTING_NUMBER, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_routing_number'));
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_IBAN, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_iban'));
        add_shortcode(Shortcode::BCTK_PAYMENT_BACS_SWIFT, array('BinaryCarpenter\BC_TK\TK_Shortcode', 'bctk_payment_bacs_swift'));
    }

    public function setting_page_ui()
    {


?>

        <div class="bc-root">
            <form>
                <?php

                //there is only one option post for this plugin

                $all_options = BC_Options::get_all_options(self::OPTION_NAME);

                $option_id = 0;
                if ($all_options->have_posts())
                    $option_id = $all_options->get_posts()[0]->ID;


                $option_form = new BC_Options_Form(self::OPTION_NAME, $option_id);

                $pages = get_pages();
                $pages_select = array();

                $pages_select[""] = ""; //add a default option
                foreach ($pages as $page) {
                    $pages_select["$page->ID"] = $page->post_title;
                }

                $thank_you_tab = array();

                $thank_you_tab[] = $option_form->card_section('Select a page as a common thank you page', array(
                    $option_form->select(Oname::GENERAL_THANK_YOU_PAGE, $pages_select, '')
                ), false);


                $cat_args = array(
                    'hide_empty' => false,
                );

                $product_categories = get_terms('product_cat', $cat_args);

                $product_categories_select = array();

                foreach ($product_categories as $category) {
                    $product_categories_select["$category->term_id"] = $category->name;
                }


                $thank_you_tab[] =
                    $option_form->card_section('Specify thank you page per category', array(

                        $option_form->key_select_select(Oname::PER_CATEGORY_THANK_YOU_PAGE, $product_categories_select, $pages_select, 'Pick product', 'Pick thank you page')

                    ), false);


                $products = wc_get_products(array(
                    'posts_per_page' => -1,
                ));

                $products_select = array();

                foreach ($products as $product) {

                    $products_select[$product->get_id()] = $product->get_name();
                }


                if (!Config::IS_PRO) {
                    $thank_you_tab[] = StaticUI::notice(
                        'Thank you per product is available in the pro version only.',
                        'string',
                        false,
                        false
                    );

                    $thank_you_tab[] = StaticUI::link('https://www.binarycarpenter.com/thankyou-page-single', 'Click to upgrade now');
                }
                $thank_you_tab[] =
                    $option_form->card_section('Specify thank you page per product', array(
                        $option_form->key_select_select(Oname::PER_PRODUCT_THANK_YOU_PAGE, $products_select, $pages_select, 'Pick product', 'Pick thank you page', !Config::IS_PRO)
                    ), false);
                //activation section
                if (!Config::IS_PRO) {
                    $thank_you_tab[] = StaticUI::notice('Auto complete order is available in the pro version only.', 'string', false, false);
                    $thank_you_tab[] = StaticUI::link('https://www.binarycarpenter.com/thankyou-page-single', 'Click to upgrade now');
                }
                $thank_you_tab[] = $option_form->card_section('Auto complete order', [
                    StaticUI::notice('This feature will automatically complete the order after the customer is redirected to the thank you page. Virtual product order will not auto complete. Check this to have your virtual orders auto completed.', 'string', false, false),
                    $option_form->checkbox(Oname::AUTO_COMPLETE_ORDER, !Config::IS_PRO, 'Auto complete order',)
                ], false);

                if (Config::IS_PRO) {
                    $activation_result = Activation::activate();
                    $activation_html = '';
                    if (!$activation_result['message'] == 'NO_LICENSE_KEY') {
                        $activation_html = StaticUI::notice($activation_result['message'], 'info', false, false);
                    } else if ($activation_result['status'] == 'success') {
                        $activation_html = StaticUI::notice($activation_result['message'], 'info', false, false);
                    } else {
                        //display activation form
                        $activation_html = '<div>
                                                        <label class="" for="">Enter your license key</label>
                                                        <br>
                                                        <input class="bc-uk-input" value="" type="text" id="license-key" placeholder="Your license key">
                                                        <p></p>
                                                </div>
                                                <input type="hidden" name="bc-tk-activate-license-nonce" value="' . wp_create_nonce('bc-tk-activate-license-nonce') . '" />
                                                <button id="activate-license-button" class="bc-uk-button-primary bc-uk-button">Activate license</button>';
                    }
                    $thank_you_tab[] =
                        $option_form->card_section('Plugin activation', array(

                            $activation_html

                        ), false);
                }

                $shop_page = array();
                $shop_page[] = $option_form->card_section('Use this page as my shop page instead', array(
                    $option_form->select(Oname::SHOP_PAGE, $pages_select, '')
                ), false);


                $product_category_pages = array();

                $product_category_pages[] =
                    $option_form->card_section('Select custom pages for your product categories', array(

                        $option_form->key_select_select(Oname::CUSTOM_PAGES_PER_CATEGORY, $product_categories_select, $pages_select, 'Pick product', 'Pick thank you page')

                    ), false);
                $shortcode_tab = array('
               <ul>
               <li>bctk_formatted_order_total</li>
<li>bctk_order_details</li>
<li>bctk_order_details_with_tax</li>
<li>bctk_order_number</li>
<li>bctk_order_key</li>
<li>bctk_customer_id</li>
<li>bctk_user_id</li>
<li>bctk_user</li>
<li>bctk_billing_details</li>
<li>bctk_billing_first_name</li>
<li>bctk_billing_last_name</li>
<li>bctk_billing_company</li>
<li>bctk_billing_address_1</li>
<li>bctk_billing_address_2</li>
<li>bctk_billing_city</li>
<li>bctk_billing_state</li>
<li>bctk_billing_postcode</li>
<li>bctk_billing_country</li>
<li>bctk_billing_email</li>
<li>bctk_billing_phone</li>
<li>bctk_shipping_first_name</li>
<li>bctk_shipping_last_name</li>
<li>bctk_shipping_company</li>
<li>bctk_shipping_address_1</li>
<li>bctk_shipping_address_2</li>
<li>bctk_shipping_city</li>
<li>bctk_shipping_state</li>
<li>bctk_shipping_postcode</li>
<li>bctk_shipping_country</li>
<li>bctk_payment_method</li>
<li>bctk_payment_method_title</li>
<li>bctk_transaction_id</li>
<li>bctk_order_id</li>
<li>bctk_customer_ip_address</li>
<li>bctk_customer_user_agent</li>
<li>bctk_created_via</li>
<li>bctk_customer_note</li>
<li>bctk_date_completed</li>
<li>bctk_date_paid</li>
<li>bctk_date_created</li>
<li>bctk_cart_hash</li>
<li>bctk_shipping_address_map_url</li>
<li>bctk_formatted_billing_full_name</li>
<li>bctk_formatted_shipping_full_name</li>
<li>bctk_formatted_billing_address</li>
<li>bctk_formatted_shipping_address</li>
<li>bctk_downloadable_items</li>
<li>bctk_checkout_payment_url</li>
<li>bctk_checkout_order_received_url</li>
<li>bctk_cancel_order_url</li>
<li>bctk_cancel_order_url_raw</li>
<li>bctk_cancel_endpoint</li>
<li>bctk_view_order_url</li>
<li>bctk_edit_order_url</li>
<li>bctk_customer_order_notes</li>
<li>bctk_total_refunded</li>
<li>bctk_total_tax_refunded</li>
<li>bctk_total_shipping_refunded</li>
<li>bctk_item_count_refunded</li>
<li>bctk_total_qty_refunded</li>
<li>bctk_qty_refunded_for_item</li>
<li>bctk_total_refunded_for_item</li>
<li>bctk_tax_refunded_for_item</li>
<li>bctk_total_tax_refunded_by_rate_id</li>
<li>bctk_remaining_refund_amount</li>
<li>bctk_remaining_refund_items</li>
<li>bctk_payment_bacs_account_number</li>
<li>bctk_payment_bacs_account_name</li>
<li>bctk_payment_bacs_bank_name</li>
<li>bctk_payment_bacs_routing_number</li>
<li>bctk_payment_bacs_iban</li>
<li>bctk_payment_bacs_swift</li>
               </ul> 
                ');

                StaticUI::tabs(
                    array(
                        array(
                            'title' => 'Thank you pages',
                            'content' => array(StaticUI::tabs(
                                array(
                                    array(
                                        'title' => 'Settings',
                                        'content' => $thank_you_tab
                                    ),
                                    array(
                                        'title' => 'Tutorial',
                                        'content' => array('<h1>Please watch the videos first. It is very important</h1>', '<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PL6rw2AEN42EplL69LxwbDBRVucyvWWQFC" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>', '<p>List of shortcodes you can use: <a href="https://www.binarycarpenter.com/list-of-shortcodes-in-bc-woo-thank-you-page-builder/" target="_blank">Shortcode list</a></a></p>')
                                    ),
                                    array(
                                        'title' => 'Shortcode list',
                                        'content' => $shortcode_tab
                                    )
                                ),
                                false
                            ))
                        )
                    ),
                    true
                );

                $option_form->setting_fields();
                $option_form->js_post_form();

                $option_form->submit_button('Save settings');
                ?>
            </form>
        </div>

<?php
    }
}


/**
 * Check if WooCommerce is activated
 */
if (!function_exists('is_woocommerce_activated')) {
    function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
}

add_action('plugin_loaded', function () {
    if (is_woocommerce_activated())
        Initiator::get_instance();
});
