<?php
/**
 * Render Salestio admin screens.
 *
 * @package Salestio
 */

namespace Salestio\WooCommerce;

use Salestio\Services\HmacGenerator;
use Salestio\Services\OptionAccessor;
use Salestio\Services\SalestioConfig;

/**
 * Page renderer.
 */
class PageRenderer {

	/**
	 * HMAC generator.
	 *
	 * @var HmacGenerator
	 */
	private $hmac_generator;

	/**
	 * Option accessor.
	 *
	 * @var OptionAccessor
	 */
	private $option_accessor;

	/**
	 * Salestio configuration.
	 *
	 * @var SalestioConfig
	 */
	private $config;

	/**
	 * PageRenderer constructor.
	 *
	 * @param HmacGenerator  $hmac_generator  HMAC generator.
	 * @param OptionAccessor $option_accessor Option accessor.
	 * @param SalestioConfig $config          Config.
	 */
	public function __construct(
		HmacGenerator $hmac_generator,
		OptionAccessor $option_accessor,
		SalestioConfig $config
	) {
		$this->hmac_generator  = $hmac_generator;
		$this->option_accessor = $option_accessor;
		$this->config          = $config;
	}

	/**
	 * Render an admin tab.
	 *
	 * @param string $tab_id Tab identifier.
	 * @return void
	 */
	public function render_tab( $tab_id = Initializer::MAIN_PAGE ) {
		$tab_id = Initializer::MAIN_PAGE;
		echo '<div class="wrap salestio-wrap">';
		echo '<h1>' . esc_html__( 'Salestio', 'salestio' ) . '</h1>';
		$this->render_notice();

		if ( $this->option_accessor->is_empty() ) {
			$this->render_connect_screen( $tab_id );
			echo '</div>';
			return;
		}

		$wp_version = preg_replace( '/[[:cntrl:]]/', '', get_bloginfo( 'version' ) );
		$site_url   = $this->prepare_url( preg_replace( '/[[:cntrl:]]/', '', get_site_url() ) );

		$generated_params                    = $this->hmac_generator->generate_params( $site_url, $this->option_accessor->get_store_id() );
		$generated_params['wpVersion']       = $wp_version;
		$generated_params['salestioVersion'] = $this->config->offsetGet( 'version' );

		$url = add_query_arg(
			$generated_params,
			$this->config->offsetGet( 'app_url' ) . $this->get_internal_route_from_page( $tab_id )
		);

		$this->render_launch_screen( $url );
		echo '</div>';
	}

	/**
	 * Normalize a store URL.
	 *
	 * @param string $url Store URL.
	 * @return string
	 */
	protected function prepare_url( $url ) {
		$url = preg_replace( '/^https?:\/\/(.+)$/i', '\\1', $url );
		$url = preg_replace( '/^www.(.+)$/i', '\\1', $url );

		return rtrim( $url, '/' );
	}

	/**
	 * Render the connect screen.
	 *
	 * @param string $tab_id Tab identifier.
	 * @return void
	 */
	private function render_connect_screen( $tab_id ) {
		$redirect_url = add_query_arg( 'page', $tab_id, admin_url( 'admin.php' ) );
		$auto_open    = $this->option_accessor->get_auto_open();
		?>
			<div id="salestio-container" class="salestio-card card">
				<img class="salestio-hero" src="<?php echo esc_url( 'https://salest.io/img/signup/RocketToAmazon_short.svg' ); ?>" alt="<?php echo esc_attr__( 'Salestio marketplaces', 'salestio' ); ?>">
				<p><?php echo esc_html__( 'Connect your WooCommerce store to Salestio to link your store with Amazon, eBay, Etsy, and other marketplaces.', 'salestio' ); ?></p>
				<p class="salestio-note"><?php echo esc_html__( 'Salestio works via API integration, syncing products and orders with your store and sending updates through webhooks.', 'salestio' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="salestio_connect">
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_url ); ?>">
					<?php wp_nonce_field( 'salestio_connect' ); ?>
					<?php submit_button( __( 'Connect Salestio', 'salestio' ), 'primary', 'submit', false ); ?>
					<label for="salestio-auto-open" class="salestio-option">
						<input type="checkbox" id="salestio-auto-open" name="salestio_auto_open" value="1" <?php checked( $auto_open ); ?>>
						<?php echo esc_html__( 'Automatically open Salestio after connecting.', 'salestio' ); ?>
					</label>
				</form>
				<p class="salestio-note"><?php echo esc_html__( 'This will create or link your Salestio account and store a Salestio store ID and merchant secret in WordPress.', 'salestio' ); ?></p>
			</div>
		<?php
	}

	/**
	 * Render the launch screen.
	 *
	 * @param string $url Launch URL.
	 * @return void
	 */
	private function render_launch_screen( $url ) {
		$should_autolaunch = $this->option_accessor->get_auto_open();
		$requested_autolaunch = filter_input( INPUT_GET, 'salestio_autolaunch', FILTER_SANITIZE_NUMBER_INT );
		if ( $requested_autolaunch ) {
			$should_autolaunch = true;
		}
		?>
			<div id="salestio-container" class="salestio-card card">
				<p><?php echo esc_html__( 'Open Salestio in a new tab to manage your marketplace integrations.', 'salestio' ); ?></p>
				<p class="salestio-actions">
					<a class="button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html__( 'Open Salestio', 'salestio' ); ?>
					</a>
					<span class="salestio-popup-help" aria-live="polite"></span>
				</p>
				<p class="salestio-note"><?php echo esc_html__( 'Salestio runs on an external service at app.salest.io.', 'salestio' ); ?></p>
			</div>
			<?php if ( $should_autolaunch ) : ?>
				<script>
					(function () {
						var url = <?php echo wp_json_encode( $url ); ?>;
						var popup = window.open(url, '_blank', 'noopener');
						if (!popup || popup.closed || typeof popup.closed === 'undefined') {
							var help = document.querySelector('.salestio-popup-help');
							if (help) {
								help.innerHTML = '<?php echo esc_js( __( 'Popup blocked? ', 'salestio' ) ); ?>' +
									'<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_js( __( 'Open Salestio', 'salestio' ) ); ?></a>';
							}
						}
					})();
				</script>
			<?php endif; ?>
		<?php
	}

	/**
	 * Render admin notices.
	 *
	 * @return void
	 */
	private function render_notice() {
		$connected = filter_input( INPUT_GET, 'salestio_connected', FILTER_SANITIZE_NUMBER_INT );
		if ( $connected ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Salestio connected successfully.', 'salestio' ) . '</p></div>';
			return;
		}

		$error = filter_input( INPUT_GET, 'salestio_error', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! $error ) {
			return;
		}

		$message = sanitize_text_field( $error );
		if ( '' === $message ) {
			$message = __( 'Salestio connection failed. Please try again.', 'salestio' );
		}

		echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Map a tab to an internal app route.
	 *
	 * @param string $tab_id Tab identifier.
	 * @return string
	 */
	private function get_internal_route_from_page( $tab_id ) {
		return '';
	}
}
