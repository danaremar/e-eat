<?php
/**
 * Booster for WooCommerce - Module - Product Input Fields
 *
 * @version 5.2.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Product_Input_Fields' ) ) :

class WCJ_Product_Input_Fields extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 5.2.0
	 * @todo    (maybe) option to change local and global fields order (i.e. output local fields before the global)
	 */
	function __construct() {

		$this->id         = 'product_input_fields';
		$this->short_desc = __( 'Product Input Fields', 'woocommerce-jetpack' );
		$this->desc       = __( 'Add input fields to the products (1 input field allowed in free version).', 'woocommerce-jetpack' );
		$this->desc_pro   = __( 'Add input fields to the products.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-product-input-fields';
		parent::__construct();

		require_once( 'input-fields/class-wcj-product-input-fields-core.php' );

		if ( $this->is_enabled() ) {

			add_action( 'woocommerce_delete_order_items', array( $this, 'delete_file_uploads' ) );

			add_action( 'init', array( $this, 'handle_downloads' ) );

			$this->global_product_fields = new WCJ_Product_Input_Fields_Core( 'global' );
			$this->local_product_fields  = new WCJ_Product_Input_Fields_Core( 'local' );

			if ( 'yes' === wcj_get_option( 'wcj_product_input_fields_global_enabled', 'no' ) || 'yes' === wcj_get_option( 'wcj_product_input_fields_local_enabled', 'no' ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'init',               array( $this, 'register_scripts' ) );
			}

			add_action( 'wp_head',               array( $this, 'preserve_linebreaks_frontend' ) );
			add_action( 'admin_head',               array( $this, 'preserve_linebreaks_admin' ) );
		}
	}

	/**
	 * preserve_linebreaks_admin.
	 *
	 * @version 4.6.0
	 * @since   4.5.0
	 */
	function preserve_linebreaks_admin() {
		if ( 'yes' !== wcj_get_option( 'wcj_product_input_fields_admin_linebreaks', 'no' ) ) {
			return;
		}
		?>
		<style>
			#woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items table.display_meta tr td, #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items table.meta tr td {
				white-space: pre-wrap;
			}
		</style>
		<?php
	}

	/**
	 * preserve_linebreaks_frontend.
	 *
	 * @version 4.6.0
	 * @since   4.5.0
	 */
	function preserve_linebreaks_frontend() {
		if ( 'yes' !== wcj_get_option( 'wcj_product_input_fields_frontend_linebreaks', 'no' ) ) {
			return;
		}
		?>
		<style>
			.woocommerce-cart-form__cart-item.cart_item .product-name dl dd,
			.woocommerce-checkout-review-order-table .product-name dl dd {
				white-space: pre-wrap !important;
			}

			.woocommerce-cart-form__cart-item.cart_item .product-name dt,
			.woocommerce-checkout-review-order-table .product-name dt {
				display: block;
			}
		</style>
		<?php
	}

	/**
	 * get_global_product_fields_options.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function get_global_product_fields_options() {
		$this->scope = 'global';
		$return = require( 'input-fields/wcj-product-input-fields-options.php' );
		unset( $this->scope );
		return $return;
	}

	/**
	 * delete_file_uploads.
	 *
	 * @version 2.2.2
	 * @since   2.2.2
	 */
	function delete_file_uploads( $postid ) {
		$the_order = wc_get_order( $postid );
		$the_items = $the_order->get_items();
		foreach ( $the_items as $item ) {
			foreach ( $item as $item_field ) {
				$item_field = maybe_unserialize( $item_field );
				if ( is_array( $item_field ) && isset( $item_field['wcj_type'] ) && 'file' === $item_field['wcj_type'] ) {
					unlink( $item_field['tmp_name'] );
				}
			}
		}
	}

	/**
	 * handle_downloads.
	 *
	 * @version 2.5.0
	 * @since   2.2.2
	 */
	function handle_downloads() {
		if ( isset ( $_GET['wcj_download_file'] ) ) {
			$file_name = $_GET['wcj_download_file'];
			$upload_dir = wcj_get_wcj_uploads_dir( 'input_fields_uploads' );
			$file_path = $upload_dir . '/' . $file_name;
			if ( wcj_is_user_role( 'administrator' ) || is_shop_manager() ) {
				header( "Expires: 0" );
				header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
				header( "Cache-Control: private", false );
				header( 'Content-disposition: attachment; filename=' . $file_name );
				header( "Content-Transfer-Encoding: binary" );
				header( "Content-Length: ". filesize( $file_path ) );
				readfile( $file_path );
				exit();
			}
		}
	}

	/**
	 * register_script.
	 *
	 * @version 2.9.0
	 */
	function register_scripts() {
		wp_register_script( 'wcj-product-input-fields', wcj_plugin_url() . '/includes/js/wcj-product-input-fields.js', array( 'jquery' ), WCJ()->version, true );
	}

	/**
	 * enqueue_checkout_script.
	 */
	function enqueue_scripts() {
		if( ! is_product() ) {
			return;
		}
		wp_enqueue_script( 'wcj-product-input-fields' );
	}

}

endif;

return new WCJ_Product_Input_Fields();
