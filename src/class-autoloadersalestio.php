<?php
/**
 * Autoloader for Salestio classes.
 *
 * @package Salestio
 */

namespace Salestio;

/**
 * Autoloader class.
 */
class AutoloaderSalestio {

	/**
	 * Autoloader instance.
	 *
	 * @var AutoloaderSalestio|null
	 */
	private static $instance = null;

	/**
	 * Plugin root path.
	 *
	 * @var string
	 */
	private $doc_root;

	/**
	 * Initialize the autoloader.
	 *
	 * @param string $doc_root Plugin root path.
	 * @return AutoloaderSalestio
	 */
	public static function init( $doc_root ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $doc_root );
		}

		return self::$instance;
	}

	/**
	 * AutoloaderSalestio constructor.
	 *
	 * @param string $doc_root Plugin root path.
	 */
	private function __construct( $doc_root ) {
		define( '_SALESTIO_AUTOLOADER_LOADED_', true );

		$this->doc_root = $doc_root;

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoload Salestio classes.
	 *
	 * @param string $class Class name to load.
	 * @return bool
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return false;
		}

		$relative_class = substr( $class, strlen( __NAMESPACE__ . '\\' ) );
		$class_parts    = explode( '\\', $relative_class );
		$class_name     = array_pop( $class_parts );

		$file_name = 'class-' . strtolower( $class_name ) . '.php';
		$base_dir  = $this->doc_root . '/src/';

		if ( ! empty( $class_parts ) ) {
			$base_dir .= implode( '/', $class_parts ) . '/';
		}

		$file = $base_dir . $file_name;

		if ( file_exists( $file ) ) {
			require_once $file;
			return true;
		}

		return false;
	}
}
