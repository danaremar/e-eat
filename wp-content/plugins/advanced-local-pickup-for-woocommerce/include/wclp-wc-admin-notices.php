<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_ALP_Admin_Notices_Under_WC_Admin {

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
	 * @return WC_ALP_Admin_Notices_Under_WC_Admin
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
		add_action('init', array( $this, 'admin_notices_for_alp_pro' ) );
	}

	public function admin_notices_for_alp_pro(){

		if ( class_exists( 'Advanced_local_pickup_PRO' ) )return;
		
		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ) {
			return;
		}
		
		$already_set = get_transient( 'alp_pro_wc_admin' );
		
		if($already_set == 'yes')return;
		
		set_transient( 'alp_pro_wc_admin', 'yes' );				
		
		$note_name = 'alp_pro_notice';
		$data_store = WC_Data_Store::load( 'admin-note' );		
		
		// Otherwise, add the note
		$activated_time = current_time( 'timestamp', 0 );
		$activated_time_formatted = date( 'F jS', $activated_time );
		$note = new Automattic\WooCommerce\Admin\Notes\WC_Admin_Note();
		$note->set_title( 'Advanced Local Pickup PRO' );
		$note->set_content( 'We just released a Pro version for Advanced Local Pickup with multiple pickup locations, split work hours, local pickup discounts and more.. Enjoy our early bird discount, use code ALPPRO10 during checkout.' );
		$note->set_content_data( (object) array(
			'getting_started'     => true,
			'activated'           => $activated_time,
			'activated_formatted' => $activated_time_formatted,
		) );
		$note->set_type( 'info' );
		$note->set_layout('plain');
		$note->set_image('');
		$note->set_name( $note_name );
		$note->set_source( 'Advanced Local Pickup PRO' );
		$note->set_layout('plain');
		$note->set_image('');
		// This example has two actions. A note can have 0 or 1 as well.
		$note->add_action(
			'settings', 'Go Pro >', 'https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/'
		);		
		$note->save();
	}
			
}

/**
 * Returns an instance of WC_ALP_Admin_Notices_Under_WC_Admin.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return WC_ALP_Admin_Notices_Under_WC_Admin
*/
function WC_ALP_Admin_Notices_Under_WC_Admin() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new WC_ALP_Admin_Notices_Under_WC_Admin();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
WC_ALP_Admin_Notices_Under_WC_Admin();