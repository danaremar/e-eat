<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_AST_Admin_Notices_Under_WC_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {
		$this->init();	
    }
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Admin_notice
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init(){										
		//add_action('init', array( $this, 'admin_notices_for_shippstation_tracking_add_on' ) );
		//add_action('init', array( $this, 'admin_notices_for_wc_shipping_tracking_add_on' ) );		
	}

	public function admin_notices_for_shippstation_tracking_add_on(){

		if ( !is_plugin_active( 'woocommerce-shipstation-integration/woocommerce-shipstation.php' ) )return;
		
		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ) {
			return;
		}
		
		$already_set = get_transient( 'shippstation_tracking_add_on_wc_admin' );
		
		if($already_set == 'yes')return;
		
		set_transient( 'shippstation_tracking_add_on_wc_admin', 'yes' );				
		
		$note_name = 'shippstation_tracking_add_on';
		$data_store = WC_Data_Store::load( 'admin-note' );		
		
		// Otherwise, add the note
		$activated_time = current_time( 'timestamp', 0 );
		$activated_time_formatted = date( 'F jS', $activated_time );
		$note = new Automattic\WooCommerce\Admin\Notes\WC_Admin_Note();
		$note->set_title( 'Auto-sync Tracking from ShipStation to AST' );
		$note->set_content( 'We noticed that you use the ShipStation Integration plugin and the Advanced Shipment Tracking (AST) plugins. You can use the ShipStation tracking add-on for AST to auto-sync the tracking numbers created by ShipStation into the AST shipment tracking order meta!' );
		$note->set_content_data( (object) array(
			'getting_started'     => true,
			'activated'           => $activated_time,
			'activated_formatted' => $activated_time_formatted,
		) );
		$note->set_type( 'info' );
		$note->set_layout('plain');
		$note->set_image('');
		$note->set_name( $note_name );
		$note->set_source( 'ShipStation Tracking Add-on' );
		$note->set_layout('plain');
		$note->set_image('');
		// This example has two actions. A note can have 0 or 1 as well.
		$note->add_action(
			'settings', 'Get this add-on', 'https://www.zorem.com/product/shipstation-tracking-add-on/'
		);		
		$note->save();
	}
	
	public function admin_notices_for_wc_shipping_tracking_add_on(){	
		
		if ( !is_plugin_active( 'woocommerce-services/woocommerce-services.php' ) )return;
		
		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ) {
			return;
		}
		
		$already_set = get_transient( 'wc_shipping_tracking_add_on_wc_admin' );
		
		if($already_set == 'yes')return;
		
		set_transient( 'wc_shipping_tracking_add_on_wc_admin', 'yes' );				
		
		$note_name = 'wc_shipping_tracking_add_on';
		$data_store = WC_Data_Store::load( 'admin-note' );		
		
		// Otherwise, add the note
		$activated_time = current_time( 'timestamp', 0 );
		$activated_time_formatted = date( 'F jS', $activated_time );
		$note = new Automattic\WooCommerce\Admin\Notes\WC_Admin_Note();
		$note->set_title( 'Auto-sync Tracking from WooCommerce Shipping to AST' );
		$note->set_content( 'We noticed that you use the WooCommerce Shipping & Tax plugin and the Advanced Shipment Tracking (AST) plugins. You can use the WC Shipping Tracking add-on for AST to auto-sync the tracking numbers created by WC Shipping into the AST shipment tracking order meta!' );
		$note->set_content_data( (object) array(
			'getting_started'     => true,
			'activated'           => $activated_time,
			'activated_formatted' => $activated_time_formatted,
		) );
		$note->set_type( 'info' );
		$note->set_layout('plain');
		$note->set_image('');
		$note->set_name( $note_name );
		$note->set_source( 'WC Shipping Tracking Add-on' );
		$note->set_layout('plain');
		$note->set_image('');
		// This example has two actions. A note can have 0 or 1 as well.
		$note->add_action(
			'settings', 'Get this add-on', 'https://www.zorem.com/product/wc-shipping-tracking-add-on/'
		);		
		$note->save();
	}
			
}

/**
 * Returns an instance of zorem_woocommerce_advanced_shipment_tracking.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return zorem_woocommerce_advanced_shipment_tracking
*/
function WC_AST_Admin_Notices_Under_WC_Admin() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new WC_AST_Admin_Notices_Under_WC_Admin();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
WC_AST_Admin_Notices_Under_WC_Admin();