<?php
/**
 * Plugin Name: Salestio
 * Plugin URI: https://salest.io/woocommerce-integration
 * Description: Connect WooCommerce to Salestio to link your store with Amazon, eBay, Etsy, and other marketplaces.
 * Version: 1.2.1
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * Tested up to: 6.9
 * Author: Salestio
 * Author URI: https://salest.io/
 * Developer: Salestio
 * Developer URI: https://salest.io/
 * Text Domain: salestio
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Salestio
 */

defined( 'ABSPATH' ) || exit;

define( 'SALESTIO_VERSION', '1.2.1' );
define( 'SALESTIO_PLUGIN_FILE', __FILE__ );
define( 'SALESTIO_PLUGIN_DIR', __DIR__ );

// Load core packages and the autoloader.
require_once __DIR__ . '/src/class-autoloadersalestio.php';

if ( ! \Salestio\AutoloaderSalestio::init( __DIR__ ) ) {
	return;
}

\Salestio\SalestioExtension::init( __FILE__ );
