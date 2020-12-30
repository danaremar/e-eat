<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Customizer {

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
			
    }
	
	/**
	 * Register the Customizer panels
	 */
	public function wcast_add_customizer_panels( $wp_customize ) {
		
		/**
		* Tracking Display Customizer Panel
		*/
		$wp_customize->add_panel( 'ast_tracking_display_panel',
			array(
				'title' => __( 'Tracking info display', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' )
			)
		);
		
		/**
		* Add our Header & Navigation Panel
		*/
		$wp_customize->add_panel( 'wcast_naviation_panel',
			array(
				'title' => __( 'Order Status Emails', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' )
			)
		);
		
		/**
		* Add our Header & Navigation Panel
		*/
		$wp_customize->add_panel( 'wcast_emails_panel',
			array(
				'title' => __( 'Shipment Status Emails', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' )
			)
		);
	}
	
	/**
	 * Register the Customizer sections
	 */
	public function wcast_add_customizer_sections( $wp_customize ) {	
		
		$wp_customize->add_section( 'ast_tracking_general_section',
			array(
				'title' => __( 'Tracking info display', 'woo-advanced-shipment-tracking' ),
				'description' => '',
				//'panel' => 'ast_tracking_display_panel'
			)
		);				
		
		$wp_customize->add_section( 'custom_order_status_email',
			array(
				'title' => __( 'Custom order status email', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				//'panel' => 'wcast_naviation_panel'
			)
		);								
		
		$wp_customize->add_section( 'custom_shipment_status_email',
			array(
				'title' => __( 'Shipment Status Email', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				//'panel' => 'wcast_emails_panel'
			)
		);	

		$wp_customize->add_section( 'customer_onhold_email',
			array(
				'title' => __( 'On Hold', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_emails_panel'
			)
		);			
		
		$wp_customize->add_section( 'customer_returntosender_email',
			array(
				'title' => __( 'Return To Sender', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_emails_panel'
			)
		);	
		$wp_customize->add_section( 'customer_availableforpickup_email',
			array(
				'title' => __( 'Available For Pickup', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_emails_panel'
			)
		);	
		$wp_customize->add_section( 'customer_outfordelivery_email',
			array(
				'title' => __( 'Out For Delivery', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_emails_panel'
			)
		);
		
		$wp_customize->add_section( 'customer_delivered_status_email',
			array(
				'title' => __( 'Delivered', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_emails_panel'
			)
		);	

		$wp_customize->add_section( 'customer_failure_email',
			array(
				'title' => __( 'Failed Attempt', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_emails_panel'
			)
		);	

		$wp_customize->add_section( 'admin_late_shipments_email',
			array(
				'title' => __( 'Late Shipments', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),
				'panel' => 'wcast_admin_emails_panel'
			)
		);			
	}
	
	/**
	 * add css and js for customizer
	*/
	public function enqueue_customizer_scripts(){
		$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings'); 		
		if(isset( $_REQUEST['wcast-customizer'] ) && '1' === $_REQUEST['wcast-customizer']){
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style('wcast-customizer-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/customizer-styles.css', array(), wc_advanced_shipment_tracking()->version  );			
			wp_enqueue_script('wcast-customizer-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/customizer-scripts.js', array('jquery', 'customize-controls','wp-color-picker'), wc_advanced_shipment_tracking()->version, true);
			
			$email_type = 'delivered';
			if(isset( $_REQUEST['order_status'] )){
				$email_type = $_REQUEST['order_status'];
			}

			$shipment_status = 'in_transit';
			if(isset( $_REQUEST['shipment_status'] )){
				$shipment_status = $_REQUEST['shipment_status'];
			}	
	
			// Send variables to Javascript
			wp_localize_script('wcast-customizer-scripts', 'wcast_customizer', array(
				'ajax_url'              => admin_url('admin-ajax.php'),
				'email_preview_url'        => $this->get_email_preview_url(),
				'partial_shipped_email_preview_url' => $this->get_partial_shipped_email_preview_url(),				
				'updated_tracking_email_preview_url' => $this->get_updated_tracking_email_preview_url(),
				'email_type' => $email_type,
				'shipment_status' => $shipment_status,
				'tracking_preview_url'        => $this->get_tracking_preview_url(),				
				'customer_failure_preview_url'  => $this->get_customer_failure_preview_url(),
				'customer_intransit_preview_url'  => $this->get_customer_intransit_preview_url(),
				'customer_onhold_preview_url'  => $this->get_customer_onhold_preview_url(),
				'customer_outfordelivery_preview_url' => $this->get_customer_outfordelivery_preview_url(),
				'customer_delivered_preview_url' => $this->get_customer_delivered_preview_url(),
				'customer_returntosender_preview_url' => $this->get_customer_returntosender_preview_url(),
				'customer_availableforpickup_preview_url' => $this->get_customer_availableforpickup_preview_url(),
				'admin_late_shipments_preview_url' => $this->get_admin_late_shipments_preview_url(),
				'trigger_click'        => '#accordion-section-'.$_REQUEST['email'].' h3',
				'wcast_enable_delivered_email' => $wcast_enable_delivered_email['enabled'],	
			));	

			wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array(
				'clear'            => __( 'Clear' ),
				'clearAriaLabel'   => __( 'Clear color' ),
				'defaultString'    => __( 'Default' ),
				'defaultAriaLabel' => __( 'Select default color' ),
				'pick'             => __( 'Select Color' ),
				'defaultLabel'     => __( 'Color value' ),
			));	
		}
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_email_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wcast-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_partial_shipped_email_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wcast-partial-shipped-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_custom_completed_email_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wcast-custom-completed-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_updated_tracking_email_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wcast-updated-tracking-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_tracking_preview_url() {		
			$tracking_preview_url = add_query_arg( array(
				'wcast-tracking-preview' => '1',
			), home_url( '' ) );		

		return $tracking_preview_url;
	}		
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_failure_preview_url() {		
			$customer_failure_preview_url = add_query_arg( array(
				'wcast-failure-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_failure_preview_url;
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_intransit_preview_url() {		
			$customer_intransit_preview_url = add_query_arg( array(
				'wcast-intransit-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_intransit_preview_url;
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_onhold_preview_url() {		
			$customer_onhold_preview_url = add_query_arg( array(
				'wcast-onhold-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_onhold_preview_url;
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_outfordelivery_preview_url() {		
			$customer_intransit_preview_url = add_query_arg( array(
				'wcast-outfordelivery-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_intransit_preview_url;
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_delivered_preview_url() {		
			$customer_intransit_preview_url = add_query_arg( array(
				'wcast-delivered-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_intransit_preview_url;
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_returntosender_preview_url() {		
			$customer_intransit_preview_url = add_query_arg( array(
				'wcast-returntosender-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_intransit_preview_url;
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public static function get_customer_availableforpickup_preview_url() {		
			$customer_intransit_preview_url = add_query_arg( array(
				'wcast-availableforpickup-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $customer_intransit_preview_url;
	}
	
	public function get_admin_late_shipments_preview_url(){
		$admin_late_shipments_preview_url = add_query_arg( array(
				'wcast-late-shipments-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $admin_late_shipments_preview_url;
	}
	
	/**
     * Remove unrelated components
     *
     * @access public
     * @param array $components
     * @param object $wp_customize
     * @return array
     */
    public function remove_unrelated_components($components, $wp_customize)	{
        // Iterate over components
        foreach ($components as $component_key => $component) {

            // Check if current component is own component
            if ( ! $this->is_own_component( $component ) ) {
                unset($components[$component_key]);
            }
        }

        // Return remaining components
        return $components;
    }

    /**
     * Remove unrelated sections
     *
     * @access public
     * @param bool $active
     * @param object $section
     * @return bool
     */
    public function remove_unrelated_sections( $active, $section ) {
        // Check if current section is own section
        if ( ! $this->is_own_section( $section->id ) ) {
            return false;
        }

        // We can override $active completely since this runs only on own Customizer requests
        return true;
    }

	/**
	* Remove unrelated controls
	*
	* @access public
	* @param bool $active
	* @param object $control
	* @return bool
	*/
	public function remove_unrelated_controls( $active, $control ) {
		
		// Check if current control belongs to own section
		if ( ! wcast_add_customizer_sections::is_own_section( $control->section ) ) {
			return false;
		}

		// We can override $active completely since this runs only on own Customizer requests
		return $active;
	}

	/**
	* Check if current component is own component
	*
	* @access public
	* @param string $component
	* @return bool
	*/
	public static function is_own_component( $component ) {
		return false;
	}

	/**
	* Check if current section is own section
	*
	* @access public
	* @param string $key
	* @return bool
	*/
	public static function is_own_section( $key ) {
				
		if ($key === 'wcast_naviation_panel' || $key === 'ast_tracking_general_section' || $key === 'custom_order_status_email' || $key === 'custom_shipment_status_email' ) {
			return true;
		}

		// Section not found
		return false;
	}
	
	/*
	 * Unhook flatsome front end.
	 */
	public function unhook_flatsome() {
		// Unhook flatsome issue.
		wp_dequeue_style( 'flatsome-customizer-preview' );
		wp_dequeue_script( 'flatsome-customizer-frontend-js' );
	}
	
	/*
	 * Unhook Divi front end.
	 */
	public function unhook_divi() {
		// Divi Theme issue.
		remove_action( 'wp_footer', 'et_builder_get_modules_js_data' );
		remove_action( 'et_customizer_footer_preview', 'et_load_social_icons' );
	}
	
	/**
	 * Get Order Ids
	 *
	 * @access public
	 * @return array
	 */
	public static function get_order_ids() {		
		$order_array = array();
		$order_array['mockup'] = __( 'Mockup order', 'woo-advanced-shipment-tracking' );
		
		$orders = wc_get_orders( array(
			'limit'        => 20,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'meta_key'     => '_wc_shipment_tracking_items', // The postmeta key field
			'meta_compare' => 'EXISTS', // The comparison argument
		));	
			
		foreach ( $orders as $order ) {				
				$ast = new WC_Advanced_Shipment_Tracking_Actions;
				$tracking_items = $ast->get_tracking_items( $order->get_id(), true );
				if($tracking_items){
					$order_array[ $order->get_id() ] = $order->get_id() . ' - ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();					
				}				
			}
		return $order_array;
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
function wcast_customizer() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new wc_advanced_shipment_tracking_customizer();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wcast_customizer();