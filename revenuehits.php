<?php

/**
 * @package RevenueHits
 */
/*
Plugin Name: RevenueHits
Plugin URI: http://www.revenuehits.com/
Description: RevenueHits help publishers to generate more revenues with state of the art Contextual & Geo targeted Ad Serving technology.
Version: 6
Author: RevenueHits
Author URI: http://revenuehits.com/
License: GPLv2 or later
Text Domain: revenuehits
*/


// This plugin is deprecated

if (!defined('WPINC')) {
    die;
}

define('REVENUEHITS_VERSION', '6');
define('REVENUEHITS__MINIMUM_WP_VERSION', '1');
define('REVENUEHITS__PLUGIN_URL', plugin_dir_url(__FILE__));
define('REVENUEHITS__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REVENUEHITS_DELETE_LIMIT', 100000);

if (is_admin()) {
    require_once(REVENUEHITS__PLUGIN_DIR . 'includes/class.revenuehits-admin.php');
    add_action('plugins_loaded', array('Revenuehits_Admin', 'init'));
    add_action('admin_menu', 'revenuehits_admin_menu');
    add_action('admin_footer', array('Revenuehits_Admin', 'load_resources'));
    add_action('wp_head', 'revenuehits_ajaxurl');
} else {
    require_once(REVENUEHITS__PLUGIN_DIR . 'includes/class.revenuehits.php');
    add_action('wp', array('Revenuehits', 'init'));
}

function revenuehits_ajaxurl()
{
    print "<script type=\"text/javascript\">var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';</script>";
}

function revenuehits_admin_menu()
{
    add_menu_page('Revenuehits', 'Revenuehits', 'administrator', __FILE__, 'create_view', REVENUEHITS__PLUGIN_URL . 'img/revenuehits-icon.jpg');
    add_action('admin_init', 'register_my_setting');
}

function create_view()
{
    include REVENUEHITS__PLUGIN_DIR . 'view/template.php';
}

function register_my_setting()
{

    // Account/activation
    register_setting('revenuehits_settings', 'revenuehits_show');
    register_setting('revenuehits_settings', 'revenuehits_userid');
    register_setting('revenuehits_settings', 'revenuehits_password');
    register_setting('revenuehits_settings', 'revenuehits_exclude_pages');

    // Placements
    register_setting('revenuehits_settings', 'revenuehits_homepage');
    register_setting('revenuehits_settings', 'revenuehits_categories');
    register_setting('revenuehits_settings', 'revenuehits_posts');
    register_setting('revenuehits_settings', 'revenuehits_other');

    // Components
    register_setting('revenuehits_settings', 'revenuehits_position_footer');
    register_setting('revenuehits_settings', 'revenuehits_position_popunder');
    register_setting('revenuehits_settings', 'revenuehits_position_dialog');
    register_setting('revenuehits_settings', 'revenuehits_position_notifier');
    register_setting('revenuehits_settings', 'revenuehits_position_shadow_box');
    register_setting('revenuehits_settings', 'revenuehits_position_interstitial');
    register_setting('revenuehits_settings', 'revenuehits_position_topbanner');
    register_setting('revenuehits_settings', 'revenuehits_position_float');

    // Extra
    register_setting('revenuehits_settings', 'revenuehits_extra_dialog');
    register_setting('revenuehits_settings', 'revenuehits_extra_notifier');

}
