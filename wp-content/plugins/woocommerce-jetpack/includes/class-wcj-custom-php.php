<?php
/**
 * Booster for WooCommerce - Module - Custom PHP
 *
 * @version 4.0.1
 * @since   4.0.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Custom_PHP' ) ) :

class WCJ_Custom_PHP extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 4.0.1
	 * @since   4.0.0
	 * @todo    [dev] maybe remove `wcj_disable_custom_php` from URL on settings save
	 * @todo    [dev] allow tab in content (i.e. settings (textarea))
	 */
	function __construct() {

		$this->id         = 'custom_php';
		$this->short_desc = __( 'Custom PHP', 'woocommerce-jetpack' );
		$this->desc       = __( 'Custom PHP tool.', 'woocommerce-jetpack' );
		$this->extra_desc = sprintf(
			__( 'Please note that if you enable the module and enter non-valid PHP code here, your site will become unavailable. To fix this you will have to add %s attribute to the URL (you must be logged as shop manager or admin (for this reason custom PHP code is not executed on %s page)).', 'woocommerce-jetpack' ),
				'<code>wcj_disable_custom_php</code>', '<strong>wp-login.php</strong>' ) . ' ' .
			sprintf( __( 'E.g.: %s', 'woocommerce-jetpack' ),
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=jetpack&wcj-cat=emails_and_misc&section=custom_php&wcj_disable_custom_php' ) . '">' .
					admin_url( 'admin.php?page=wc-settings&tab=jetpack&wcj-cat=emails_and_misc&section=custom_php&wcj_disable_custom_php' ) . '</a>' );
		$this->link_slug  = 'woocommerce-booster-custom-php';
		parent::__construct();

		add_action( 'woojetpack_after_settings_save',  array( $this, 'create_php_file' ), PHP_INT_MAX, 2 );

		if ( $this->is_enabled() ) {
			if ( isset( $_GET['wcj_disable_custom_php'] ) ) {
				if ( wcj_current_user_can( 'manage_woocommerce' ) ) {
					// Stop custom PHP execution
					return;
				} elseif ( ! wcj_is_user_logged_in() ) {
					// Redirect to login page
					wp_redirect( wp_login_url( add_query_arg( '', '' ) ) );
					exit;
				}
			}
			if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
				// Stop custom PHP execution if it's the login page
				return;
			}
			// Executing custom PHP code
			$file_path = wcj_get_wcj_uploads_dir( 'custom_php', false ) . DIRECTORY_SEPARATOR . 'booster.php';
			if ( file_exists( $file_path ) ) {
				include_once( $file_path );
			}
		}
	}

	/**
	 * create_php_file.
	 *
	 * @version 4.0.0
	 * @since   4.0.0
	 * @todo    [dev] `unlink` and `rmdir` on settings reset
	 * @todo    [dev] on empty content - delete dir also (`rmdir`)
	 */
	function create_php_file( $sections, $current_section ) {
		if ( $this->id === $current_section ) {
			$file_content = wcj_get_option( 'wcj_custom_php', '' );
			if ( '' !== $file_content ) {
				$file_path = wcj_get_wcj_uploads_dir( 'custom_php' ) . DIRECTORY_SEPARATOR . 'booster.php';
				file_put_contents( $file_path, '<?php' . PHP_EOL . $file_content );
			} else {
				$file_path = wcj_get_wcj_uploads_dir( 'custom_php', false ) . DIRECTORY_SEPARATOR . 'booster.php';
				if ( file_exists( $file_path ) ) {
					unlink( $file_path );
				}
			}
		}
	}

}

endif;

return new WCJ_Custom_PHP();
