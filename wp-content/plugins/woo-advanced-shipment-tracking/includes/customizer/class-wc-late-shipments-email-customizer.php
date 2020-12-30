<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wcast_late_shipments_customizer_email {
	// Get our default values	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wcast_generate_defaults();
		
		$wc_ast_api_key = get_option('wc_ast_api_key');

		if(!$wc_ast_api_key){
			return;
		}		
	}		
	
	/**
	 * code for initialize default value for customizer
	*/
	public function wcast_generate_defaults() {		
		$customizer_defaults = array(			
			'wcast_late_shipments_email_subject' => __( 'Late shipment for order #{order_number}', 'woo-advanced-shipment-tracking' ),
			'wcast_late_shipments_email_heading' => __( 'Late shipment', 'woo-advanced-shipment-tracking' ),
			'wcast_late_shipments_email_content' => __( 'This order was shipped {shipment_length} days ago, the shipment status is {shipment_status} and its est. delivery date is {est_delivery_date}.', 'woo-advanced-shipment-tracking' ),				
			'wcast_enable_late_shipments_admin_email'  => '',
			'wcast_late_shipments_days' => '7',
			'wcast_late_shipments_email_to'  => '{admin_email}',
			'wcast_late_shipments_show_tracking_details' => '',
			'wcast_late_shipments_show_order_details' => '',
			'wcast_late_shipments_show_billing_address' => '',
			'wcast_late_shipments_show_shipping_address' => '',
			'wcast_late_shipments_email_code_block' => '',
		);

		return apply_filters( 'skyrocket_customizer_defaults', $customizer_defaults );
	}			
}
/**
 * Initialise our Customizer settings
 */

$wcast_late_shipments_settings = new wcast_late_shipments_customizer_email();