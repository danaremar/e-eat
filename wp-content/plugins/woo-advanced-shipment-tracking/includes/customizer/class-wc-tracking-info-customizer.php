<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wcast_initialise_customizer_settings {
	// Get our default values	
	private static $order_ids  = null;
	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wcast_generate_defaults();		
		
		// Register our sample default controls
		add_action( 'customize_register', array( $this, 'wcast_register_sample_default_controls' ) );
		
		// Only proceed if this is own request.
		if ( ! wcast_initialise_customizer_settings::is_own_customizer_request() && ! wcast_initialise_customizer_settings::is_own_preview_request() ) {
			return;
		}		
		
		// Register our Panels
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
		 wp_enqueue_script('wcast-preview-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . '/assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_advanced_shipment_tracking()->version, true);
		 wp_enqueue_style('wcast-preview-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_advanced_shipment_tracking()->version  );
		 $preview_id     = get_theme_mod('wcast_email_preview_order_id');
		 wp_localize_script('wcast-preview-scripts', 'wcast_preview', array(
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
		return isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'ast_tracking_general_section';
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_customizer_url($email,$return_tab) {	
			//echo $return_tab;exit;
			$customizer_url = add_query_arg( array(
				'wcast-customizer' => '1',
				'email' => $email,		
				'autofocus[section]' => 'ast_tracking_general_section',	
				'url'                  => urlencode( add_query_arg( array( 'wcast-tracking-preview' => '1' ), home_url( '/' ) ) ),
				'return'               => urlencode( wcast_initialise_customizer_settings::get_email_settings_page_url($return_tab) ),
				//'autofocus[panel]' => 'ast_tracking_display_panel',
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
		return admin_url( 'admin.php?page=woocommerce-advanced-shipment-tracking&tab='.$return_tab );
	}
	
	/**
	 * code for initialize default value for customizer
	*/	
	public function wcast_generate_defaults() {
		$customizer_defaults = array(
			'display_shipment_provider_image' => 1,
			'display_shipment_provider_name' => 1,
			'remove_date_from_tracking' => '',
			'header_text_change' => '',
			'additional_header_text' => '',
			'table_bg_color' => '#ffffff',
			'table_border_color' => '#e0e0e0',
			'table_border_size' => '1',
			'table_header_font_size' => '',
			'table_header_font_color' => '',
			'table_header_bg_color' => '#fafafa',
			'table_header_font_weight' => '400',
			'table_content_font_size' => '12',
			'table_content_font_color' => '#212121',
			'tracking_link_font_color' => '',
			'tracking_link_bg_color' => '',	
			'wcast_preview_order_id' => 'mockup',
			'table_content_line_height' => '20',
			'table_content_font_weight' => '400',
			'table_padding'  => '12',
			'header_content_text_align'  => 'left',
			'tracking_link_border' => 1,
			'show_track_label' => '',
			'provider_header_text' => __( 'Provider', 'woo-advanced-shipment-tracking' ),
			'tracking_number_header_text' => __( 'Tracking Number', 'woo-advanced-shipment-tracking' ),
			'shipped_date_header_text' => __( 'Shipped Date', 'woo-advanced-shipment-tracking' ),
			'track_header_text' => __( 'Track', 'woo-advanced-shipment-tracking' ),
			'display_tracking_info_at' => 'before_order',
			'select_tracking_template' => 'default_table',			
			'simple_provider_font_size' => '14',
			'simple_provider_font_color' => '#212121',
			'show_provider_border' => 1,
			'provider_border_color' => '#e0e0e0',
			'simple_layout_content' => __( 'Shipped on {ship_date} via {shipping_provider} - {tracking_number_link}', 'woo-advanced-shipment-tracking' ),
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
		
		$font_size_array[ '' ] = __( 'Select', 'woocommerce' );
		for ( $i = 10; $i <= 30; $i++ ) {
			$font_size_array[ $i ] = $i."px";
		}
		
		// Preview Order				
		$wp_customize->add_setting( 'wcast_preview_order_id',
			array(
				'default' => $this->defaults['wcast_preview_order_id'],
				'transport' => 'refresh',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new Skyrocket_Dropdown_Select_Custom_Control( $wp_customize, 'wcast_preview_order_id',
			array(
				'label' => __( 'Preview order', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( 'Select an order to preview and design the tracking info display.', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'input_attrs' => array(
					'placeholder' => __( 'Please select a order...', 'woo-advanced-shipment-tracking' ),
					'class' => 'preview_order_select',
				),
				'choices' => wcast_customizer()->get_order_ids(),
			)
		) );				
		
		// Tracking Display Position
		$wp_customize->add_setting( 'tracking_info_settings[display_tracking_info_at]',
			array(
				'default' => $this->defaults['display_tracking_info_at'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[display_tracking_info_at]',
			array(
				'label' => __( 'Tracking Display Position', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'select',
				'choices' => array(					
					'before_order'		=> __( 'Before Order Details', 'woo-advanced-shipment-tracking' ),
					'after_order'		=> __( 'After Order Details', 'woo-advanced-shipment-tracking' ),							
				)
			)
		);
		
		// Show track label
		$wp_customize->add_setting( 'tracking_info_settings[hide_trackig_header]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[hide_trackig_header]',
			array(
				'label' => __( 'Hide Tracking Header', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);
			
		// Header Text		
		$wp_customize->add_setting( 'tracking_info_settings[header_text_change]',
			array(
				'default' => $this->defaults['header_text_change'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[header_text_change]',
			array(
				'label' => __( 'Tracking Header text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Tracking Information', 'woo-advanced-shipment-tracking' ),
				),
			)
		);
		
		// Additional text after header
		$wp_customize->add_setting( 'tracking_info_settings[additional_header_text]',
			array(
				'default' => $this->defaults['additional_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[additional_header_text]',
			array(
				'label' => __( 'Additional text after header', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'textarea',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' =>'',
				),
			)
		);	

		// Tracking display layout			
		$wp_customize->add_setting( 'tracking_info_settings[select_tracking_template]',
			array(
				'default' => $this->defaults['select_tracking_template'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new Skyrocket_Dropdown_Select_Custom_Control( $wp_customize, 'tracking_info_settings[select_tracking_template]',
			array(
				'label' => __( 'Tracking display layout', 'woo-advanced-shipment-tracking' ),				
				'section' => 'ast_tracking_general_section',
				'input_attrs' => array(
					'placeholder' => __( 'Tracking display layout', 'woo-advanced-shipment-tracking' ),
					'class' => 'tracking_template_select',
				),
				'choices' => array(
					'' => __( 'Select Template', 'woo-advanced-shipment-tracking' ),
					'default_table' => __( 'Table Layout', 'woo-advanced-shipment-tracking' ),
					'simple_list' => 'Simple Layout',
				),
			)
		) );	
				
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'tracking_info_settings[simple_content_header]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'tracking_info_settings[simple_content_header]',
			array(
				'label' => __( 'Simple Layout Design', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section'
			)
		) );
		
		// Additional text after header
		$wp_customize->add_setting( 'tracking_info_settings[simple_layout_content]',
			array(
				'default' => $this->defaults['simple_layout_content'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[simple_layout_content]',
			array(
				'label' => __( 'Content', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'textarea',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' =>'',
				),
			)
		);	

		$wp_customize->add_setting( 'tracking_info_settings[simple_content_variables]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_codeinfoblock_Control( $wp_customize, 'tracking_info_settings[simple_content_variables]',
			array(
				'label' => __( 'Available variables:', 'woo-advanced-shipment-tracking' ),
				'description' => '<code>{ship_date}<br>{shipping_provider}<br>{tracking_number_link}</code>',
				'section' => 'ast_tracking_general_section',				
			)
		) );	

		// Simple Layout Provider font size
		$wp_customize->add_setting( 'tracking_info_settings[simple_provider_font_size]',
			array(
				'default' => $this->defaults['simple_provider_font_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[simple_provider_font_size]',
			array(
				'label' => __( 'Content font size', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'select',
				'choices' => $font_size_array
			)
		);
		
		// Table header font color
		$wp_customize->add_setting( 'tracking_info_settings[simple_provider_font_color]',
			array(
				'default' => $this->defaults['simple_provider_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[simple_provider_font_color]',
			array(
				'label' => __( 'Content font color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);
		
		// Show track label
		$wp_customize->add_setting( 'tracking_info_settings[show_provider_border]',
			array(
				'default' => $this->defaults['show_provider_border'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[show_provider_border]',
			array(
				'label' => __( 'Show bottom border', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);
		
		// Table header font color
		$wp_customize->add_setting( 'tracking_info_settings[provider_border_color]',
			array(
				'default' => $this->defaults['provider_border_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[provider_border_color]',
			array(
				'label' => __( 'Bottom border color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);		
		
		
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'tracking_info_settings[table_content_header]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'tracking_info_settings[table_content_header]',
			array(
				'label' => __( 'Table Options', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section'
			)
		) );
		
		// Hide Shipment Provider name
		$wp_customize->add_setting( 'tracking_info_settings[display_shipment_provider_name]',
			array(
				'default' => $this->defaults['display_shipment_provider_name'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[display_shipment_provider_name]',
			array(
				'label' => __( 'Display shipping provider name', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'tracking_info_settings[display_shipment_provider_image]',
			array(
				'default' => $this->defaults['display_shipment_provider_image'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[display_shipment_provider_image]',
			array(
				'label' => __( 'Display shipping provider image', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);			
		
		// Remove date from tracking info
		$wp_customize->add_setting( 'tracking_info_settings[remove_date_from_tracking]',
			array(
				'default' => $this->defaults['remove_date_from_tracking'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[remove_date_from_tracking]',
			array(
				'label' => __( 'Hide the shipped date', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);
		
		// Use tracking number as a link
		$wp_customize->add_setting( 'tracking_info_settings[tracking_number_link]',
			array(
				'default' => '',
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[tracking_number_link]',
			array(
				'label' => __( 'Use tracking number as a link', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'tracking_info_settings[table_design_options]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'tracking_info_settings[table_design_options]',
			array(
				'label' => __( 'Table Design Options', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section'
			)
		) );
		
		// Table Border color
		$wp_customize->add_setting( 'tracking_info_settings[table_border_color]',
			array(
				'default' => $this->defaults['table_border_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_border_color]',
			array(
				'label' => __( 'Border color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);
		
		// Table Border size
		$wp_customize->add_setting( 'tracking_info_settings[table_border_size]',
			array(
				'default' => $this->defaults['table_border_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_border_size]',
			array(
				'label' => __( 'Border size', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'select',
				'choices' => array(
					'' => __( 'Select', 'woocommerce' ),
					'1'		=> '1 px',
					'2'		=> '2 px',
					'3'		=> '3 px',
					'4'		=> '4 px',
					'5'		=> '5 px',
				)
			)
		);
		
		// Table Border size
		$wp_customize->add_setting( 'tracking_info_settings[header_content_text_align]',
			array(
				'default' => $this->defaults['header_content_text_align'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[header_content_text_align]',
			array(
				'label' => __( 'Text align', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'select',
				'choices' => array(
					'' => __( 'Select', 'woocommerce' ),
					'left'		=> __( 'Left', '' ),
					'right'		=> __( 'Right', '' ),
					'center'	=> __( 'Center', '' )
				)
			)
		);
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'tracking_info_settings[table_header_block]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'tracking_info_settings[table_header_block]',
			array(
				'label' => __( 'Table Header', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section'
			)
		) );
		
		// Show track label
		$wp_customize->add_setting( 'tracking_info_settings[hide_table_header]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[hide_table_header]',
			array(
				'label' => __( 'Hide Table Headers', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox'
			)
		);
		
		// Provider Header Text		
		$wp_customize->add_setting( 'tracking_info_settings[provider_header_text]',
			array(
				'default' => $this->defaults['provider_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[provider_header_text]',
			array(
				'label' => __( 'Shipping provider header text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Provider', 'woo-advanced-shipment-tracking' ),
				),
			)
		);
		
		// Tracking Number Header Text		
		$wp_customize->add_setting( 'tracking_info_settings[tracking_number_header_text]',
			array(
				'default' => $this->defaults['tracking_number_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[tracking_number_header_text]',
			array(
				'label' => __( 'Tracking number header text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Tracking Number', 'woo-advanced-shipment-tracking' ),
				),
			)
		);
		// Shipped Date Header Text		
		$wp_customize->add_setting( 'tracking_info_settings[shipped_date_header_text]',
			array(
				'default' => $this->defaults['shipped_date_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[shipped_date_header_text]',
			array(
				'label' => __( 'Shipped date header text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Shipped Date', 'woo-advanced-shipment-tracking' ),
				),
			)
		);	

		// Table header font size
		$wp_customize->add_setting( 'tracking_info_settings[table_header_font_size]',
			array(
				'default' => $this->defaults['table_header_font_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_header_font_size]',
			array(
				'label' => __( 'Headers font size', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'select',
				'choices' => $font_size_array
			)
		);												
		
		// Table header font color
		$wp_customize->add_setting( 'tracking_info_settings[table_header_bg_color]',
			array(
				'default' => $this->defaults['table_header_bg_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_header_bg_color]',
			array(
				'label' => __( 'Headers background color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);
		
		// Table header font color
		$wp_customize->add_setting( 'tracking_info_settings[table_header_font_color]',
			array(
				'default' => $this->defaults['table_header_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_header_font_color]',
			array(
				'label' => __( 'Headers font color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);
		
		// Table content font weight
		$wp_customize->add_setting( 'tracking_info_settings[table_header_font_weight]',
			array(
				'default' => $this->defaults['table_header_font_weight'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'tracking_info_settings[table_header_font_weight]',
			array(
				'label' => __( 'Headers font weight', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_header_font_weight'],
						'step'  => 100,
						'min'   => 400,
						'max'   => 900,
					),
			)
		));																
		
		// Test of Toggle Switch Custom Control
		$wp_customize->add_setting( 'table_header',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'table_header',
			array(
				'label' => __( 'Table Content', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section'
			)
		) );
		
		// Table Background color
		$wp_customize->add_setting( 'tracking_info_settings[table_bg_color]',
			array(
				'default' => $this->defaults['table_bg_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => 'sanitize_hex_color',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_bg_color]',
			array(
				'label' => __( 'Content Background color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color',				
			)
		);	
		
		// Table content font color
		$wp_customize->add_setting( 'tracking_info_settings[table_content_font_color]',
			array(
				'default' => $this->defaults['table_content_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_content_font_color]',
			array(
				'label' => __( 'Content font color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);												
		
		// Table content font size
		$wp_customize->add_setting( 'tracking_info_settings[table_content_font_size]',
			array(
				'default' => $this->defaults['table_content_font_size'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[table_content_font_size]',
			array(
				'label' => __( 'Content font size', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'select',
				'choices' => $font_size_array
			)
		);				
		
		// Table content line height
		$wp_customize->add_setting( 'tracking_info_settings[table_content_line_height]',
			array(
				'default' => $this->defaults['table_content_line_height'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'tracking_info_settings[table_content_line_height]',
			array(
				'label' => __( 'Content line height', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_content_line_height'],
						'step'  => 1,
						'min'   => 20,
						'max'   => 90,
					),
			)
		));
		
		// Table content font weight
		$wp_customize->add_setting( 'tracking_info_settings[table_content_font_weight]',
			array(
				'default' => $this->defaults['table_content_font_weight'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'tracking_info_settings[table_content_font_weight]',
			array(
				'label' => __( 'Content font weight', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'input_attrs' => array(
						'default' => $this->defaults['table_content_font_weight'],
						'step'  => 100,
						'min'   => 400,
						'max'   => 900,
					),
			)
		));	
		
		$wp_customize->add_setting( 'tracking_info_settings[shipment_link_header]',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);	
		
		$wp_customize->add_control( new WP_Customize_Heading_Control( $wp_customize, 'tracking_info_settings[shipment_link_header]',
			array(
				'label' => __( 'Track Button', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section'
			)
		) );
		
		// Show track label
		$wp_customize->add_setting( 'tracking_info_settings[show_track_label]',
			array(
				'default' => $this->defaults['show_track_label'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[show_track_label]',
			array(
				'label' => __( 'Track Header', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'checkbox',			
			)
		);
		
		// Track Header Text		
		$wp_customize->add_setting( 'tracking_info_settings[track_header_text]',
			array(
				'default' => $this->defaults['track_header_text'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[track_header_text]',
			array(
				'label' => __( 'Track header text', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'Track', 'woo-advanced-shipment-tracking' ),
				),
			)
		);	
		
		// Tracking link background color
		$wp_customize->add_setting( 'tracking_info_settings[tracking_link_bg_color]',
			array(
				'default' => $this->defaults['tracking_link_bg_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[tracking_link_bg_color]',
			array(
				'label' => __( 'Button color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
			)
		);	
		
		// Tracking link font color
		$wp_customize->add_setting( 'tracking_info_settings[tracking_link_font_color]',
			array(
				'default' => $this->defaults['tracking_link_font_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'tracking_info_settings[tracking_link_font_color]',
			array(
				'label' => __( 'Button font color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_general_section',
				'type' => 'color'
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
		if ( ! wcast_initialise_customizer_settings::is_own_preview_request() ) {
			return;
		}
		include wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/preview/preview.php';		
		exit;			
	}
	
	/**
	 * code for preview of tracking info in email
	*/	
	public function preview_completed_email(){
		
		$ast = new WC_Advanced_Shipment_Tracking_Actions;				
				
		$tracking_info_settings = get_option('tracking_info_settings');		
					
		if($tracking_info_settings['display_tracking_info_at'] == 'after_order'){			
			add_action( 'woocommerce_email_order_meta', array( $ast, 'email_display' ), 0, 4 );
		} else{
			add_action( 'woocommerce_email_before_order_table', array( $ast, 'email_display' ), 0, 4 );
		}	
		
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();
		$email_template = 'customer_completed_order';
		$preview_id     = get_theme_mod('wcast_preview_order_id');
		$email_type = 'WC_Email_Customer_Completed_Order';
		if ( false === $email_type ) {
			return false;
		}

		$order_status = 'completed';
		
		if($preview_id == '' || $preview_id == 'mockup') {							
			
			//$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'To preview the tracking display, please add tracking information to at least one order and choose it in the preview order selection.', 'woo-advanced-shipment-tracking' ) . '</div>';							
			//echo $content;
			//return;
		}		
		
		// Reference email.
		if ( isset( $emails[ $email_type ] ) && is_object( $emails[ $email_type ] ) ) {
			$email = $emails[ $email_type ];
		}
		
		// Get an order
		$order = self::get_wc_order_for_preview( $order_status, $preview_id );		
		
		// Make sure gateways are running in case the email needs to input content from them.
		WC()->payment_gateways();
		// Make sure shipping is running in case the email needs to input content from it.
		WC()->shipping();
			
		$email->object               = $order;
		$email->find['order-date']   = '{order_date}';
		$email->find['order-number'] = '{order_number}';
		if ( is_object( $order ) ) {
			$email->replace['order-date']   = wc_format_datetime( $email->object->get_date_created() );
			$email->replace['order-number'] = $email->object->get_order_number();
			// Other properties
			$email->recipient = $email->object->get_billing_email();
		}
		// Get email content and apply styles.
		$content = $email->get_content();
		$content = $email->style_inline( $content );
		$content = apply_filters( 'woocommerce_mail_content', $content );

		if ( 'plain' === $email->email_type ) {
			$content = '<div style="padding: 35px 40px; background-color: white;">' . str_replace( "\n", '<br/>', $content ) . '</div>';
		}
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

$wcast_customizer_settings = new wcast_initialise_customizer_settings();