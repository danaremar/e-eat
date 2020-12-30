<?php
/**
 * Class WC_Email_Customer_Partial_Shipped_Order file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Customer_Partial_Shipped_Order', false ) ) :

	/**
	 * Customer Completed Order Email.
	 *
	 * Order complete emails are sent to the customer when the order is marked complete and usual indicates that the order has been shipped.
	 *
	 * @class       WC_Email_Customer_Partial_Shipped_Order
	 * @version     2.0.0
	 * @package     WooCommerce/Classes/Emails
	 * @extends     WC_Email
	 */
	class WC_Email_Customer_Partial_Shipped_Order extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_partial_shipped_order';
			$this->customer_email = true;
			$this->title          = __( 'Partially Shipped order', 'woocommerce' );
			$this->description    = __( 'Order partially shipped emails are sent to customers when their orders are marked partially shipped and usually indicate that their orders have been partially shipped.', 'woocommerce' );
			$this->template_html  = 'emails/customer-partial-shipped-order.php';
			$this->template_plain = 'emails/plain/customer-completed-order.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);
			$this->template_base = AST_TEMPLATE_PATH;
			// Triggers for this email.
			//add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 2 );

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
			return __( 'Your {site_title} order is now partially shipped', 'woocommerce' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Your Order is Partially Shipped', 'woocommerce' );
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
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin' => false,
						'plain_text'    => false,
						'email'         => $this,
					)
				);
			} else{	
				return wc_get_template_html(
					'emails/customer-partial-shipped-order.php',
					array(
						'order'              => $this->object,
						'email_heading'      => $this->get_heading(),
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin'      => false,
						'plain_text'         => false,
						'email'              => $this,
					),
					'woocommerce-advanced-shipment-tracking/', 
					wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
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
				//echo $local_file;exit;			
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
					'emails/customer-partial-shipped-order.php',
					array(
						'order'              => $this->object,
						'email_heading'      => $this->get_heading(),
						'additional_content' => $this->get_additional_content(),
						'sent_to_admin'      => false,
						'plain_text'         => false,
						'email'              => $this,
					),
					'woocommerce-advanced-shipment-tracking/', 
					wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
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
			return __( 'Thanks for shopping with us.', 'woocommerce' );
		}
	}

endif;

return new WC_Email_Customer_Partial_Shipped_Order();