<?php
/**
 * HMAC generator service.
 *
 * @package Salestio
 */

namespace Salestio\Services;

/**
 * HMAC generator class.
 */
class HmacGenerator {

	/**
	 * Option accessor used for stored secrets.
	 *
	 * @var OptionAccessor
	 */
	private $option_accessor;

	/**
	 * HmacGenerator constructor.
	 *
	 * @param OptionAccessor $option_accessor Option accessor.
	 */
	public function __construct( OptionAccessor $option_accessor ) {
		$this->option_accessor = $option_accessor;
	}

	/**
	 * Get the shared secret.
	 *
	 * @return string|false
	 */
	private function get_shared_secret() {
		return $this->option_accessor->get_merchant_secret();
	}

	/**
	 * Validate a signature.
	 *
	 * @param string $signature Signature to validate.
	 * @param array  $params    Parameters to sign.
	 * @return bool
	 */
	public function is_valid( $signature, array $params ) {
		return $this->generate_hmac( $params ) === $signature;
	}

	/**
	 * Return list of generated params.
	 *
	 * @param string $store_url Store URL.
	 * @param string $store_id  Store ID.
	 * @return array
	 */
	public function generate_params( $store_url, $store_id ) {
		$timestamp = time();

		return array(
			'shop'    => $store_url,
			'storeId' => $store_id,
			'time'    => $timestamp,
			'hmac'    => $this->generate_hmac(
				array(
					'shop'    => $store_url,
					'storeId' => $store_id,
					'time'    => $timestamp,
				)
			),
		);
	}

	/**
	 * Build a HMAC signature.
	 *
	 * @param array $params Parameters to sign.
	 * @return string
	 */
	private function generate_hmac( array $params ) {
		$signature_parts = array();

		foreach ( $params as $key => $value ) {
			$signature_parts[] = $key . '=' . $value;
		}

		natsort( $signature_parts );

		return hash_hmac( 'sha256', implode( '&', $signature_parts ), $this->get_shared_secret() );
	}
}
