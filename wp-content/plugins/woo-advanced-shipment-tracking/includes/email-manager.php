<?php
/**
 * Handles email sending
 */
class WC_Advanced_Shipment_Tracking_Email_Manager {

	private static $instance;
	
	/**
	 * Constructor sets up actions
	 */
	public function __construct() {
		// template path	
		if (!defined('AST_TEMPLATE_PATH')) define('AST_TEMPLATE_PATH', SHIPMENT_TRACKING_PATH . '/templates/');		
	    // hook for when order status is changed	
		add_filter( 'woocommerce_email_classes', array( $this, 'custom_init_emails' ));		
	}		    
	
	/**
	 * code for include delivered email class
	 */
	public function custom_init_emails( $emails ) {
				
		// Include the email class file if it's not included already		
		$newstatus = get_option( "wc_ast_status_delivered", 0);
		if( $newstatus == true ){
			if ( ! isset( $emails[ 'WC_Email_Customer_Delivered_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Delivered_Order' ] = include_once( 'emails/class-shipment-delivered-email.php' );
			}
		}
		
		$partial_shipped_status = get_option( "wc_ast_status_partial_shipped", 0);
		if( $partial_shipped_status == true ){
			if ( ! isset( $emails[ 'WC_Email_Customer_Partial_Shipped_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Partial_Shipped_Order' ] = include_once( 'emails/class-shipment-partial-shipped-email.php' );
			}
		}
		
		$updated_tracking_status = get_option( "wc_ast_status_updated_tracking", 0);
		if( $updated_tracking_status == true ){
			if ( ! isset( $emails[ 'WC_Email_Customer_Updated_Tracking_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Updated_Tracking_Order' ] = include_once( 'emails/class-shipment-updated-tracking-email.php' );
			}				
		}
		return $emails;
	}
	
	/**
	 * code for format email content 
	 */
	public function email_content( $email_content, $order_id, $order ){	
	
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $ast->get_custom_order_number( $order_id );
		
		$customer_email = $order->get_billing_email();
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$company_name = $order->get_billing_company();
		$user = $order->get_user();
		
		if($user)$username = $user->user_login;			
		
		$email_content = str_replace( '{customer_email}', $customer_email, $email_content );
		$email_content = str_replace( '{site_title}', $this->get_blogname(), $email_content );
		$email_content = str_replace( '{customer_first_name}', $first_name, $email_content );
		$email_content = str_replace( '{customer_last_name}', $last_name, $email_content );
		
		if(isset($company_name)){
			$email_content = str_replace( '{customer_company_name}', $company_name, $email_content );	
		} else{
			$email_content = str_replace( '{customer_company_name}','', $email_content );	
		}	 
		
		if(isset($username)){
			$email_content = str_replace( '{customer_username}', $username, $email_content );
		} else{
			$email_content = str_replace( '{customer_username}', '', $email_content );
		}
		
		$email_content = str_replace( '{order_number}', $order_id, $email_content );		
		
		return $email_content;
	}

	/**
	 * Get blog name formatted for emails.
	 *
	 * @return string
	 */
	private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}		
	
}// end of class
new WC_Advanced_Shipment_Tracking_Email_Manager();

/**
 * Returns an instance of zorem_woocommerce_advanced_shipment_tracking.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return zorem_woocommerce_advanced_shipment_tracking
*/
function wc_advanced_shipment_tracking_email_class() {
	static $instance;

	if ( ! isset( $instance ) ) {
		$instance = new WC_Advanced_Shipment_Tracking_Email_Manager();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_advanced_shipment_tracking_email_class();