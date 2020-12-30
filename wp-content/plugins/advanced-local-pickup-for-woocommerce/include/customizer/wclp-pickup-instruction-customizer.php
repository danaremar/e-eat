<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wclp_pickup_instruction_customizer {
	// Get our default values	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wclp_generate_defaults();
						
		// Register our sample default controls
		add_action( 'customize_register', array( $this, 'wclp_register_sample_default_controls' ) );
		
		// Only proceed if this is own request.		
		if ( ! wclp_pickup_instruction_customizer::is_own_customizer_request() && ! wclp_pickup_instruction_customizer::is_own_preview_request() ) {
			return;
		}	
			
		add_action( 'customize_register', array( wclp_customizer(), 'wclp_add_customizer_panels' ) );
		// Register our sections
		add_action( 'customize_register', array( wclp_customizer(), 'wclp_add_customizer_sections' ) );	
		
		// Remove unrelated components.
		add_filter( 'customize_loaded_components', array( wclp_customizer(), 'remove_unrelated_components' ), 99, 2 );

		// Remove unrelated sections.
		add_filter( 'customize_section_active', array( wclp_customizer(), 'remove_unrelated_sections' ), 10, 2 );	
		
		// Unhook divi front end.
		add_action( 'woomail_footer', array( wclp_customizer(), 'unhook_divi' ), 10 );

		// Unhook Flatsome js
		add_action( 'customize_preview_init', array( wclp_customizer(), 'unhook_flatsome' ), 50  );
		
		add_filter( 'customize_controls_enqueue_scripts', array( wclp_customizer(), 'enqueue_customizer_scripts' ) );				
		
		add_action( 'parse_request', array( $this, 'set_up_preview' ) );	
		
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );					
	}
	
	public function enqueue_preview_scripts() {		 
		wp_enqueue_script('wclp-instruction-preview-scripts', wc_local_pickup()->plugin_dir_url() . 'assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_local_pickup()->version, true);
		wp_enqueue_style('wclp-instrction-preview-styles', wc_local_pickup()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_local_pickup()->version  );
				// Send variables to Javascript
		$preview_id     = get_theme_mod('wclp_pickup_instruction_preview_order_id');
		wp_localize_script('wclp-instruction-preview-scripts', 'wclp_preview', array(
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
		return isset( $_REQUEST['wclp-pickup-instruction-customizer-preview'] ) && '1' === $_REQUEST['wclp-pickup-instruction-customizer-preview'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'pickup_instaruction';
	}

	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_customizer_url($email) {		
		$customizer_url = add_query_arg( array(
			'wclp-customizer' => '1',
			'email' => $email,
			'url'                  => urlencode( add_query_arg( array( 'wclp-pickup-instruction-customizer-preview' => '1' ), home_url( '/' ) ) ),
			'return'               => urlencode( wclp_pickup_instruction_customizer::get_email_settings_page_url() ),
		), admin_url( 'customize.php' ) );		

	return $customizer_url;
	}		
	
	/**
	 * Get WooCommerce email settings page URL
	 *
	 * @access public
	 * @return string
	 */
	public static function get_email_settings_page_url() {
		return admin_url( 'admin.php?page=local_pickup' );
	}
	
	/**
	 * code for initialize default value for customizer
	*/
	public function wclp_generate_defaults() {
		$customizer_defaults = array(				
			'hide_table_header'  => '',
			'hide_table_header'  => '',
			'header_address_text'  => __( 'Pickup Address', 'advanced-local-pickup-for-woocommerce' ),
			'header_business_text'  => __( 'Business Hours', 'advanced-local-pickup-for-woocommerce' ),
			'header_font_size'  => '14px',
			'location_box_font_size'  => '13px',
			'header_font_color'  => '#333',			
			'location_box_border_size'  => '1px',
			'location_box_font_color'  => '#333',
			'location_box_border_color'  => '#ccc',
			'location_box_background_color' => '#fff',
			'header_background_color' => '#fafafa',
			'location_box_content_line_height' => '20px',
			'location_box_heading' => __( 'Pickup Instruction', 'advanced-local-pickup-for-woocommerce' ),
		);

		return apply_filters( 'skyrocket_customizer_defaults', $customizer_defaults );
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
				if( $order_object->has_shipping_method('local_pickup') ) { 
					$order_array[ $order_object->get_id() ] = $order_object->get_id() . ' - ' . $order_object->get_billing_first_name() .' ' .	$order_object->get_billing_last_name();
				}
			}
		}
		
		return $order_array;
	}	

	/**
	 * Register our sample default controls
	 */
	public function wclp_register_sample_default_controls( $wp_customize ) {		
		/**
		* Load all our Customizer Custom Controls
		*/
		require_once trailingslashit( dirname(__FILE__) ) . 'custom-controls.php';
		
		$font_size_array[ '' ] = __( 'Select', 'woocommerce' );
		for ( $i = 10; $i <= 30; $i++ ) {
			$font_size_array[ $i ] = $i."px";
		}

		// Preview Order		
		$wp_customize->add_setting( 'wclp_pickup_instruction_preview_order_id',
			array(
				'default' => 'mockup',
				'transport' => 'refresh',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WPLP_Skyrocket_Dropdown_Select_Custom_Control( $wp_customize, 'wclp_pickup_instruction_preview_order_id',
			array(
				'label' => __( 'Preview order', 'advanced-local-pickup-for-woocommerce' ),
				'description' => __( 'Select an order to preview and design the pickup instructions display.', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'input_attrs' => array(
					'placeholder' => __( 'Please select a order...', 'advanced-local-pickup-for-woocommerce' ),
					'class' => 'preview_order_select',
				),
				'choices' => $this->get_order_ids(),
			)
		) );
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'pickup_instruction_display_settings[hide_instruction_heading]',
			array(
				'default' => '',
				'transport' => 'refresh',
				'type'      => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[hide_instruction_heading]',
			array(
				'label' => __( 'Hide Local Pickup Section Heading', 'advanced-local-pickup-for-woocommerce' ),
				'description' => esc_html__( '', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'checkbox',
				
			)
		);
		
		// Header Text		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_heading]',
			array(
				'default' => __( 'Pickup Instruction', 'advanced-local-pickup-for-woocommerce' ),
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_heading]',
			array(
				'label' => __( 'Local Pickup section heading text', 'advanced-local-pickup-for-woocommerce' ),				
				'section' => 'pickup_instaruction',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' =>  __( 'Pickup Instruction', 'advanced-local-pickup-for-woocommerce' ),
				),
			)
		);
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_border_color]',
			array(
				'default' => '#ccc',
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_border_color]',
			array(
				'label' => __( 'Table Border color', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'color'
			)
		);
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_border_size]',
			array(
				'default' => '1px',
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_border_size]',
			array(
				'label' => __( 'Table Border size', 'advanced-local-pickup-for-woocommerce' ),
				'description' => '',
				'section' => 'pickup_instaruction',
				'type' => 'select',
				'input_attrs' => array(
					'class' => '',
					'style' => 'width:100px;',
					'placeholder' => __( 'Business Hours', 'advanced-local-pickup-for-woocommerce' ),
				),
				'choices' => array(
					'0px' => __( 'Select', 'woocommerce' ),
					'1px'		=> '1 px',
					'2px'		=> '2 px',
					'3px'		=> '3 px',
					'4px'		=> '4 px',
					'5px'		=> '5 px',
				)
			)
		);
		
		$wp_customize->add_setting( 'wclp_table_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		
		$wp_customize->add_control( new WPLP_Customize_Heading_Control( $wp_customize, 'wclp_table_header',
			array(
				'label' => __( 'TABLE HEADERS', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction'
			)
		) );							
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'pickup_instruction_display_settings[hide_table_header]',
			array(
				'default' => '',
				'transport' => 'refresh',
				'type'      => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[hide_table_header]',
			array(
				'label' => __( 'Hide Table Headers', 'advanced-local-pickup-for-woocommerce' ),
				'description' => esc_html__( '', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'checkbox',
				
			)
		);

		// Header Text		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[header_address_text]',
			array(
				'default' => __( 'Pickup Address', 'advanced-local-pickup-for-woocommerce' ),
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[header_address_text]',
			array(
				'label' => __( 'Pickup address header text', 'advanced-local-pickup-for-woocommerce' ),
				'description' => '',
				'section' => 'pickup_instaruction',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Pickup Address', 'advanced-local-pickup-for-woocommerce' ),
				),
			)
		);
		
		// Header Text		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[header_business_text]',
			array(
				'default' => __( 'Business Hours', 'advanced-local-pickup-for-woocommerce' ),
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[header_business_text]',
			array(
				'label' => __( 'Business Hours header text', 'advanced-local-pickup-for-woocommerce' ),
				'description' => '',
				'section' => 'pickup_instaruction',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Business Hours', 'advanced-local-pickup-for-woocommerce' ),
				),
			)
		);
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[header_background_color]',
			array(
				'default' => '#fafafa',
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[header_background_color]',
			array(
				'label' => __( 'Headers Background color', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'color'
			)
		);
		
		
		// Header Text		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[header_font_size]',
			array(
				'default' => '14px',
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[header_font_size]',
			array(
				'label' => __( 'Headers font size', 'advanced-local-pickup-for-woocommerce' ),
				'description' => '',
				'section' => 'pickup_instaruction',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => 'width:100px',
					'placeholder' => '14px',
				),
			)
		);
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[header_font_color]',
			array(
				'default' => '#333',
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[header_font_color]',
			array(
				'label' => __( 'Headers font color', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'color'
			)
		);
		
		$wp_customize->add_setting( 'wclp_table_content',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WPLP_Customize_Heading_Control( $wp_customize, 'wclp_table_content',
			array(
				'label' => __( 'TABLE CONTENT', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction'
			)
		) );		
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_background_color]',
			array(
				'default' => '#fff',
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_background_color]',
			array(
				'label' => __( 'Background color', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'color'
			)
		);
		
		// Header Text		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_font_size]',
			array(
				'default' => '13px',
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_font_size]',
			array(
				'label' => __( 'Content font size', 'advanced-local-pickup-for-woocommerce' ),
				'description' => '',
				'section' => 'pickup_instaruction',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => 'width:100px',
					'placeholder' => '0px',
				),
			)
		);
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_font_color]',
			array(
				'default' => '#333',
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_font_color]',
			array(
				'label' => __( 'Content font color', 'advanced-local-pickup-for-woocommerce' ),
				'section' => 'pickup_instaruction',
				'type' => 'color'
			)
		);
		
		$wp_customize->add_setting( 'pickup_instruction_display_settings[location_box_content_line_height]',
			array(
				'default' => '20px',
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'pickup_instruction_display_settings[location_box_content_line_height]',
			array(
				'label' => __( 'Content line height', 'advanced-local-pickup-for-woocommerce' ),
				'description' => '',
				'section' => 'pickup_instaruction',
				'type' => 'select',
				'input_attrs' => array(
					'class' => '',
					'style' => 'width:100px;',
					'placeholder' => __( 'Business Hours', 'advanced-local-pickup-for-woocommerce' ),
				),
				'choices' => array(
					'' => __( 'Select', 'woocommerce' ),
					'13px'		=> '13 px',
					'14px'		=> '14 px',
					'15px'		=> '15 px',
					'16px'		=> '16 px',
					'17px'		=> '17 px',
					'18px'		=> '18 px',
					'19px'		=> '19 px',
					'20px'		=> '20 px',
					'21px'		=> '21 px',
					'22px'		=> '22 px',
					'23px'		=> '23 px',
					'24px'		=> '24 px',
				)
			)
		);

		
	}
		
	/**
	 * Set up preview
	 *
	 * @access public
	 * @return void
	 */
	public function set_up_preview() {
		
		// Make sure this is own preview request.
		if ( ! wclp_pickup_instruction_customizer::is_own_preview_request() ) {
			return;
		}
		include wc_local_pickup()->get_plugin_path() . '/include/customizer/preview/pickup_instaruction_preview.php';		
		exit;			
	}
	
	/**
	 * code for preview of delivered order status email
	*/
	public function preview_ready_pickup_email(){
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();		
		$preview_id     = get_theme_mod('wclp_pickup_instruction_preview_order_id');
		
		$email_type = 'WC_Email_Customer_Ready_Pickup_Order';
		
		if ( false === $email_type ) {
			return false;
		}
		 						
		// Reference email.
		if ( isset( $emails[ $email_type ] ) && is_object( $emails[ $email_type ] ) ) {
			$email = $emails[ $email_type ];
		}
		
		if($preview_id == '' || $preview_id == 'mockup') {
			ob_start();
			
			require_once( wc_local_pickup()->get_plugin_path().'/include/views/wclp_pickup_location_instruction_preview.php' );
			$message = ob_get_clean();	
			$email = new WC_Email();
			$email_heading = 'Your Order is ready for pickup';		
			$mailer = WC()->mailer();
			// wrap the content with the email template and then add styles
			$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );
			echo '<div class="notice" style="width: 100%;padding: 10px;background: #000;color: #fff;text-align: center;"><span>Note -  if you do not have any orders to preview, check that you have at least one order with Local Pickup shipping method.</span></div>'.$message;
			return;
		}		
		
		$order = wc_get_order( $preview_id );
		
		if(!$order){
			ob_start();
			require_once( wc_local_pickup()->get_plugin_path().'/include/views/wclp_pickup_location_instruction_preview.php' );
			$message = ob_get_clean();	
			$email = new WC_Email();
			$email_heading = 'Your Order is ready for pickup';		
			$mailer = WC()->mailer();
			// wrap the content with the email template and then add styles
			$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );
			echo '<div class="notice" style="width: 100%;padding: 10px;background: #000;color: #fff;text-align: center;"><span>Note -  if you do not have any orders to preview, check that you have at least one order with Local Pickup shipping method.</span></div>'.$message;
			return;
		}							
		
		$order_status = 'ready-pickup';
		// Get an order
		$order = self::get_wc_order_for_preview( $order_status, $preview_id );		
		
		// Make sure gateways are running in case the email needs to input content from them.
		WC()->payment_gateways();
		// Make sure shipping is running in case the email needs to input content from it.
		WC()->shipping();
		
		$email->object               = $order;
		
		$woocommerce_customer_ready_pickup_order_settings = get_option('woocommerce_customer_ready_pickup_order_settings');
		$email->settings['additional_content'] =  $woocommerce_customer_ready_pickup_order_settings['additional_content'];		
		
		$email->find['customer-first-name']   = '{customer_first_name}';
		$email->find['customer-last-name']   = '{customer_last_name}';
		$email->find['customer-email']   = '{customer_email}';
		//$email->find['customer-username']   = '{customer_username}';
		$email->find['order-date']   = '{order_date}';
		$email->find['order-number'] = '{order_number}';
		if ( is_object( $order ) ) {
			$email->replace['customer-first-name'] = $email->object->get_billing_first_name();
			$email->replace['customer-last-name'] = $email->object->get_billing_last_name();
			$email->replace['customer-email'] = $email->object->get_billing_email();
			//$email->replace['customer-username'] = $email->object->get_user();
			$email->replace['order-date']   = wc_format_datetime( $email->object->get_date_created() );
			$email->replace['order-number'] = $email->object->get_order_number();
			// Other properties
			$email->recipient = $email->object->get_billing_email();
		}
		
		// Get email content and apply styles.
		$content = $email->get_content();
		
		$content = $email->style_inline( $content );
		$content = apply_filters( 'woocommerce_mail_content', $content );
		
		echo $content;		
	}
	
	/**
	 * Get WooCommerce order for preview
	 *
	 * @access public
	 * @param string $order_status
	 * @return object
	 */
	public static function get_wc_order_for_preview( $order_status = null, $order_id = null ) {
		if ( ! empty( $order_id ) && 'mockup' != $order_id ) {
			return wc_get_order( $order_id );
		} else {
			// Use mockup order

			// Instantiate order object
			$order = new WC_Order();

			// Other order properties
			$order->set_props( array(
				'id'                 => 1,
				'status'             => ( null === $order_status ? 'processing' : $order_status ),
				'billing_first_name' => 'Sherlock',
				'billing_last_name'  => 'Holmes',
				'billing_company'    => 'Detectives Ltd.',
				'billing_address_1'  => '221B Baker Street',
				'billing_city'       => 'London',
				'billing_postcode'   => 'NW1 6XE',
				'billing_country'    => 'GB',
				'billing_email'      => 'sherlock@holmes.co.uk',
				'billing_phone'      => '02079304832',
				'date_created'       => date( 'Y-m-d H:i:s' ),
				'total'              => 24.90,
			) );

			// Item #1
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'A Study in Scarlet',
				'subtotal' => '9.95',
				'sku'      => 'kwd_ex_1',
			) );
			$order->add_item( $order_item );

			// Item #2
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'The Hound of the Baskervilles',
				'subtotal' => '14.95',
				'sku'      => 'kwd_ex_2',
			) );
			$order->add_item( $order_item );

			// Return mockup order
			return $order;
		}

	}	
}
/**
 * Initialise our Customizer settings
 */

new wclp_pickup_instruction_customizer();