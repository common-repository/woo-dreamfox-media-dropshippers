<?php
/*
Plugin Name: Woocommerce Dreamfox Media Dropshippers
Plugin URI: https://www.dreamfoxmedia.com/plugins/
Description: Dreamfox Media Woocommerce Dropshippers
Author: Dreamfoxmedia.com
Author URI: https://www.dreamfoxmedia.com
Version: 1.0.2
Text Domain: woocommerce-dfm-dropshippers
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

}

if ( is_plugin_active_for_network('woocommerce/woocommerce.php') || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    if ( !class_exists( 'DFM_Dropshippers' ) ) {
        require ( dirname(__FILE__).'/inc/class-dfm-dropshippers.php' );
    }
    $plugin = new DFM_Dropshippers();
    register_activation_hook(__FILE__, 'dfm_dropshippers_activate');
    register_deactivation_hook(__FILE__, 'dfm_dropshippers_deactivate');
}

/**
 * Activate the plugin
 */
function dfm_dropshippers_activate()
{
    $result = add_role('dfm_dropshipper', 'DFM Dropshipper', array(
        'show_dropshipper_widget' => true, //can see widget in dashboard
        'read' => true, // True allows that capability
        'edit_posts' => true,
    ));

    $domain_name =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
    // SET DEFAULTS
    $options = get_option('dfm_woocommerce_dropshippers_options');
    $options = dfm_prepare_active_options($options);
    update_option('dfm_woocommerce_dropshippers_options', $options);

}

/**
 * Deactivate the plugin
 */
function dfm_dropshippers_deactivate()
{
    remove_role('dfm_dropshipper');
}
?>