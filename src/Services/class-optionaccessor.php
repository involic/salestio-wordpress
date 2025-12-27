<?php
/**
 * Option storage helper.
 *
 * @package Salestio
 */

namespace Salestio\Services;

/**
 * Option accessor class.
 */
class OptionAccessor {

	/**
	 * Option name for merchant secret.
	 */
	const MERCHANT_SECRET_KEY = 'salestio_merchant_secret';

	/**
	 * Option name for store ID.
	 */
	const STORE_ID_KEY = 'salestio_store_id';
	const AUTO_OPEN_KEY = 'salestio_auto_open';

	/**
	 * Persist the merchant secret.
	 *
	 * @param string $merchant_secret Merchant secret.
	 * @return bool
	 */
	public function set_merchant_secret( $merchant_secret ) {
		return update_option( self::MERCHANT_SECRET_KEY, $merchant_secret );
	}

	/**
	 * Get the merchant secret.
	 *
	 * @return string|false
	 */
	public function get_merchant_secret() {
		return get_option( self::MERCHANT_SECRET_KEY );
	}

	/**
	 * Persist the store ID.
	 *
	 * @param string $store_id Store ID.
	 * @return bool
	 */
	public function set_store_id( $store_id ) {
		return update_option( self::STORE_ID_KEY, $store_id );
	}

	/**
	 * Get the store ID.
	 *
	 * @return string|false
	 */
	public function get_store_id() {
		return get_option( self::STORE_ID_KEY );
	}

	/**
	 * Check whether stored credentials are missing.
	 *
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->get_merchant_secret() ) || empty( $this->get_store_id() );
	}

	/**
	 * Delete stored credentials.
	 *
	 * @return void
	 */
	public function delete_options() {
		delete_option( self::MERCHANT_SECRET_KEY );
		delete_option( self::STORE_ID_KEY );
		delete_option( self::AUTO_OPEN_KEY );
		delete_site_option( self::MERCHANT_SECRET_KEY );
		delete_site_option( self::STORE_ID_KEY );
		delete_site_option( self::AUTO_OPEN_KEY );
	}

	/**
	 * Persist the auto-open preference.
	 *
	 * @param bool $auto_open Whether to auto-open Salestio.
	 * @return bool
	 */
	public function set_auto_open( $auto_open ) {
		return update_option( self::AUTO_OPEN_KEY, $auto_open ? 'yes' : 'no' );
	}

	/**
	 * Get the auto-open preference.
	 *
	 * @return bool
	 */
	public function get_auto_open() {
		return 'yes' === get_option( self::AUTO_OPEN_KEY, 'no' );
	}
}
