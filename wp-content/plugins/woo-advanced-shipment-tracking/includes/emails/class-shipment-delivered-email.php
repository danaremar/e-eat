<?php
/**
 * Class WC_Email_Customer_Delivered_Order file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Customer_Delivered_Order', false ) ) :

	/**
	 * Customer Delivered Order Email.
	 *
	 * Order delivered emails are sent to the customer when the order is marked delivered and usual indicates that the order has been shipped.
	 *
	 * @class       WC_Email_Customer_Delivered_Order
	 * @version     2.0.0
	 * @package     WooCommerce/Classes/Emails
	 * @extends     WC_Email
	 */
	class WC_Email_Customer_Delivered_Order extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_delivered_order';
			//$this->customer_email = true;
			$this->title          = __( 'Delivered order', 'woo-advanced-shipment-tracking' );
			$this->description    = __( 'Order delivered emails are sent to customers when their orders are marked delivered and usually indicate that their orders have been shipped.', 'woo-advanced-shipment-tracking' );
			$this->template_html  = 'emails/customer-delivered-order.php';
			$this->template_plain = 'emails/plain/customer-delivered-order.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{order_date}'   => '',
				'{order_number}' => '',
			);		
			$this->recipient = $this->get_option( 'recipient', '{customer_email}' );
			$this->template_base = AST_TEMPLATE_PATH;  						

			// Call parent constructor.
			parent::__construct();
		}	
		
		/**
		* Get valid recipients.
		*
		* @return string
		*/
		public function get_delivered_recipient() {
			$recipient  = apply_filters( 'woocommerce_email_recipient_' . $this->id, $this->recipient, $this->object );			
			$recipient = str_replace( '{customer_email}', $this->object->get_billing_email(), $recipient );			
			$recipients = array_map( 'trim', explode( ',', $recipient ) );			
			return implode( ', ', $recipients );
		}		
				
		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {	
			//echo $this->template_base;exit;
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;			
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();				
			}	
				
			if ( $this->is_enabled() && $this->get_delivered_recipient() ) {				
				$this->send( $this->get_delivered_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Your {site_title} order is now delivered', 'woo-advanced-shipment-tracking' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Thanks for shopping with us', 'woocommerce' );
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			$template = $this->get_template( 'template_html' );			
			$local_file    = $this->get_theme_template_file( $template );			
			
			if ( file_exists( $local_file ) && is_writable( $local_file )){	
				//echo $local_file;exit;			
				return wc_get_template_html(
					$this->template_html,
					array(
						'order'         => $this->object,
						'email_heading' => $this->get_heading(),
						'sent_to_admin' => false,
						'plain_text'    => false,
						'email'         => $this,
					)
				);
			} else{
				$order = $this->object;
				$order_id = $order->get_id();
				$mailer = WC()->mailer();
				
				$email_heading = $this->get_heading();
				$ast = new WC_Advanced_Shipment_Tracking_Actions;
				$wcast_initialise_customizer_email = new wcast_initialise_customizer_email;
				//ob_start();	
				$woocommerce_customer_delivered_order_settings = get_option('woocommerce_customer_delivered_order_settings');
				
				
				$email_content = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_delivered_email_content',$wcast_initialise_customizer_email->defaults['wcast_delivered_email_content']);
				
				$wcast_show_tracking_details  = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_show_tracking_details','');
				$wcast_show_order_details     = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_show_order_details','');	
				$wcast_show_billing_address   = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_show_billing_address','');	
				$wcast_show_shipping_address  = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_show_shipping_address','');
				
				$message = wc_advanced_shipment_tracking_email_class()->email_content($email_content,$order_id,$order);
				
				$wcast_enable_delivered_ga_tracking = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_enable_delivered_ga_tracking','');
				$wcast_delivered_analytics_link = $ast->get_option_value_from_array('woocommerce_customer_delivered_order_settings','wcast_delivered_analytics_link','');
				
				if($wcast_delivered_analytics_link && $wcast_enable_delivered_ga_tracking == 1){	
					$regex = '#(<a href=")([^"]*)("[^>]*?>)#i';
					$message = preg_replace_callback($regex, array( $this, '_appendCampaignToString'), $message);	
				}
				
				$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
				$sent_to_admin = false;
				$plain_text = false;
				
				ob_start();
				do_action( 'wcast_email_before_email_content', $order, $sent_to_admin, $plain_text, $this );
				$message .= ob_get_clean();
				
				if($wcast_show_tracking_details == 1){			
					ob_start();					
					$local_template	= get_stylesheet_directory().'/woocommerce/emails/tracking-info.php';				
					if ( file_exists( $local_template ) && is_writable( $local_template )){
						wc_get_template( 'emails/tracking-info.php', array( 
							'tracking_items' => $wast->get_tracking_items( $order_id, true ), 
							'order_id'=> $order_id 
						), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
					} else{
						wc_get_template( 'emails/tracking-info.php', array( 
							'tracking_items' => $wast->get_tracking_items( $order_id, true ),
							'order_id' => $order_id						
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
						'email'         => $this,
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
				ob_start();
				do_action( 'wcast_email_after_email_content', $order, $sent_to_admin, $plain_text, $this );
				$message .= ob_get_clean();	
				
				// create a new email
				$email = new WC_Email();
				$email->id = 'WC_Delivered_email';			
				
				// wrap the content with the email template and then add styles
				$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );				
				return $message;	
			}			
		}	
		
		/**
		 * code for append analytics link into rl inside email content.
		 */
		public function _appendCampaignToString($match){
			$woocommerce_customer_delivered_order_settings = get_option('woocommerce_customer_delivered_order_settings');
			$url = $match[2];
			if (strpos($url, '?') === false) {
				$url .= '?';
			}
			$url .= $woocommerce_customer_delivered_order_settings['wcast_delivered_analytics_link'];
			return $match[1].$url.$match[3];
		}	

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {			
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'woocommerce' ),					
					'default' => 'yes',
				),
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'woocommerce' ),
					'type'        => 'text',
					/* translators: %s: WP admin email */
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '{customer_email}',
					'desc_tip'    => true,
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => __( 'Available variables:', 'woo-advanced-shipment-tracking' ).' {site_title}, {order_date}, {order_number}',
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => __( 'Available variables:', 'woo-advanced-shipment-tracking' ).' {site_title}, {order_date}, {order_number}',
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
				'edit_in_customizer' => array(
					'type'			=> 'title',
					'description'	=> '<a href="'.wcast_initialise_customizer_email::get_customizer_url( 'custom_order_status_email','delivered' ).'" >'.__( 'Click Here', 'woo-advanced-shipment-tracking' ).'</a>',
					'title'			=> __( 'Edit in customizer', 'woo-advanced-shipment-tracking' ),
				),
			);
		}
	}

endif;

return new WC_Email_Customer_Delivered_Order();