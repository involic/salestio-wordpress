<?php
/**
 * WooCommerce admin integration.
 *
 * @package Salestio
 */

namespace Salestio\WooCommerce;

use Salestio\Services\HmacGenerator;
use Salestio\Services\OptionAccessor;
use Salestio\Services\SalestioConfig;
use Salestio\Services\SalestioUserGenerator;

/**
 * WooCommerce initializer.
 */
class Initializer {

	/**
	 * Main menu slug.
	 */
	const MAIN_PAGE = 'salestio';

	/**
	 * Register plugin hooks.
	 *
	 * @return void
	 */
	public function init_plugin() {
		load_plugin_textdomain( 'salestio', false, dirname( plugin_basename( SALESTIO_PLUGIN_FILE ) ) . '/languages' );

		wp_register_style( 'salestio-css', plugins_url( 'salestio.css', SALESTIO_PLUGIN_FILE ), array(), SALESTIO_VERSION );
		wp_register_script( 'salestio-js', plugins_url( 'salestio.js', SALESTIO_PLUGIN_FILE ), array(), SALESTIO_VERSION, true );

		add_action( 'admin_init', array( $this, 'register_privacy_policy' ) );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'show_woocommerce_notice' ) );
			return;
		}

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'admin_post_salestio_connect', array( $this, 'handle_connect_action' ) );
	}

	/**
	 * Register admin menu items.
	 *
	 * @return void
	 */
	public function admin_menu() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$option_accessor = new OptionAccessor();
		$salestio_config = new SalestioConfig();

		$page_renderer = new PageRenderer(
			new HmacGenerator( $option_accessor ),
			$option_accessor,
			$salestio_config
		);

		add_menu_page(
			__( 'Salestio', 'salestio' ),
			__( 'Salestio', 'salestio' ),
			'manage_woocommerce',
			self::MAIN_PAGE,
			function () use ( $page_renderer ) {
				return $page_renderer->render_tab( self::MAIN_PAGE );
			},
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGlkPSJiIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNS4wNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE1LjA2IDE2Ij48ZyBpZD0iYyI+PGc+PHBvbHlnb24gcG9pbnRzPSI4LjkgMTEuNjYgNy43MSAxMS45OSA3LjY0IDEzLjM3IDguMDIgMTMuMzUgNy45NSAxNC43NCA4Ljk2IDEyLjY1IDguNTMgMTIuNzQgOC45IDExLjY2IiBmaWxsPSIjYjRiOWJlIi8+PHBhdGggZD0iTTEuOTIsNS45M2MtLjQ3LDAtMS4wNSwuNzgtMS4wNSwxLjY5cy41OCwxLjY2LDEuMDUsMS42NmMuNDMsMCwuNjYtLjU2LC42Ni0xLjYzLDAtLjY0LS4wOS0xLjcyLS42Ni0xLjcyWiIgZmlsbD0iI2I0YjliZSIvPjxwYXRoIGQ9Ik0xNC43NywxMS41OWwuMjktLjUzYy0uNjItLjMzLTEuMjQtLjM0LTEuNjYtLjAyLS4xLC4wOC0uMTgsLjE3LS4yNSwuMjgtLjY2LS4zLTEuNDYtLjM5LTIuMjMtLjI1LDAtLjE0LC4wMi0uMjUsLjAzLS40di0uMDRjMC0uMSwwLS4yMy0uMDctLjM2LC43LS4zMiwxLjI3LS43LDEuNzEtMS4xOGgwYy43Ny0uODMsMS4xMy0yLjA0LC45NC0zLjIzLS4xNi0xLjAyLS42OC0xLjg5LTEuNDYtMi40Ny0xLjQtMS4wMy0zLjI3LTEuMDQtNS4xNC0uODFsLS4yMi0uOTNjLjI4LS4xNCwuNDktLjQzLC40OS0uNzcsMC0uNDgtLjM5LS44Ny0uODctLjg3cy0uODcsLjM5LS44NywuODdjMCwuNDEsLjMsLjc1LC42OSwuODNsLjAzLC45OHMtLjAzLDAtLjA0LDBsLS4yMSwuMDNjLS4yOCwuMDQtLjU2LC4wOS0uODQsLjEzLTEuNDQsLjI0LTMuNCwuNTctNC4zNiwyLjExLS44OCwxLjQyLS45NiwzLjI1LS4xOSw0LjU1LC4zNCwuNTcsLjc4LDEuMDUsMS4yOSwxLjM5LC4yNiwuMTgsLjU2LC4zMiwuODgsLjQzLS4wNCwuMDktLjA2LC4xOS0uMDQsLjI5di4wNmMuMDcsLjI5LC4xNSwuNjIsLjI2LC45NS0uNDMsLjI1LS43MywuNjctLjg3LDEuMjktLjA5LC4wMi0uMTcsLjA1LS4yNSwuMS0uMjIsLjEyLS43MiwuNTItLjY0LDEuNTVsLjYtLjA1Yy0uMDQtLjQ3LC4wOS0uODMsLjMzLS45NywuMTUtLjA4LC4zMy0uMDcsLjQ3LC4wMywuMjMsLjE3LC4zMiwuNTQsLjIyLC45OWwuNTksLjEyYy4xNC0uNjktLjAzLTEuMjktLjQ2LTEuNi0uMDgtLjA2LS4xNy0uMS0uMjYtLjE0LC4wOS0uMzUsLjI1LS41OSwuNDYtLjc2LC4xOCwuNDMsLjQxLC44NiwuNzQsMS4yNCwuNzYsLjg4LDEuNTgsMS4zOSwyLjQ1LDEuNTMsLjIsLjAzLC40LC4wNSwuNiwuMDUsLjg0LDAsMS42Ni0uMjcsMi4yNy0uNzYsLjY1LS41MywxLjE3LTEuMzQsMS40OC0yLjM1LC4xMS0uMzcsLjE4LS43OCwuMjMtMS4yMiwuNzItLjE2LDEuNTItLjA3LDIuMTEsLjIzLDAsLjA2LDAsLjEyLC4wMSwuMTgsLjA1LC4yNSwuMjksLjg0LDEuMywxLjA2bC4xMi0uNTljLS40Ni0uMS0uNzctLjMyLS44NC0uNi0uMDQtLjE3LC4wMi0uMzMsLjE2LS40NCwuMjMtLjE3LC42MS0uMTUsMS4wMiwuMDdabS00LjI4LS45M2MwLC44NS0uMDksMS41Mi0uMjcsMi4xLS4yLC42NC0uNTksMS41My0xLjMzLDIuMTMtLjY2LC41My0xLjYsLjc3LTIuNTEsLjYyLS43Ni0uMTItMS40OS0uNTgtMi4xNy0xLjM4LS4zLS4zNC0uNS0uNzQtLjY3LTEuMTQsLjA2LS4wMiwuMTEtLjA0LC4xNy0uMDUsLjAzLC4wNywuMDYsLjEzLC4xMSwuMTksLjEsLjEzLC4yNCwuMTksLjM4LC4xOSwwLDAsLjAyLDAsLjAzLDAsLjMtLjAyLC41Mi0uMzYsLjQ5LS43Ni0uMDEtLjE5LS4wOC0uMzYtLjE4LS40OS0uMTEtLjEzLS4yNi0uMi0uNC0uMTktLjE1LC4wMS0uMjgsLjEtLjM3LC4yNS0uMDQsLjA3LS4wNywuMTQtLjA5LC4yMi0uMTIsLjAyLS4yMSwuMDYtLjMyLC4wOS0uMDktLjMtLjE3LS42LS4yMi0uODZ2LS4wM3MwLS4wMSwwLS4wMWgwczAtLjA2LC4wMi0uMDdjLjEzLC4wMywuMjcsLjA1LC40LC4wNywuMDgsLjAxLC4xNSwuMDMsLjIzLC4wNCwuMTUsLjAyLC4zMiwuMDIsLjQ4LC4wMywuMDgsMCwuMTYsLjAxLC4yNCwuMDEsLjAxLDAsLjAzLDAsLjA0LDAsLjcsMCwxLjM4LS4xNSwyLjAzLS4yOSwuMzQtLjA3LC43LS4xNSwxLjA1LS4yMSwuNjctLjEsMS4yNC0uMjIsMS43Ni0uMzUsLjM3LS4xLC43LS4yLDEuMDEtLjMyLC4wMiwwLC4wNS0uMDEsLjA3LS4wMiwuMDQsLjAzLC4wNCwuMDYsLjAzLC4xN3YuMDRabS0uMTEtLjY3Yy0uMzQsLjEzLS43LC4yNS0xLjEyLC4zNi0uNSwuMTMtMS4wNiwuMjQtMS43MSwuMzQtLjM3LC4wNi0uNzMsLjEzLTEuMDgsLjIxLS42NiwuMTQtMS4yOCwuMjgtMS45NCwuMjgtLjUsMC0uOTYtLjA1LTEuMzctLjE2aC0uMDNjLS4zOS0uMTItLjc1LS4yOC0xLjA1LS40OS0uNDUtLjMxLS44NS0uNzQtMS4xNS0xLjI1LS42Ny0xLjE0LS42LTIuODIsLjE5LTQuMDgsLjg1LTEuMzcsMi42Mi0xLjY2LDQuMDUtMS45LC4yOC0uMDUsLjU1LS4wOSwuODMtLjEzbC4yLS4wM3YuNDFjLS4wOCwuMDEtLjE3LC4wMy0uMjYsLjA0LS4zNywuMDYtLjc0LC4xMS0xLjExLC4xNy0uMTUsLjAyLS4yOCwuMDMtLjQ1LC4wOS0uMDMsLjAxLS4wNSwuMDMtLjA4LC4wNS0uMTIsLjEtLjEyLC4yMSwwLC4zNCwuMjMsLjI0LC43NiwuMTksMS4xMiwuMTMsLjMtLjA2LC42MS0uMTEsLjkxLS4xNiwuMzktLjA3LC43Ny0uMTQsMS4xNi0uMiwuNTQtLjA5LC45Ny0uMTQsMS42Mi0uMjUsLjEtLjAyLC4yNC0uMDMsLjI3LS4xNSwuMDItLjA5LS4wNS0uMjItLjEyLS4yNy0uMi0uMTgtMS4yNi0uMDMtMS45LC4wNC0uMDgsMC0uMTYsLjAzLS4yNSwuMDRsLS4wOS0uMzhjMS43Ny0uMjEsMy41LS4yLDQuNzcsLjczLC42OSwuNTEsMS4xNSwxLjI4LDEuMjksMi4xOCwuMTcsMS4wNS0uMTQsMi4xMi0uODIsMi44NS0uNDcsLjUtMS4wOCwuOS0xLjg3LDEuMjFaIiBmaWxsPSIjYjRiOWJlIi8+PHBhdGggZD0iTTEyLjQ1LDUuOTdjLS4zMi0xLjE5LTEuNDQtMS43Mi0yLjYxLTEuNjEtLjQ4LC4wNS0uOTQsLjItMS40MSwuMzItLjc2LC4yLTEuNTUsLjE1LTIuMzIsLjI1LS4yNCwuMDQtLjQ4LC4wOS0uNzEsLjE2LS41NywuMTktMS4wNywuNTEtMS40NCwxLjAzLS40NywuNjUtLjY3LDEuNDgtLjU0LDIuMjcsLjEsLjU4LC4zNywxLjE1LC44MSwxLjU1LC40NSwuNDIsMS4wOCwuNywxLjcsLjU5LC40OS0uMDgsLjg4LS40LDEuMzEtLjYzLC40NS0uMjQsLjk1LS40LDEuNDUtLjQ4LC41My0uMDksMS4wOC0uMDgsMS41OS0uMTcsMS40OS0uMjUsMi41NC0xLjg2LDIuMTYtMy4zMVptLTYuNSwyLjk4Yy0uNTUsMC0xLS41OC0xLTEuM3MuNDUtMS4zLDEtMS4zLDEsLjU4LDEsMS4zLS40NSwxLjMtMSwxLjNabTQuMzQtLjc5Yy0uNTMsMC0uOTYtLjU0LS45Ni0xLjIxcy40My0xLjIxLC45Ni0xLjIxLC45NiwuNTQsLjk2LDEuMjEtLjQzLDEuMjEtLjk2LDEuMjFaIiBmaWxsPSIjYjRiOWJlIi8+PC9nPjwvZz48L3N2Zz4=',
			'33.382'
		);

		remove_submenu_page( self::MAIN_PAGE, self::MAIN_PAGE );
	}

	/**
	 * Enqueue admin assets for plugin screens.
	 *
	 * @param string $hook Current admin hook.
	 * @return void
	 */
	public function admin_scripts( $hook ) {
		if ( false !== strpos( $hook, 'salestio' ) ) {
			wp_enqueue_style( 'salestio-css' );
			wp_enqueue_script( 'salestio-js' );
		}
	}

	/**
	 * Add a body class for Salestio admin screens.
	 *
	 * @param string $classes Existing body classes.
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $page ) {
			$page = sanitize_key( $page );
		}

		if ( $page && false === strpos( $page, 'salestio' ) ) {
			return $classes;
		}

		return $classes . ' salestio_app ';
	}

	/**
	 * Handle the connect action.
	 *
	 * @return void
	 */
	public function handle_connect_action() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to connect Salestio.', 'salestio' ) );
		}

		check_admin_referer( 'salestio_connect' );

		$redirect = filter_input( INPUT_POST, 'redirect_to', FILTER_SANITIZE_URL );
		$redirect = $redirect ? $redirect : '';
		$redirect = wp_validate_redirect( $redirect, admin_url( 'admin.php?page=' . self::MAIN_PAGE ) );

		$option_accessor = new OptionAccessor();
		$salestio_config = new SalestioConfig();
		$auto_open       = filter_input( INPUT_POST, 'salestio_auto_open', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$auto_open       = ! empty( $auto_open );

		$site_url = $this->prepare_url( preg_replace( '/[[:cntrl:]]/', '', get_site_url() ) );
		$result   = ( new SalestioUserGenerator( $option_accessor, $salestio_config ) )->create_user( $site_url );

		if ( is_wp_error( $result ) ) {
			$error_message = sanitize_text_field( $result->get_error_message() );
			$redirect      = add_query_arg( 'salestio_error', $error_message, $redirect );
		} else {
			$redirect = add_query_arg( 'salestio_connected', '1', $redirect );
			$option_accessor->set_auto_open( $auto_open );

			if ( $auto_open ) {
				$redirect = add_query_arg( 'salestio_autolaunch', '1', $redirect );
			}
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Register privacy policy recommendations.
	 *
	 * @return void
	 */
	public function register_privacy_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content  = '<p>' . esc_html__( 'Salestio connects your WooCommerce store to the Salestio service.', 'salestio' ) . '</p>';
		$content .= '<p>' . esc_html__( 'When you connect or open the Salestio admin pages, the plugin sends your store URL, WordPress version, store ID, a timestamp, and an HMAC signature to Salestio.', 'salestio' ) . '</p>';
		$content .= '<p>' . esc_html__( 'Salestio also receives a store ID and merchant secret that are stored in your WordPress options table.', 'salestio' ) . '</p>';
		$content .= '<p>' . esc_html__( 'Privacy policy: https://salest.io/privacy', 'salestio' ) . '</p>';

		wp_add_privacy_policy_content( __( 'Salestio', 'salestio' ), wp_kses_post( $content ) );
	}

	/**
	 * Show a notice when WooCommerce is missing.
	 *
	 * @return void
	 */
	public function show_woocommerce_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Salestio requires WooCommerce to be installed and active.', 'salestio' ) . '</p></div>';
	}

	/**
	 * Normalize a store URL.
	 *
	 * @param string $url Store URL.
	 * @return string
	 */
	private function prepare_url( $url ) {
		$url = preg_replace( '/^https?:\/\/(.+)$/i', '\\1', $url );
		$url = preg_replace( '/^www.(.+)$/i', '\\1', $url );

		return rtrim( $url, '/' );
	}
}
