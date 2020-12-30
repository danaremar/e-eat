<?php
/**
 * Handles email sending
 */
class WC_TrackShip_Email_Manager {

	private static $instance;
	
	/**
	 * Constructor sets up actions
	 */
	public function __construct() {		
	}			
	
	/**
	 * code for send shipment status email
	 */
	public function shippment_status_email_trigger( $order_id, $order, $old_status, $new_status , $tracking_item ){			
		
		$status = str_replace("_","",$new_status);
		$status_class = 'wcast_'.$status.'_customizer_email';		
		$wcast_status_customizer_email = new $status_class();
		$ast = new WC_Advanced_Shipment_Tracking_Actions;		
		
		$email_subject = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_email_subject',$wcast_status_customizer_email->defaults['wcast_'.$status.'_email_subject']);				
		
		$subject = $this->email_subject($email_subject,$order_id,$order);
				
		$email_to = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_email_to',$wcast_status_customizer_email->defaults['wcast_'.$status.'_email_to']);								
		
		$email_to = explode(",",$email_to);				
		
		$enable = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_enable_'.$status.'_email','');
		
		if($enable == 1){
			foreach($email_to as $email){																
				$email_heading = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_email_heading',$wcast_status_customizer_email->defaults['wcast_'.$status.'_email_heading']);								
									
				$email_content = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_email_content',$wcast_status_customizer_email->defaults['wcast_'.$status.'_email_content']);							
				
				$wcast_show_tracking_details = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_show_tracking_details','');
				
				$wcast_show_order_details = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_show_order_details','');
				
				$wcast_show_billing_address = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_show_billing_address','');
				
				$wcast_show_shipping_address = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_show_shipping_address','');
				
				$sent_to_admin = false;
				$plain_text = false;
				
					
				$recipient = $this->email_to($email,$order,$order_id);
				
				$email_content = $this->email_content($email_content,$order_id, $order);
				
				$mailer = WC()->mailer();
				
				$email_heading = $this->email_heading($email_heading,$order_id,$order);
									
				$message = $this->append_analytics_link($email_content,$status);								
				
				$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
				$tracking_items = $wast->get_tracking_items( $order_id, true );
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);
				
				foreach($tracking_items as $key => $item){
					if($item['tracking_number'] != $tracking_item['tracking_number']){
						unset($tracking_items[$key]);
					}
				}				
				
				if($wcast_show_tracking_details == 1){					
					ob_start();
					$local_template	= get_stylesheet_directory().'/woocommerce/emails/tracking-info.php';			
					if ( file_exists( $local_template ) && is_writable( $local_template )){				
						wc_get_template( 'emails/tracking-info.php', array( 
							'tracking_items' => $tracking_items,
							'order_id' => $order_id,
							'show_shipment_status' => false,
							'new_status' => $new_status,
						), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
					} else{
						wc_get_template( 'emails/tracking-info.php', array( 
							'tracking_items' => $tracking_items,
							'order_id' => $order_id,
							'show_shipment_status' => false,
							'new_status' => $new_status,	
						), 'woocommerce-advanced-shipment-tracking/', wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' );
					}
					$message .= ob_get_clean();			
				}					
				
				if($wcast_show_order_details == 1){					
					ob_start();
					wc_get_template(
						'emails/wcast-email-order-details.php', array(
							'order'         => $order,
							'sent_to_admin' => $sent_to_admin,
							'plain_text'    => $plain_text,
							'email'         => '',
						),
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
					);	
					$message .= ob_get_clean();	
				}
				
				if($wcast_show_billing_address == 1){
					ob_start();
					wc_get_template(
						'emails/wcast-billing-email-addresses.php', array(
							'order'         => $order,
							'sent_to_admin' => $sent_to_admin,
						),
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
					);	
					$message .= ob_get_clean();	
				}
				
				if($wcast_show_shipping_address == 1){
					ob_start();
					wc_get_template(
						'emails/wcast-shipping-email-addresses.php', array(
							'order'         => $order,
							'sent_to_admin' => $sent_to_admin,
						),
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
					);	
					$message .= ob_get_clean();	
				}
								
				// create a new email
				$email = new WC_Email();
			
				// wrap the content with the email template and then add styles
				$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );
				$headers = "Content-Type: text/html\r\n";
				add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
				add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
				
				$email_send = wp_mail( $recipient, $subject, $message, $email->get_headers() );
				$logger = wc_get_logger();
				$context = array( 'source' => 'trackship_shipment_status_email_log' );
				$logger->error( "Order_Id: ".$order_id." Shipment_Status: ".$new_status." Email_Sent: " .$email_send, $context );
			}
		}	
	}
		
	/**
	 * code for send delivered shipment status email
	 */
	public function delivered_shippment_status_email_trigger($order_id, $order, $old_status, $new_status, $tracking_item){	
		
		$wcast_delivered_customizer_email = new wcast_delivered_customizer_email();		
		$ast = new WC_Advanced_Shipment_Tracking_Actions;	
		
		$email_subject = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_email_subject',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_subject']);
		
		$subject = $this->email_subject($email_subject,$order_id,$order);
		
		$email_to = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_email_to',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_to']);		
		
		$enable = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_enable_delivered_status_email',$wcast_delivered_customizer_email->defaults['wcast_enable_delivered_status_email']);	
		
		$email_to = explode(",",$email_to);
		
		if($enable == 1){	
			foreach($email_to as $email){													
				
				$email_heading = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_email_heading',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_heading']);				
				
				$email_content = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_email_content',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_content']);				
				
				$wcast_show_tracking_details = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_tracking_details',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_tracking_details']);
				
				$wcast_show_order_details = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_order_details',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_order_details']);				
				
				$wcast_show_billing_address = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_billing_address',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_billing_address']);
				
				$wcast_show_shipping_address = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_shipping_address',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_shipping_address']);
				
				$sent_to_admin = false;
				$plain_text = false;				
					
				$recipient = $this->email_to($email,$order,$order_id);
				
				$email_content = $this->email_content($email_content,$order_id, $order);
				
				$mailer = WC()->mailer();
				
				$email_heading = $this->email_heading($email_heading,$order_id,$order);
				
				$status = 'delivered_status';	
				$message = $this->append_analytics_link($email_content,$status);
				
				$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
				$tracking_items = $wast->get_tracking_items( $order_id, true );
				
				foreach($tracking_items as $key => $item){
					if($item['tracking_number'] != $tracking_item['tracking_number']){
						unset($tracking_items[$key]);
					}
				}
				
				if($wcast_show_tracking_details == 1){						
					ob_start();					
					$local_template	= get_stylesheet_directory().'/woocommerce/emails/tracking-info.php';			
					if ( file_exists( $local_template ) && is_writable( $local_template )){				
						wc_get_template( 'emails/tracking-info.php', array( 
							'tracking_items' => $tracking_items,
							'order_id' => $order_id,
							'show_shipment_status' => false,
							'new_status' => $new_status,
						), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
					} else{
						wc_get_template( 'emails/tracking-info.php', array( 
							'tracking_items' => $tracking_items,
							'order_id' => $order_id,
							'show_shipment_status' => false,
							'new_status' => $new_status,
						), 'woocommerce-advanced-shipment-tracking/', wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' );
					}
					$message .= ob_get_clean();			
				}					
				
				if($wcast_show_order_details == 1){
					
					ob_start();
					wc_get_template(
						'emails/wcast-email-order-details.php', array(
						'order'         => $order,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => '',
						),
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
					);	
					$message .= ob_get_clean();	
				}
				
				if($wcast_show_billing_address == 1){
					ob_start();
					wc_get_template(
						'emails/wcast-billing-email-addresses.php', array(
							'order'         => $order,
							'sent_to_admin' => $sent_to_admin,
						),
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
					);	
					$message .= ob_get_clean();	
				}
				
				if($wcast_show_shipping_address == 1){
					ob_start();
					wc_get_template(
						'emails/wcast-shipping-email-addresses.php', array(
							'order'         => $order,
							'sent_to_admin' => $sent_to_admin,
						),
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
					);	
					$message .= ob_get_clean();	
				}
								
				// create a new email
				$email = new WC_Email();
		
				// wrap the content with the email template and then add styles
				$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );
				$headers = "Content-Type: text/html\r\n";
				add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
				add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
				
				$email_send = wp_mail( $recipient, $subject, $message, $email->get_headers() );
								
				$logger = wc_get_logger();
				$context = array( 'source' => 'trackship_shipment_status_email_log' );
				$logger->error( "Order_Id: ".$order_id." Shipment_Status: ".$new_status." Email_Sent: " .$email_send, $context );
			}			
		}			
	}
	
	/**
	 * code for format email subject
	 */
	public function email_subject( $string, $order_id, $order ){
		
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $ast->get_custom_order_number( $order_id );
		
		$customer_email = $order->get_billing_email();
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$user = $order->get_user();
		
		if($user)$username = $user->user_login;
		
		$string =  str_replace( '{order_number}', $order_id, $string );
		$string =  str_replace( '{customer_email}', $customer_email, $string );
		$string =  str_replace( '{customer_first_name}', $first_name, $string );
		$string =  str_replace( '{customer_last_name}', $last_name, $string );
		
		if(isset($username)){
			$string = str_replace( '{customer_username}', $username, $string );
		} else{
			$string = str_replace( '{customer_username}', '', $string );
		}
		
		$string =  str_replace( '{site_title}', $this->get_blogname(), $string );
		return $string;
	} 
	
	/**
	 * code for format email heading
	 */	
	public function email_heading( $string, $order_id, $order ){
		
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $ast->get_custom_order_number( $order_id );
		
		$customer_email = $order->get_billing_email();
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$user = $order->get_user();
		
		if($user)$username = $user->user_login;		
		
		$string =  str_replace( '{order_number}', $order_id, $string );
		$string =  str_replace( '{customer_email}', $customer_email, $string );
		$string =  str_replace( '{customer_first_name}', $first_name, $string );
		$string =  str_replace( '{customer_last_name}', $last_name, $string );
		
		if(isset($username)){
			$string = str_replace( '{customer_username}', $username, $string );
		} else{
			$string = str_replace( '{customer_username}', '', $string );
		}
		
		$string =  str_replace( '{site_title}', $this->get_blogname(), $string );
		return $string;
	} 
	
	/**
	 * code for format recipients 
	 */	
	public function email_to( $string, $order, $order_id ){
		$customer_email = $order->get_billing_email();
		$admin_email = get_option('admin_email');
		$string =  str_replace( '{admin_email}', $admin_email, $string );
		$string =  str_replace( '{customer_email}', $customer_email, $string );
		return $string;
	} 
	
	/**
	 * code for format email content 
	 */
	public function email_content($email_content, $order_id, $order){						
		
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $ast->get_custom_order_number( $order_id );
		
		$customer_email = $order->get_billing_email();
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$company_name = $order->get_billing_company();
		$user = $order->get_user();
		
		if($user)$username = $user->user_login;		
		
		$wc_ast_api_key = get_option('wc_ast_api_key');
		$api_enabled = get_option( "wc_ast_api_enabled", 0);
		
		if($wc_ast_api_key && $api_enabled)$est_delivery_date = $this->get_est_delivery_date( $order->get_id(), $order );
		
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
		
		if($wc_ast_api_key && $api_enabled)$email_content = str_replace( '{est_delivery_date}', $est_delivery_date, $email_content );
		
		return $email_content;
	}
	
	/**
	 * code for append analytics link
	 */
	public function append_analytics_link($message,$status){
		
		$ast = new WC_Advanced_Shipment_Tracking_Actions;	
		
		if($status == 'delivered_status'){
			$analytics_link = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_analytics_link','');	
		} else{
			$analytics_link = $ast->get_option_value_from_array('wcast_'.$status.'_email_settings','wcast_'.$status.'_analytics_link','');
		}		
	
		if($analytics_link){	
			$regex = '#(<a href=")([^"]*)("[^>]*?>)#i';
			$message = preg_replace_callback($regex, function($match) use ($status){
							$url = $match[2];
							if (strpos($url, '?') === false) {
								$url .= '?';
							}
							$url .= $analytics_link;
							return $match[1].$url.$match[3];
						}, $message);	
		}
		return $message;	
	}	

	/**
	 * code for get estimate delivery date
	 */
	public function get_est_delivery_date($order_id, $order){
		
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$tracking_items = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );				
		} else {
			$order          = new WC_Order( $order_id );
			$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );			
		}
		
		$html = '';
		$wc_ast_api_key = get_option('wc_ast_api_key');
		$shipment_status = get_post_meta( $order_id, "shipment_status", true);
		if($tracking_items){
			foreach($tracking_items as $key => $item){
				$tracking_number = $item['tracking_number'];				
				if(isset($shipment_status[$key]['est_delivery_date']) && $shipment_status[$key]['est_delivery_date'] != ''){
					$est_delivery_date = $shipment_status[$key]['est_delivery_date'];
					$unixTimestamp = strtotime($est_delivery_date);				
					$day = date("l", $unixTimestamp);					
					$html .= '<div>Estimated Delivery Date for Tracking Number - '.$tracking_number.'</div><h3 style="margin:0 0 10px;">'.$day.', '.date("M d", strtotime($est_delivery_date)).'</h3>';
				}				
			}	
		}
		return $html;
	}
	
	/**
	 * Get blog name formatted for emails.
	 *
	 * @return string
	 */
	private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	
	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'woocommerce_email_from_name', get_option( 'woocommerce_email_from_name' ), $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
		return sanitize_email( $from_address );
	}		
	
}// end of class
new WC_TrackShip_Email_Manager();

/**
 * Returns an instance of zorem_woocommerce_advanced_shipment_tracking.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return zorem_woocommerce_advanced_shipment_tracking
*/
function wc_trackship_email_manager() {
	static $instance;

	if ( ! isset( $instance ) ) {
		$instance = new WC_TrackShip_Email_Manager();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_trackship_email_manager();