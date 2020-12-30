<?php
/**
 * Class WC_Email_Customer_Pickup_Order file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Customer_Pickup_Order', false ) ) :

	/**
	 * Customer Completed Order Email.
	 *
	 * Order complete emails are sent to the customer when the order is marked complete and usual indicates that the order has been shipped.
	 *
	 * @class       WC_Email_Customer_Pickup_Order
	 * @version     2.0.0
	 * @package     WooCommerce/Classes/Emails
	 * @extends     WC_Email
	 */
	class WC_Email_Customer_Pickup_Order extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_pickup_order';
			$this->customer_email = true;
			$this->title          = __( 'Picked up order', 'advanced-local-pickup-for-woocommerce' );
			$this->description    = __( 'Picked up Order emails are sent to customers when their orders are marked picked up.', 'advanced-local-pickup-for-woocommerce' );
			$this->template_html  = 'emails/pickup-order.php';
			$this->template_plain = 'emails/plain/pickup-order.php';
			$this->placeholders   = array(
				'{customer_first_name}'   => '',
				'{order_date}'   => '',
				'{order_number}' => '',
			);
			$this->template_base = WC_LOCAL_PICKUP_TEMPLATE_PATH;
			// Triggers for this email.		
			//add_action( 'woocommerce_order_status_pickup', array( $this, 'trigger' ), 10, 2 );
			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{customer_first_name}']   = $this->object->get_billing_first_name();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
			return __( 'Your {site_title} order is now picked up', 'advanced-local-pickup-for-woocommerce' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Your Order is picked up', 'advanced-local-pickup-for-woocommerce' );
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
				return wc_get_template_html(
					$this->template_html,
					array(
						'order'         => $this->object,
						'email_heading' => $this->get_heading(),
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin' => false,
						'plain_text'    => false,
						'email'         => $this,
					)
				);
			} else{	
				return wc_get_template_html(
					'emails/pickup-order.php',
					array(
						'order'              => $this->object,
						'email_heading'      => $this->get_heading(),
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin'      => false,
						'plain_text'         => false,
						'email'              => $this,
					),
					'woocommerce-local-pickup/', 
					wc_local_pickup()->get_plugin_path() . '/templates/'
				);
			}
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			$template = $this->get_template( 'template_html' );			
			$local_file    = $this->get_theme_template_file( $template );
			if ( file_exists( $local_file ) && is_writable( $local_file )){						
				return wc_get_template_html(
					$this->template_html,
					array(
						'order'         => $this->object,
						'email_heading' => $this->get_heading(),
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin' => false,
						'plain_text'    => false,
						'email'         => $this,
					)
				);
			} else{	
				return wc_get_template_html(
					'emails/pickup-order.php',
					array(
						'order'              => $this->object,
						'email_heading'      => $this->get_heading(),
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin'      => false,
						'plain_text'         => false,
						'email'              => $this,
					),
					'woocommerce-local-pickup/', 
					wc_local_pickup()->get_plugin_path() . '/templates/'
				);
			}
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_additional_content() {
			return __( "Hi there. we thought you'd like to know that your recent order from {site_title} has been picked up.", 'advanced-local-pickup-for-woocommerce' );
		}
	}

endif;

return new WC_Email_Customer_Pickup_Order();