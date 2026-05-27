<?php
/**
 * Plugin Name: Newsletter by Hem
 * Plugin URI:  https://github.com/hem-inamdar/hem-newsletter
 * Description: A free forever newsletter plugin by Hem Inamdar. Easy email subscription, double opt-in confirmation, SMTP settings, and post broadcast — all from your WordPress dashboard.
 * Version:     1.1.6
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Author:      Hem Inamdar
 * Author URI:  https://heminamdar.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hem-newsletter
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HEM_NL_VERSION',     '1.1.6' );
define( 'HEM_NL_PLUGIN_FILE', __FILE__ );
define( 'HEM_NL_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'HEM_NL_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

require_once HEM_NL_PLUGIN_DIR . 'includes/class-hem-newsletter-db.php';
require_once HEM_NL_PLUGIN_DIR . 'includes/class-hem-newsletter-mailer.php';
require_once HEM_NL_PLUGIN_DIR . 'includes/class-hem-newsletter-subscriber.php';
require_once HEM_NL_PLUGIN_DIR . 'admin/class-hem-newsletter-admin.php';
require_once HEM_NL_PLUGIN_DIR . 'public/class-hem-newsletter-public.php';

register_activation_hook( __FILE__, array( 'Hem_Newsletter_DB', 'install' ) );

function hem_newsletter_init() {
    if ( get_option( 'hem_newsletter_version' ) !== HEM_NL_VERSION ) {
        Hem_Newsletter_DB::install();
    }
    new Hem_Newsletter_Admin();
    new Hem_Newsletter_Public();
}
add_action( 'plugins_loaded', 'hem_newsletter_init' );
