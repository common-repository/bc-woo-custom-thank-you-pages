<?php

/**
 * Created by PhpStorm.
 * User: MYN
 * Date: 5/9/2019
 * Time: 8:57 AM
 */

namespace BinaryCarpenter\BC_TK;

class Config
{
    const PLUGIN_NAME = 'Woo Custom Thank You Pages';
    const PLUGIN_MENU_NAME = 'Woo Custom Thank You Pages';
    const PLUGIN_SLUG = 'bc_woo_custom_tk';
    const PLUGIN_TEXT_DOMAIN = 'bc_woo_custom_tk';
    const PLUGIN_OPTION_NAME = 'bc_woo_custom_tk_option_name';
    const PLUGIN_COMMON_HANDLER = 'bc_woo_tk_handler'; //handler when enqueuing the scripts, style
    const SESSION_KEY = 'bc_tk_session_key';
    const KEY_CHECK_OPTION = 'bc_woo_custom_tk_check_option_key';
    const LICENSE_KEY_OPTION = 'bc_woo_custom_tk_stored_license_key';
    const PLUGIN_VERSION_NUMBER = 112;
    const OPTION_NAME = 'bc_menu_bar_cart_option_name';
    const IS_PRO = false;
    const LICENSE_CHECK_URL = "https://api.gotkey.io/public/activate/30837999853190265244496741031/14346c08-0020-4bba-bbdb-f1f1fae14f8f/30895721584086733288781299651";
    const PRO_NEW_VERSION_CHECK = "https://api.gotkey.io/public/find-release/product/30895721584086733288781299651";
    const PRO_DOWNLOAD_LATEST_URL = "https://gotkey.io/download-release/30837999853190265244496741031/30895721584086733288781299651";
}
