<?php
/**
 * Salestio user provisioning.
 *
 * @package Salestio
 */

namespace Salestio\Services;

/**
 * Salestio user generator.
 */
class SalestioUserGenerator {

	/**
	 * Option accessor.
	 *
	 * @var OptionAccessor
	 */
	private $option_accessor;

	/**
	 * Plugin configuration.
	 *
	 * @var SalestioConfig
	 */
	private $config;

	/**
	 * SalestioUserGenerator constructor.
	 *
	 * @param OptionAccessor $option_accessor Option accessor.
	 * @param SalestioConfig $config          Plugin config.
	 */
	public function __construct( OptionAccessor $option_accessor, SalestioConfig $config ) {
		$this->option_accessor = $option_accessor;
		$this->config          = $config;
	}

	/**
	 * Create or link a Salestio user.
	 *
	 * @param string $site_url Site URL.
	 * @return true|\WP_Error
	 */
	public function create_user( $site_url ) {
		$post_data = wp_json_encode( array( 'shop' => $site_url ) );

		$response = wp_remote_post(
			$this->config->offsetGet( 'registration_url' ),
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => $post_data,
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code < 200 || $status_code >= 300 ) {
			return new \WP_Error(
				'salestio_registration_failed',
				__( 'Salestio registration failed. Please try again later.', 'salestio' ),
				array( 'status' => $status_code )
			);
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $result['storeId'] ) || ! isset( $result['merchantSecret'] ) ) {
			return new \WP_Error(
				'salestio_registration_invalid',
				__( 'Salestio registration returned an invalid response.', 'salestio' )
			);
		}

		$this->option_accessor->set_store_id( sanitize_text_field( $result['storeId'] ) );
		$this->option_accessor->set_merchant_secret( sanitize_text_field( $result['merchantSecret'] ) );

		wp_cache_flush();

		return true;
	}
}
