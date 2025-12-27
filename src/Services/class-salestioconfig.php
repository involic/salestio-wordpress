<?php
/**
 * Configuration loader.
 *
 * @package Salestio
 */

namespace Salestio\Services;

use ArrayAccess;
use LogicException;

/**
 * Configuration class.
 */
class SalestioConfig implements ArrayAccess {

	/**
	 * Loaded configuration values.
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 * SalestioConfig constructor.
	 */
	public function __construct() {
		$config = array();

		if ( file_exists( __DIR__ . '/../../etc/parameters.dev.ini' ) ) {
			$config = parse_ini_file( __DIR__ . '/../../etc/parameters.dev.ini' );
		}

		$config += parse_ini_file( __DIR__ . '/../../etc/parameters.ini' );

		foreach ( $config as $offset => $value ) {
			$this->offsetSet( $offset, $value );
		}
	}

	/**
	 * Set a config value.
	 *
	 * @param string $offset Config key.
	 * @param mixed  $value  Config value.
	 * @throws LogicException When the config key is missing.
	 * @return void
	 */
	public function offsetSet( $offset, $value ) {
		if ( null === $offset ) {
			throw new LogicException( 'Config parameter should have name and value' );
		}

		$this->config[ (string) $offset ] = $value;
	}

	/**
	 * Check whether a config value exists.
	 *
	 * @param string $offset Config key.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->config[ $offset ] );
	}

	/**
	 * Remove a config value.
	 *
	 * @param string $offset Config key.
	 * @return void
	 */
	public function offsetUnset( $offset ) {
		unset( $this->config[ $offset ] );
	}

	/**
	 * Get a config value.
	 *
	 * @param string $offset Config key.
	 * @return mixed|null
	 */
	public function offsetGet( $offset ) {
		return isset( $this->config[ $offset ] ) ? $this->config[ $offset ] : null;
	}
}
