<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Local_pickup_Customizer {

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
	public function wclp_add_customizer_panels( $wp_customize ) {
		
		/**
		* Add our Header & Navigation Panel
		*/
		$wp_customize->add_panel( 'wclp_naviation_panel',
			array(
				'title' => __( 'Local Pickup Order Status Emails', 'advanced-local-pickup-for-woocommerce' ),
				'description' => esc_html__( '', 'advanced-local-pickup-for-woocommerce' )
			)
		);
		
	}
	
	/**
	 * Register the Customizer sections
	 */
	public function wclp_add_customizer_sections( $wp_customize ) {	
		
		$wp_customize->add_section( 'customer_ready_pickup_email',
			array(
				'title' => __( 'Ready for Pickup order status email', 'advanced-local-pickup-for-woocommerce' ),
				'description' => esc_html__( '', 'advanced-local-pickup-for-woocommerce'  ),
				'panel' => 'wclp_naviation_panel'
			)
		);
		
		$wp_customize->add_section( 'customer_pickup_email',
			array(
				'title' => __( 'Picked up order status email', 'advanced-local-pickup-for-woocommerce' ),
				'description' => esc_html__( '', 'advanced-local-pickup-for-woocommerce'  ),
				'panel' => 'wclp_naviation_panel'
			)
		);
		
		$wp_customize->add_section( 'pickup_instaruction',
			array(
				'title' => __( 'Pickup Instructions Layout', 'advanced-local-pickup-for-woocommerce' ),
				'description' => esc_html__( '', 'advanced-local-pickup-for-woocommerce'  ),
				'panel' => 'wclp_naviation_panel'
			)
		);
				
	}
	
	/**
	 * add css and js for customizer
	*/
	public function enqueue_customizer_scripts(){
		$wclp_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings'); 		
		if(isset( $_REQUEST['wclp-customizer'] ) && '1' === $_REQUEST['wclp-customizer']){
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker');			
			wp_enqueue_style('wclp-customizer-styles', wc_local_pickup()->plugin_dir_url() . 'assets/css/customizer-styles.css', array(), wc_local_pickup()->version  );
			wp_enqueue_script('wclp-customizer-scripts', wc_local_pickup()->plugin_dir_url() . 'assets/js/customizer-scripts.js', array('jquery', 'customize-controls'), wc_local_pickup()->version, true);
	
			// Send variables to Javascript
			wp_localize_script('wclp-customizer-scripts', 'wclp_customizer', array(
				'ajax_url'              => admin_url('admin-ajax.php'),
				'pickup_email_preview_url'        => $this->get_pickup_email_preview_url(),
				'ready_pickup_email_preview_url' => $this->get_ready_pickup_email_preview_url(),
				'pickup_instaruction_preview_url'        => $this->get_pickup_instaruction_preview_url(),
				'trigger_click'        => '#accordion-section-'.$_REQUEST['email'].' h3',
			));		
		}
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_pickup_email_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wclp-pickup-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_ready_pickup_email_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wclp-ready-pickup-email-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_pickup_instaruction_preview_url() {		
			$email_preview_url = add_query_arg( array(
				'wclp-pickup-instruction-customizer-preview' => '1',
			), home_url( '' ) );		

		return $email_preview_url;
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
		if ( ! wclp_add_customizer_sections::is_own_section( $control->section ) ) {
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
				
		if ($key === 'wclp_naviation_panel' || $key === 'customer_ready_pickup_email' || $key === 'customer_pickup_email' || $key === 'pickup_instaruction' ) {
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
		$order_array['mockup'] = __( 'Select order to preview', 'advanced-local-pickup-for-woocommerce' );
		
		$orders = new WP_Query(
			array(
				'post_type'      => 'shop_order',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'posts_per_page' => 20,
			)
		);
		
		if ( $orders->posts ) {
			foreach ( $orders->posts as $order ) {
				// Get order object.
				$order_object = new WC_Order( $order->ID );
				$order_array[ $order_object->get_id() ] = $order_object->get_id() . ' - ' . $order_object->get_billing_first_name() . ' ' . $order_object->get_billing_last_name();
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
function wclp_customizer() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new wc_local_pickup_customizer();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
$GLOBALS['WC_Local_pickup_Customizer'] = wclp_customizer();