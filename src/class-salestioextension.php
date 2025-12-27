<?php
/**
 * Plugin bootstrapper.
 *
 * @package Salestio
 */

namespace Salestio;

use Salestio\Hooks\ActivateHook;
use Salestio\Hooks\DeactivateHook;
use Salestio\WooCommerce\Initializer;

/**
 * Plugin bootstrapper.
 */
class SalestioExtension {

	/**
	 * Singleton instance.
	 *
	 * @var SalestioExtension|null
	 */
	private static $instance = null;

	/**
	 * Initialize the plugin.
	 *
	 * @param string $main_plugin_filename Main plugin file path.
	 * @return SalestioExtension
	 */
	public static function init( $main_plugin_filename ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
			register_activation_hook( $main_plugin_filename, array( new ActivateHook(), 'execute' ) );
			register_deactivation_hook( $main_plugin_filename, array( new DeactivateHook(), 'execute' ) );
			add_action( 'init', array( new Initializer(), 'init_plugin' ) );
		}

		return self::$instance;
	}
}
