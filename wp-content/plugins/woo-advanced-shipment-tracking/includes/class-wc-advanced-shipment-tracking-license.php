<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_License {
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;
	
	/**
	 * @var string store_url
	*/
	var $item_code = 'ast_per_product';
	var $store_url = 'https://www.zorem.com/';
	var $default_product_id = '76646';
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return smswoo_license
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0
	 * @return  void
	*/
	public function __construct() {		
		$this->init();
	}
	
	/**
	 * Return item code
	 *
	 * @since   1.0
	 * @return  string
	 *
	 */
	public function get_item_code() {
		return $this->item_code;
	}
	
	/**
	 * Set license key
	 *
	 * @since   1.0
	 * @return  Void
	 *
	 */
	public function set_license_key( $license_key ) {
		update_option( 'ast_product_license_key', $license_key );
	}
	
	/**
	 * Return licence key
	 *
	 * @since   1.0
	 * @return  string
	 *
	 */
	public function get_license_key() {
		return get_option( 'ast_product_license_key', false);
	}
	
	/**
	 * Set license status
	 *
	 * @since   1.0
	 * @return  Void
	 *
	 */
	public function set_license_status( $status ) {
		update_option( 'ast_product_license_status', $status );
	}
	
	/**
	 * Return license status
	 *
	 * @since   1.0
	 * @return  Bool
	 *
	 */
	public function get_license_status() {
		return get_option( 'ast_product_license_status', false);
	}
	/*
	//below line remove if in future not in use
	*/
	public function licence_valid() {
		return get_option( 'ast_product_license_status', false);
	}
	
	
	/**
	 * Create Instance ID
	 *
	 * @since   1.0
	 * @return  string
	 *
	 */
	public function create_instance_id() {
		return $instance_id = md5( $this->get_item_code().time() );
	}
	
	/**
	 * Set Instance ID
	 *
	 * @since   1.0
	 * @return  Void
	 *
	 */
	public function set_instance_id( $instance_id ) {
		update_option( $this->get_item_code().'_instance_id', $instance_id );
	}
	
	/**
	 * Return Instance ID
	 *
	 * @since   1.0
	 * @return  string
	 *
	 */
	public function get_instance_id() {
		return get_option( $this->get_item_code().'_instance_id', false);
	}
	
	/**
	 * Set Instance ID
	 *
	 * @since   1.0
	 * @return  Void
	 *
	 */
	public function set_product_id( $product_id ) {
		update_option( $this->get_item_code().'_product_id', $product_id );
	}
	
	/**
	 * Return item code
	 *
	 * @since   1.0
	 * @return  string
	 *
	 */
	public function get_product_id() {
		$product_id = get_option( $this->get_item_code().'_product_id', false );
		return !empty( $product_id ) ? $product_id : $this->default_product_id;
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init(){		
	}		
	
}
