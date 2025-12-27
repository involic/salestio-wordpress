<?php
/**
 * Deactivation hook.
 *
 * @package Salestio
 */

namespace Salestio\Hooks;

use Salestio\Services\HmacGenerator;
use Salestio\Services\OptionAccessor;
use Salestio\Services\SalestioConfig;

/**
 * Deactivation hook handler.
 */
class DeactivateHook implements WooHooks {

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
	 * HMAC generator.
	 *
	 * @var HmacGenerator
	 */
	private $hmac_generator;

	/**
	 * DeactivateHook constructor.
	 */
	public function __construct() {
		$this->option_accessor = new OptionAccessor();
		$this->config          = new SalestioConfig();
		$this->hmac_generator  = new HmacGenerator( $this->option_accessor );
	}

	/**
	 * Run on deactivation.
	 *
	 * @return void
	 */
	public function execute() {
		if ( $this->option_accessor->is_empty() ) {
			return;
		}

		$site_url         = $this->prepare_url( preg_replace( '/[[:cntrl:]]/', '', get_site_url() ) );
		$generated_params = $this->hmac_generator->generate_params( $site_url, $this->option_accessor->get_store_id() );

		$post_data = wp_json_encode( $generated_params );

		wp_remote_post(
			$this->config->offsetGet( 'uninstall_url' ),
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => $post_data,
				'timeout' => 10,
			)
		);
	}

	/**
	 * Prepare a store URL for signing.
	 *
	 * @param string $url Store URL.
	 * @return string
	 */
	protected function prepare_url( $url ) {
		$url = preg_replace( '/^https?:\/\/(.+)$/i', '\\1', $url );
		$url = preg_replace( '/^www.(.+)$/i', '\\1', $url );

		return rtrim( $url, '/' );
	}
}
