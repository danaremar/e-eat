<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wcast_delivered_customizer_email {
	// Get our default values	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wcast_generate_defaults();
		
		$wc_ast_api_key = get_option('wc_ast_api_key');
		if(!$wc_ast_api_key){
			return;
		}
		
		// Register our sample default controls
		add_action( 'customize_register', array( $this, 'wcast_register_sample_default_controls' ) );
		
		// Only proceed if this is own request.
		if ( ! wcast_delivered_customizer_email::is_own_customizer_request() && ! wcast_delivered_customizer_email::is_own_preview_request() ) {
			return;
		}					
		add_action( 'customize_register', array( wcast_customizer(), 'wcast_add_customizer_panels' ) );
		// Register our sections
		add_action( 'customize_register', array( wcast_customizer(), 'wcast_add_customizer_sections' ) );	
		
		// Remove unrelated components.
		add_filter( 'customize_loaded_components', array( wcast_customizer(), 'remove_unrelated_components' ), 99, 2 );

		// Remove unrelated sections.
		add_filter( 'customize_section_active', array( wcast_customizer(), 'remove_unrelated_sections' ), 10, 2 );	
		
		// Unhook divi front end.
		add_action( 'woomail_footer', array( wcast_customizer(), 'unhook_divi' ), 10 );

		// Unhook Flatsome js
		add_action( 'customize_preview_init', array( wcast_customizer(), 'unhook_flatsome' ), 50  );
		
		add_filter( 'customize_controls_enqueue_scripts', array( wcast_customizer(), 'enqueue_customizer_scripts' ) );				
		
		add_action( 'parse_request', array( $this, 'set_up_preview' ) );	
		
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );	

	}
	
	/**
	 * add css and js for preview
	*/
	public function enqueue_preview_scripts() {		 
		wp_enqueue_script('wcast-email-preview-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_advanced_shipment_tracking()->version, true);
		wp_enqueue_style('wcast-preview-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_advanced_shipment_tracking()->version  );
		 		// Send variables to Javascript
		$preview_id     = get_theme_mod('wcast_email_preview_order_id');
		wp_localize_script('wcast-email-preview-scripts', 'wcast_preview', array(
			'site_title'   => $this->get_blogname(),
			'order_number' => $preview_id,			
		));
	}
	
	/**
	 * Get blog name formatted for emails.
	 *
	 * @return string
	 */
	public function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}	
	
	/**
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_preview_request() {
		return isset( $_REQUEST['wcast-delivered-email-customizer-preview'] ) && '1' === $_REQUEST['wcast-delivered-email-customizer-preview'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'custom_shipment_status_email';
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_customizer_url( $email, $shipment_status, $return_tab ) {		
		$customizer_url = add_query_arg( array(
			'wcast-customizer' => '1',
			'email' => $email,
			'shipment_status' => $shipment_status,
			'autofocus[section]' => 'custom_shipment_status_email',
			'url'                  => urlencode( add_query_arg( array( 'wcast-delivered-email-customizer-preview' => '1' ), home_url( '/' ) ) ),
			'return'               => urlencode( wcast_delivered_customizer_email::get_email_settings_page_url($return_tab) ),
		), admin_url( 'customize.php' ) );	
		return $customizer_url;
	}
	
	/**
	 * Get WooCommerce email settings page URL
	 *
	 * @access public
	 * @return string
	 */
	public static function get_email_settings_page_url($return_tab) {
		return admin_url( 'admin.php?page=trackship-for-woocommerce&tab='.$return_tab );
	}
	
	/**
	 * code for initialize default value for customizer
	*/
	public function wcast_generate_defaults() {		
		$customizer_defaults = array(			
			'wcast_delivered_status_email_subject' => __( 'Your order #{order_number} has been delivered', 'woo-advanced-shipment-tracking' ),
			'wcast_delivered_status_email_heading' => __( 'Delivered', 'woo-advanced-shipment-tracking' ),
			'wcast_delivered_status_email_content' => __( "Hi there. we thought you'd like to know that your recent order from {site_title} has been Delivered.", 'woo-advanced-shipment-tracking' ),				
			'wcast_enable_delivered_status_email'  => '',
			'wcast_delivered_status_email_to'  => 	'{customer_email}',
			'wcast_delivered_status_show_tracking_details' => '',
			'wcast_delivered_status_show_order_details' => '',
			'wcast_delivered_status_show_billing_address' => '',
			'wcast_delivered_status_show_shipping_address' => '',
			'wcast_delivered_status_email_code_block' => '',
		);

		return apply_filters( 'skyrocket_customizer_defaults', $customizer_defaults );
	}
	
	/**
	 * Register our sample default controls
	 */
	public function wcast_register_sample_default_controls( $wp_customize ) {		
		/**
		* Load all our Customizer Custom Controls
		*/
		require_once trailingslashit( dirname(__FILE__) ) . 'custom-controls.php';
							
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_enable_delivered_status_email]',
			array(
				'default' => $this->defaults['wcast_enable_delivered_status_email'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_enable_delivered_status_email]',
			array(
				'label' => __( 'Enable Delivered email', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);				
			
		// Header Text		
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_email_to]',
			array(
				'default' => $this->defaults['wcast_delivered_status_email_to'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_email_to]',
			array(
				'label' => __( 'Recipient(s)', 'woocommerce' ),
				'description' => esc_html__( 'Use the {customer_email} placeholder, you can add comma separated email addresses.', 'woocommerce' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'E.g. {customer.email}, admin@example.org', 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);		
		
		// Header Text		
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_email_subject]',
			array(
				'default' => $this->defaults['wcast_delivered_status_email_subject'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_email_subject]',
			array(
				'label' => __( 'Email Subject', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( 'Available variables:', 'woo-advanced-shipment-tracking' ).' {site_title}, {order_number}',
				'section' => 'custom_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( $this->defaults['wcast_delivered_status_email_subject'], 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Header Text		
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_email_heading]',
			array(
				'default' => $this->defaults['wcast_delivered_status_email_heading'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_email_heading]',
			array(
				'label' => __( 'Email heading', 'woocommerce' ),
				'description' => esc_html__( 'Available variables:', 'woo-advanced-shipment-tracking' ).' {site_title}, {order_number}',
				'section' => 'custom_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( $this->defaults['wcast_delivered_status_email_heading'], 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Test of TinyMCE control
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_email_content]',
			array(
				'default' => $this->defaults['wcast_delivered_status_email_content'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => 'wp_kses_post'
			)
		);
		$wp_customize->add_control( new Skyrocket_TinyMCE_Custom_control( $wp_customize, 'wcast_delivered_email_settings[wcast_delivered_status_email_content]',
			array(
				'label' => __( 'Email content', 'woo-advanced-shipment-tracking' ),
				'description' => __( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'input_attrs' => array(
					'toolbar1' => 'bold italic bullist numlist alignleft aligncenter alignright link',
					'mediaButtons' => true,
					'placeholder' => __( $this->defaults['wcast_delivered_status_email_content'], 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		) );
		
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_email_code_block]',
			array(
				'default' => $this->defaults['wcast_delivered_status_email_code_block'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_codeinfoblock_Control( $wp_customize, 'wcast_delivered_email_settings[wcast_delivered_status_email_code_block]',
			array(
				'label' => __( 'Available variables:', 'woo-advanced-shipment-tracking' ),
				'description' => '<code>{site_title}<br>{customer_email}<br>{customer_first_name}<br>{customer_last_name}<br>{customer_company_name}<br>{customer_username}<br>{order_number}<br>{est_delivery_date}</code>',
				'section' => 'custom_shipment_status_email',				
				'active_callback' => array( $this, 'active_callback' ),
			)
		) );
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_show_tracking_details]',
			array(
				'default' => $this->defaults['wcast_delivered_status_show_tracking_details'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_show_tracking_details]',
			array(
				'label' => __( 'Show tracking details', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_show_order_details]',
			array(
				'default' => $this->defaults['wcast_delivered_status_show_order_details'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_show_order_details]',
			array(
				'label' => __( 'Show order details', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_show_billing_address]',
			array(
				'default' => $this->defaults['wcast_delivered_status_show_billing_address'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_show_billing_address]',
			array(
				'label' => __( 'Show billing address', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_show_shipping_address]',
			array(
				'default' => $this->defaults['wcast_delivered_status_show_shipping_address'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_show_shipping_address]',
			array(
				'label' => __( 'Show shipping address', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		$wp_customize->add_setting( 'wcast_delivered_email_settings[wcast_delivered_status_analytics_link]',
			array(
				'default' => '',
				'transport' => 'refresh',
				'type'  => 'option',				
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_delivered_email_settings[wcast_delivered_status_analytics_link]',
			array(
				'label' => __( 'Google Analytics link tracking', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( 'This will be appended to URL in the email content', 'woo-advanced-shipment-tracking' ),
				'section' => 'custom_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( '', 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);				
	}	
	
	public function active_callback(){
		if ( wcast_delivered_customizer_email::is_own_preview_request() ) {
			return true;
		} else{
			return false;
		}
	}
	
	/**
	 * Set up preview
	 *
	 * @access public
	 * @return void
	 */
	public function set_up_preview() {
		
		// Make sure this is own preview request.
		if ( ! wcast_delivered_customizer_email::is_own_preview_request() ) {
			return;
		}
		include wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/preview/delivered_status_preview.php';		
		exit;			
	}
	
	/**
	 * code for preview of delivered email
	*/
	public function preview_delivered_email(){
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();		
		$preview_id     = get_theme_mod('wcast_intransit_email_preview_order_id');
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		
		$order_id = $ast->get_custom_order_number( $preview_id );
		
		$email_heading = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_email_heading',$this->defaults['wcast_delivered_status_email_heading']);		
		$email_heading = str_replace( '{site_title}', $this->get_blogname(), $email_heading );
		$email_heading =  str_replace( '{order_number}', $order_id, $email_heading );
		
		$email_content = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_email_content',$this->defaults['wcast_delivered_status_email_content']);				
		
		$wcast_show_tracking_details = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_tracking_details',$this->defaults['wcast_delivered_status_show_tracking_details']);
		
		$wcast_show_order_details = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_order_details',$this->defaults['wcast_delivered_status_show_order_details']);
		
		$wcast_show_billing_address = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_billing_address',$this->defaults['wcast_delivered_status_show_billing_address']);
		
		$wcast_show_shipping_address = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_show_shipping_address',$this->defaults['wcast_delivered_status_show_shipping_address']);
		
		$sent_to_admin = false;
		$plain_text = false;
		$email = '';
		
		if($preview_id == '' || $preview_id == 'mockup') {
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'woo-advanced-shipment-tracking' ) . '</div>';							
			echo $content;
			return;
		}		
		$order = wc_get_order( $preview_id );
		
		if(!$order){
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'woo-advanced-shipment-tracking' ) . '</div>';							
			echo $content;
			return;
		}
		
		$mailer = WC()->mailer();
				
		// get the preview email subject
		$email_heading = __( $email_heading, 'woo-advanced-shipment-tracking' );
		//ob_start();
		
		$message = wc_trackship_email_manager()->email_content($email_content,$preview_id,$order);
		
		$wcast_delivered_status_analytics_link = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_analytics_link','');		
				
		if($wcast_delivered_status_analytics_link){	
			$regex = '#(<a href=")([^"]*)("[^>]*?>)#i';
			$message = preg_replace_callback($regex, array( $this, '_appendCampaignToString'), $message);	
		}
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		
		if($wcast_show_tracking_details == 1){			
			ob_start();			
			$local_template	= get_stylesheet_directory().'/woocommerce/emails/tracking-info.php';			
			if ( file_exists( $local_template ) && is_writable( $local_template )){				
				wc_get_template( 'emails/tracking-info.php', array( 
					'tracking_items' => $wast->get_tracking_items( $preview_id, true ),
					'order_id' => $preview_id,
					'new_status' => 'delivered',
				), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
			} else{
				wc_get_template( 'emails/tracking-info.php', array( 
					'tracking_items' => $wast->get_tracking_items( $preview_id, true ),
					'order_id' => $preview_id,
					'new_status' => 'delivered',
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
				'email'         => $email,
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
		$email->id = 'WC_Delivered_email';
		
		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );
		echo $message;			
	}

	/**
	 * code for append analytics link in email content
	*/
	public function _appendCampaignToString($match){
		$ast = new WC_Advanced_Shipment_Tracking_Actions;	
		$wcast_delivered_status_analytics_link = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_delivered_status_analytics_link','');
		
		$url = $match[2];
		if (strpos($url, '?') === false) {
			$url .= '?';
		}
		$url .= $wcast_delivered_status_analytics_link;
		return $match[1].$url.$match[3];
	}	
}
/**
 * Initialise our Customizer settings
 */

$wcast_delivered_customizer_email = new wcast_delivered_customizer_email();