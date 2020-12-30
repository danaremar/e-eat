<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Install {

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
		
		global $wpdb;
		$this->table = $wpdb->prefix."woo_shippment_provider";
		if( is_multisite() ){
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_plugin_active_for_network( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {
				$main_blog_prefix = $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE);			
				$this->table = $main_blog_prefix."woo_shippment_provider";	
			} else{
				$this->table = $wpdb->prefix."woo_shippment_provider";
			}			
		} else{
			$this->table = $wpdb->prefix."woo_shippment_provider";	
		}
		
		$this->init();	
    }
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Install
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init(){				
		add_action( 'init', array( $this, 'update_database_check'));		
	}	

	/**
	 * Define plugin activation function
	 *
	 * Create Table
	 *
	 * Insert data 
	 *
	 * 
	*/	
	public function woo_shippment_tracking_install(){
		
		global $wpdb;			
		// Add transient to trigger redirect.
		set_transient( '_ast_activation_redirect', 1, 30 );		
		$woo_shippment_table_name = $this->table;		
		if(!$wpdb->query($wpdb->prepare("show tables like %s",$woo_shippment_table_name))){
			$charset_collate = $wpdb->get_charset_collate();			
			$sql = "CREATE TABLE $woo_shippment_table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				provider_name varchar(500) DEFAULT '' NOT NULL,
				ts_slug text NULL DEFAULT NULL,
				provider_url varchar(500) DEFAULT '' NULL,
				shipping_country varchar(45) DEFAULT '' NULL,
				shipping_default tinyint(4) NULL DEFAULT '0',
				custom_thumb_id int(11) NOT NULL DEFAULT '0',
				display_in_order tinyint(4) NOT NULL DEFAULT '1',
				trackship_supported int(11) NOT NULL DEFAULT '0',
				sort_order int(11) NOT NULL DEFAULT '0',				
				PRIMARY KEY  (id)
			) $charset_collate;";			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
						
			$this->update_shipping_providers();
			
			update_option( 'wc_advanced_shipment_tracking', '3.14');	
		}
		
		$wc_ast_default_mark_shipped = get_option('wc_ast_default_mark_shipped');
		if($wc_ast_default_mark_shipped == ''){
			update_option('wc_ast_default_mark_shipped',1);
		}
		
		$wc_ast_unclude_tracking_info = get_option('wc_ast_unclude_tracking_info');
		if(empty($wc_ast_unclude_tracking_info)){	
			$data_array = array('completed' => 1,'partial-shipped' => 1,'updated-tracking' => 1);
			update_option( 'wc_ast_unclude_tracking_info', $data_array );	
		}

		$wc_ast_show_orders_actions = get_option('wc_ast_show_orders_actions');
		if(empty($wc_ast_show_orders_actions)){	
			$data_array = array('processing' => 1,'completed' => 1,'partial-shipped' => 1,'updated-tracking' => 1);
			update_option( 'wc_ast_show_orders_actions', $data_array );	
		}		
	}		
	
	/*
	* database update
	*/
	public function update_database_check(){					
		if ( is_admin() ){					
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'1.2', '<') ){							
				global $wpdb;
				$results = $wpdb->get_row( "SELECT * FROM $this->table LIMIT 1" );
				if(!isset($results->sort_order)) {
					$res = $wpdb->query( "ALTER TABLE $this->table ADD sort_order int(11) NOT NULL DEFAULT '0'" );
				}				
				update_option( 'wc_advanced_shipment_tracking', '1.2');				
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'1.3', '<') ){			
				global $wpdb;
				$results = $wpdb->get_row( "SELECT * FROM $this->table LIMIT 1" );				
				if(!isset($results->custom_thumb_id)) {
					$res = $wpdb->query( "ALTER TABLE $this->table ADD custom_thumb_id int(11) NOT NULL DEFAULT '0'" );
				}			
				update_option( 'wc_advanced_shipment_tracking', '1.3');				
			}								
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.3', '<') ){
				global $wpdb;				
				$results = $wpdb->get_row( "SELECT * FROM $this->table LIMIT 1" );				
				if(!isset($results->ts_slug)) {					
					$res = $wpdb->query( "ALTER TABLE $this->table ADD ts_slug text NULL DEFAULT NULL AFTER provider_name" );
				}
				$this->update_shipping_providers();
				update_option( 'wc_advanced_shipment_tracking', '3.3');	
			}			
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.4', '<') ){
				$multi_checkbox_data = get_option('wc_ast_unclude_tracking_info');
				$data_array = array('completed' => 1);
				if($multi_checkbox_data){	
					$data_array = array_merge($multi_checkbox_data,$data_array);
				}				
				update_option( 'wc_ast_unclude_tracking_info', $data_array );
				update_option( 'wc_advanced_shipment_tracking', '3.4');					
			}	
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.5', '<') ){
				$multi_checkbox_data = get_option('wc_ast_unclude_tracking_info');
				$data_array = array('partial-shipped' => 1);
				if($multi_checkbox_data){	
					$data_array = array_merge($multi_checkbox_data,$data_array);
				}				
				update_option( 'wc_ast_unclude_tracking_info', $data_array );
				update_option( 'wc_advanced_shipment_tracking', '3.5');					
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.6', '<') ){				
				set_theme_mod('display_shipment_provider_name', 1);
				set_theme_mod('select_tracking_template', 'default_table');				
				set_theme_mod('simple_provider_font_size', '14');
				set_theme_mod('simple_provider_font_color', '#575f6d');
				set_theme_mod('show_provider_border', 1);
				set_theme_mod('provider_border_color', '#e0e0e0');
				update_option( 'wc_advanced_shipment_tracking', '3.6');					
			}						
			$wcast_customizer_settings = new wcast_initialise_customizer_settings();
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.8', '<') ){	
				
				$opt = array(
					'display_tracking_info_at' => get_theme_mod('display_tracking_info_at',$wcast_customizer_settings->defaults['display_tracking_info_at']),
					'select_tracking_template' => get_theme_mod('select_tracking_template',$wcast_customizer_settings->defaults['select_tracking_template']),
					'header_text_change' => get_theme_mod('header_text_change',$wcast_customizer_settings->defaults['header_text_change']),
					'additional_header_text' => get_theme_mod('additional_header_text',$wcast_customizer_settings->defaults['additional_header_text']),
					'simple_provider_font_size' => get_theme_mod('simple_provider_font_size',$wcast_customizer_settings->defaults['simple_provider_font_size']),
					'simple_provider_font_color' => get_theme_mod('simple_provider_font_color',$wcast_customizer_settings->defaults['simple_provider_font_color']),
					'show_provider_border' => get_theme_mod('show_provider_border',$wcast_customizer_settings->defaults['show_provider_border']),
					'provider_border_color' => get_theme_mod('provider_border_color',$wcast_customizer_settings->defaults['provider_border_color']),
					'display_shipment_provider_name' => get_theme_mod('display_shipment_provider_name',$wcast_customizer_settings->defaults['display_shipment_provider_name']),
					'display_shipment_provider_image' => get_theme_mod('display_shipment_provider_image',$wcast_customizer_settings->defaults['display_shipment_provider_image']),					
					'remove_date_from_tracking' => get_theme_mod('remove_date_from_tracking',$wcast_customizer_settings->defaults['remove_date_from_tracking']),					
					'tracking_number_link' => get_theme_mod('tracking_number_link',$wcast_customizer_settings->defaults['tracking_number_link']),					
					'hide_table_header' => get_theme_mod('hide_table_header',$wcast_customizer_settings->defaults['hide_table_header']),'provider_header_text' => get_theme_mod('provider_header_text',$wcast_customizer_settings->defaults['provider_header_text']),					
					'tracking_number_header_text' => get_theme_mod('tracking_number_header_text',$wcast_customizer_settings->defaults['tracking_number_header_text']),
					'shipped_date_header_text' => get_theme_mod('shipped_date_header_text',$wcast_customizer_settings->defaults['shipped_date_header_text']),					
					'show_track_label' => get_theme_mod('show_track_label',$wcast_customizer_settings->defaults['show_track_label']),'track_header_text' => get_theme_mod('track_header_text',$wcast_customizer_settings->defaults['track_header_text']),'table_header_font_size' => get_theme_mod('table_header_font_size',$wcast_customizer_settings->defaults['table_header_font_size']),					
					'table_header_font_color' => get_theme_mod('table_header_font_color',$wcast_customizer_settings->defaults['table_header_font_color']),					
					'table_padding' => get_theme_mod('table_padding',$wcast_customizer_settings->defaults['table_padding']),'table_bg_color' => get_theme_mod('table_bg_color',$wcast_customizer_settings->defaults['table_bg_color']),'table_border_color' => get_theme_mod('table_border_color',$wcast_customizer_settings->defaults['table_border_color']),					
					'table_border_size' => get_theme_mod('table_border_size',$wcast_customizer_settings->defaults['table_border_size']),'header_content_text_align' => get_theme_mod('header_content_text_align',$wcast_customizer_settings->defaults['header_content_text_align']),					
					'table_content_font_color' => get_theme_mod('table_content_font_color',$wcast_customizer_settings->defaults['table_content_font_color']),					
					'table_content_font_size' => get_theme_mod('table_content_font_size',$wcast_customizer_settings->defaults['table_content_font_size']),					
					'table_content_line_height' => get_theme_mod('table_content_line_height',$wcast_customizer_settings->defaults['table_content_line_height']),					
					'table_content_font_weight' => get_theme_mod('table_content_font_weight',$wcast_customizer_settings->defaults['table_content_font_weight']),					
					'tracking_link_font_color' => get_theme_mod('tracking_link_font_color',$wcast_customizer_settings->defaults['tracking_link_font_color']),					
					'tracking_link_bg_color' => get_theme_mod('tracking_link_bg_color',$wcast_customizer_settings->defaults['tracking_link_bg_color']),					
					'tracking_link_border' => get_theme_mod('tracking_link_border',$wcast_customizer_settings->defaults['tracking_link_border']),					
				);
				update_option('tracking_info_settings',$opt);
				
				$wcast_delivered_order_email = new wcast_initialise_customizer_email();
				$woocommerce_customer_delivered_order_settings = get_option('woocommerce_customer_delivered_order_settings'); 
				$delivered_email_opt = array(
					'enabled' => $woocommerce_customer_delivered_order_settings['enabled'],
					'subject' => $woocommerce_customer_delivered_order_settings['subject'],
					'heading' => $woocommerce_customer_delivered_order_settings['heading'],
					'wcast_delivered_email_content' => get_theme_mod('wcast_delivered_email_content',$wcast_delivered_order_email->defaults['wcast_delivered_email_content']),
					'wcast_show_tracking_details' => get_theme_mod('wcast_show_tracking_details',$wcast_delivered_order_email->defaults['wcast_show_tracking_details']),
					'wcast_show_order_details' => get_theme_mod('wcast_show_order_details',$wcast_delivered_order_email->defaults['wcast_show_order_details']),
					'wcast_show_shipping_address' => get_theme_mod('wcast_show_shipping_address',$wcast_delivered_order_email->defaults['wcast_show_shipping_address']),
					'wcast_show_billing_address' => get_theme_mod('wcast_show_billing_address',$wcast_delivered_order_email->defaults['wcast_show_billing_address']),
					'wcast_enable_delivered_ga_tracking' => get_theme_mod('wcast_enable_delivered_ga_tracking',''),
					'wcast_delivered_analytics_link' => get_theme_mod('wcast_delivered_analytics_link',''),
				);
				update_option( 'woocommerce_customer_delivered_order_settings', $delivered_email_opt );
				
				$wcast_partial_shipped_customizer_email = new wcast_partial_shipped_customizer_email();
				$woocommerce_customer_partial_shipped_order_settings = get_option('woocommerce_customer_partial_shipped_order_settings'); 
				$partial_shipped_email_opt = array(
					'enabled' => $woocommerce_customer_partial_shipped_order_settings['enabled'],
					'subject' => $woocommerce_customer_partial_shipped_order_settings['subject'],
					'heading' => $woocommerce_customer_partial_shipped_order_settings['heading'],
					'wcast_partial_shipped_email_content' => get_theme_mod('wcast_partial_shipped_email_content',$wcast_partial_shipped_customizer_email->defaults['wcast_partial_shipped_email_content']),					
				);
				update_option( 'woocommerce_customer_partial_shipped_order_settings', $partial_shipped_email_opt );
				
				$wcast_updated_tracking_customizer_email = new wcast_updated_tracking_customizer_email();
				$woocommerce_customer_updated_tracking_order_settings = get_option('woocommerce_customer_updated_tracking_order_settings'); 
				$updated_tracking_email_opt = array(
					'enabled' => $woocommerce_customer_updated_tracking_order_settings['enabled'],
					'subject' => $woocommerce_customer_updated_tracking_order_settings['subject'],
					'heading' => $woocommerce_customer_updated_tracking_order_settings['heading'],
					'wcast_updated_tracking_email_content' => get_theme_mod('wcast_updated_tracking_email_content',$wcast_updated_tracking_customizer_email->defaults['wcast_updated_tracking_email_content']),					
				);
				update_option( 'woocommerce_customer_updated_tracking_order_settings', $updated_tracking_email_opt );
				
				$wcast_intransit_customizer_email = new wcast_intransit_customizer_email();
				$in_transit_email_opt = array(
					'wcast_enable_intransit_email' => get_theme_mod('wcast_enable_intransit_email',$wcast_intransit_customizer_email->defaults['wcast_enable_intransit_email']),
					'wcast_intransit_email_to' => get_theme_mod('wcast_intransit_email_to',$wcast_intransit_customizer_email->defaults['wcast_intransit_email_to']),
					'wcast_intransit_email_subject' => get_theme_mod('wcast_intransit_email_subject',$wcast_intransit_customizer_email->defaults['wcast_intransit_email_subject']),
					'wcast_intransit_email_heading' => get_theme_mod('wcast_intransit_email_heading',$wcast_intransit_customizer_email->defaults['wcast_intransit_email_heading']),
					'wcast_intransit_show_tracking_details' => get_theme_mod('wcast_intransit_show_tracking_details',$wcast_intransit_customizer_email->defaults['wcast_intransit_show_tracking_details']),
					'wcast_intransit_show_order_details' => get_theme_mod('wcast_intransit_show_order_details',$wcast_intransit_customizer_email->defaults['wcast_intransit_show_order_details']),
					'wcast_intransit_show_billing_address' => get_theme_mod('wcast_intransit_show_billing_address',$wcast_intransit_customizer_email->defaults['wcast_intransit_show_billing_address']),					
					'wcast_intransit_show_shipping_address' => get_theme_mod('wcast_intransit_show_shipping_address',$wcast_intransit_customizer_email->defaults['wcast_intransit_show_shipping_address']),
					'wcast_intransit_email_content' => get_theme_mod('wcast_intransit_email_content',$wcast_intransit_customizer_email->defaults['wcast_intransit_email_content']),
					'wcast_intransit_analytics_link' => get_theme_mod('wcast_intransit_analytics_link',''),
				);
				update_option( 'wcast_intransit_email_settings', $in_transit_email_opt );
				
				$wcast_returntosender_customizer_email = new wcast_returntosender_customizer_email();
				$returntosender_email_opt = array(
					'wcast_enable_returntosender_email' => get_theme_mod('wcast_enable_returntosender_email',$wcast_returntosender_customizer_email->defaults['wcast_enable_returntosender_email']),
					'wcast_returntosender_email_to' => get_theme_mod('wcast_returntosender_email_to',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_email_to']),
					'wcast_returntosender_email_subject' => get_theme_mod('wcast_returntosender_email_subject',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_email_subject']),
					'wcast_returntosender_email_heading' => get_theme_mod('wcast_returntosender_email_heading',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_email_heading']),
					'wcast_returntosender_show_tracking_details' => get_theme_mod('wcast_returntosender_show_tracking_details',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_show_tracking_details']),
					'wcast_returntosender_show_order_details' => get_theme_mod('wcast_returntosender_show_order_details',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_show_order_details']),
					'wcast_returntosender_show_billing_address' => get_theme_mod('wcast_returntosender_show_billing_address',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_show_billing_address']),	'wcast_returntosender_show_shipping_address' => get_theme_mod('wcast_returntosender_show_shipping_address',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_show_shipping_address']),
					'wcast_returntosender_email_content' => get_theme_mod('wcast_returntosender_email_content',$wcast_returntosender_customizer_email->defaults['wcast_returntosender_email_content']),
					'wcast_returntosender_analytics_link' => get_theme_mod('wcast_returntosender_analytics_link',''),
				);
				update_option( 'wcast_returntosender_email_settings', $returntosender_email_opt );
				
				$wcast_availableforpickup_customizer_email = new wcast_availableforpickup_customizer_email();
				$availableforpickup_email_opt = array(
					'wcast_enable_availableforpickup_email' => get_theme_mod('wcast_enable_availableforpickup_email',$wcast_availableforpickup_customizer_email->defaults['wcast_enable_availableforpickup_email']),
					'wcast_availableforpickup_email_to' => get_theme_mod('wcast_availableforpickup_email_to',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_email_to']),
					'wcast_availableforpickup_email_subject' => get_theme_mod('wcast_availableforpickup_email_subject',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_email_subject']),
					'wcast_availableforpickup_email_heading' => get_theme_mod('wcast_availableforpickup_email_heading',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_email_heading']),
					'wcast_availableforpickup_show_tracking_details' => get_theme_mod('wcast_availableforpickup_show_tracking_details',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_show_tracking_details']),
					'wcast_availableforpickup_show_order_details' => get_theme_mod('wcast_availableforpickup_show_order_details',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_show_order_details']),
					'wcast_availableforpickup_show_billing_address' => get_theme_mod('wcast_availableforpickup_show_billing_address',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_show_billing_address']),'wcast_availableforpickup_show_shipping_address' => get_theme_mod('wcast_availableforpickup_show_shipping_address',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_show_shipping_address']),
					'wcast_availableforpickup_email_content' => get_theme_mod('wcast_availableforpickup_email_content',$wcast_availableforpickup_customizer_email->defaults['wcast_availableforpickup_email_content']),
					'wcast_availableforpickup_analytics_link' => get_theme_mod('wcast_availableforpickup_analytics_link',''),
				);
				update_option( 'wcast_availableforpickup_email_settings', $availableforpickup_email_opt );
				
				$wcast_outfordelivery_customizer_email = new wcast_outfordelivery_customizer_email();
				$outfordelivery_email_opt = array(
					'wcast_enable_outfordelivery_email' => get_theme_mod('wcast_enable_outfordelivery_email',$wcast_outfordelivery_customizer_email->defaults['wcast_enable_outfordelivery_email']),
					'wcast_outfordelivery_email_to' => get_theme_mod('wcast_outfordelivery_email_to',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_email_to']),
					'wcast_outfordelivery_email_subject' => get_theme_mod('wcast_outfordelivery_email_subject',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_email_subject']),
					'wcast_outfordelivery_email_heading' => get_theme_mod('wcast_outfordelivery_email_heading',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_email_heading']),
					'wcast_outfordelivery_show_tracking_details' => get_theme_mod('wcast_outfordelivery_show_tracking_details',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_show_tracking_details']),
					'wcast_outfordelivery_show_order_details' => get_theme_mod('wcast_outfordelivery_show_order_details',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_show_order_details']),
					'wcast_outfordelivery_show_billing_address' => get_theme_mod('wcast_outfordelivery_show_billing_address',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_show_billing_address']),	'wcast_outfordelivery_show_shipping_address' => get_theme_mod('wcast_outfordelivery_show_shipping_address',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_show_shipping_address']),
					'wcast_outfordelivery_email_content' => get_theme_mod('wcast_outfordelivery_email_content',$wcast_outfordelivery_customizer_email->defaults['wcast_outfordelivery_email_content']),
					'wcast_outfordelivery_analytics_link' => get_theme_mod('wcast_outfordelivery_analytics_link',''),
				);
				update_option( 'wcast_outfordelivery_email_settings', $outfordelivery_email_opt );
				
				$wcast_failure_customizer_email = new wcast_failure_customizer_email();
				$failure_email_opt = array(
					'wcast_enable_failure_email' => get_theme_mod('wcast_enable_failure_email',$wcast_failure_customizer_email->defaults['wcast_enable_failure_email']),
					'wcast_failure_email_to' => get_theme_mod('wcast_failure_email_to',$wcast_failure_customizer_email->defaults['wcast_failure_email_to']),
					'wcast_failure_email_subject' => get_theme_mod('wcast_failure_email_subject',$wcast_failure_customizer_email->defaults['wcast_failure_email_subject']),
					'wcast_failure_email_heading' => get_theme_mod('wcast_failure_email_heading',$wcast_failure_customizer_email->defaults['wcast_failure_email_heading']),
					'wcast_failure_show_tracking_details' => get_theme_mod('wcast_failure_show_tracking_details',$wcast_failure_customizer_email->defaults['wcast_failure_show_tracking_details']),
					'wcast_failure_show_order_details' => get_theme_mod('wcast_failure_show_order_details',$wcast_failure_customizer_email->defaults['wcast_failure_show_order_details']),
					'wcast_failure_show_billing_address' => get_theme_mod('wcast_failure_show_billing_address',$wcast_failure_customizer_email->defaults['wcast_failure_show_billing_address']),	
					'wcast_failure_show_shipping_address' => get_theme_mod('wcast_failure_show_shipping_address',$wcast_failure_customizer_email->defaults['wcast_failure_show_shipping_address']),
					'wcast_failure_email_content' => get_theme_mod('wcast_failure_email_content',$wcast_failure_customizer_email->defaults['wcast_failure_email_content']),
					'wcast_failure_analytics_link' => get_theme_mod('wcast_failure_analytics_link',''),
				);
				update_option( 'wcast_failure_email_settings', $failure_email_opt );
				
				$wcast_delivered_customizer_email = new wcast_delivered_customizer_email();
				$delivered_status_email_opt = array(
					'wcast_enable_delivered_status_email' => get_theme_mod('wcast_enable_delivered_status_email',$wcast_delivered_customizer_email->defaults['wcast_enable_delivered_status_email']),
					'wcast_delivered_status_email_to' => get_theme_mod('wcast_delivered_status_email_to',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_to']),
					'wcast_delivered_status_email_subject' => get_theme_mod('wcast_delivered_status_email_subject',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_subject']),
					'wcast_delivered_status_email_heading' => get_theme_mod('wcast_delivered_status_email_heading',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_heading']),
					'wcast_delivered_status_show_tracking_details' => get_theme_mod('wcast_delivered_status_show_tracking_details',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_tracking_details']),
					'wcast_delivered_status_show_order_details' => get_theme_mod('wcast_delivered_status_show_order_details',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_order_details']),
					'wcast_delivered_status_show_billing_address' => get_theme_mod('wcast_delivered_status_show_billing_address',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_billing_address']),	'wcast_delivered_status_show_shipping_address' => get_theme_mod('wcast_delivered_status_show_shipping_address',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_show_shipping_address']),
					'wcast_delivered_status_email_content' => get_theme_mod('wcast_delivered_status_email_content',$wcast_delivered_customizer_email->defaults['wcast_delivered_status_email_content']),
					'wcast_delivered_status_analytics_link' => get_theme_mod('wcast_delivered_status_analytics_link',''),
				);
				update_option( 'wcast_delivered_email_settings', $delivered_status_email_opt );
				
				$wcast_late_shipments_customizer_email = new wcast_late_shipments_customizer_email();
				$late_shipments_email_opt = array(
					'wcast_enable_late_shipments_admin_email' => get_theme_mod('wcast_enable_late_shipments_admin_email',$wcast_late_shipments_customizer_email->defaults['wcast_enable_late_shipments_admin_email']),
					'wcast_late_shipments_days' => get_theme_mod('wcast_late_shipments_days',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_days']),
					'wcast_late_shipments_email_to' => get_theme_mod('wcast_late_shipments_email_to',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_email_to']),
					'wcast_late_shipments_email_subject' => get_theme_mod('wcast_late_shipments_email_subject',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_email_subject']),
					'wcast_late_shipments_email_heading' => get_theme_mod('wcast_late_shipments_email_heading',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_email_heading']),
					'wcast_late_shipments_show_tracking_details' => get_theme_mod('wcast_late_shipments_show_tracking_details',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_show_tracking_details']),
					'wcast_late_shipments_show_order_details' => get_theme_mod('wcast_late_shipments_show_order_details',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_show_order_details']),	'wcast_late_shipments_show_billing_address' => get_theme_mod('wcast_late_shipments_show_billing_address',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_show_billing_address']),
					'wcast_late_shipments_show_shipping_address' => get_theme_mod('wcast_late_shipments_show_shipping_address',$wcast_late_shipments_customizer_email->defaults['wcast_late_shipments_show_shipping_address']),
					'wcast_late_shipments_email_content' => get_theme_mod('wcast_late_shipments_email_content',''),
				);
				update_option( 'late_shipments_email_settings', $late_shipments_email_opt );
		
				update_option( 'wc_advanced_shipment_tracking', '3.8');				
			} 
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.10', '<') ){
				global $wpdb;
				$results = $wpdb->get_row( "SELECT * FROM $this->table LIMIT 1" );				
				if(!isset($results->trackship_supported)) {
					$res = $wpdb->query( "ALTER TABLE $this->table ADD trackship_supported int(11) NOT NULL DEFAULT '0'" );
				}	
				
				$url = 'https://trackship.info/wp-json/WCAST/v1/Provider';		
				$resp = wp_remote_get( $url );
				if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
					$providers = json_decode($resp['body'],true);
				}
				foreach($providers as $provider){
					$data_array = array(
						'trackship_supported' => $provider['trackship_supported'],									
					);
					$where_array = array(
						'provider_name' => $provider['shipping_provider'],			
					);					
					$wpdb->update( $this->table, $data_array, $where_array);	
				}				
				update_option( 'wc_advanced_shipment_tracking', '3.10');		
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.13', '<') ){
				$wc_ast_unclude_tracking_info = get_option('wc_ast_unclude_tracking_info');	
				$wc_ast_unclude_tracking_info['show_in_customer_note'] = 0;
				update_option('wc_ast_unclude_tracking_info',$wc_ast_unclude_tracking_info);				
				update_option( 'wc_advanced_shipment_tracking', '3.13');		
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.14', '<') ){				
				$this->add_provider_image_in_upload_directory();							
				update_option( 'wc_advanced_shipment_tracking', '3.14');		
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.15', '<') ){				
				global $wpdb;				
				$results = $wpdb->get_row( "SELECT * FROM $this->table LIMIT 1" );				
				if(!isset($results->custom_provider_name)) {					
					$res = $wpdb->query( "ALTER TABLE $this->table ADD custom_provider_name text NULL DEFAULT NULL AFTER provider_name" );
				}		
				update_option( 'wc_advanced_shipment_tracking', '3.15');		
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.16', '<') ){				
				global $wpdb;				
				$results = $wpdb->get_row( "SELECT * FROM $this->table LIMIT 1" );				
				if(!isset($results->api_provider_name)) {					
					$res = $wpdb->query( "ALTER TABLE $this->table ADD api_provider_name text NULL DEFAULT NULL AFTER provider_name" );
				}		
				update_option( 'wc_advanced_shipment_tracking', '3.16');		
			}
			
			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.17', '<') ){
				$data_array = array('processing' => 1,'completed' => 1,'partial-shipped' => 1);				
				update_option( 'wc_ast_show_orders_actions', $data_array );	
				update_option( 'wc_advanced_shipment_tracking', '3.17');					
			}

			if(version_compare(get_option( 'wc_advanced_shipment_tracking' ),'3.18', '<') ){
				update_option( 'display_track_in_my_account', 1);
				update_option( 'open_track_in_new_tab', 1);
				
				$ast = new WC_Advanced_Shipment_Tracking_Actions;	
				$table_content_font_weight = $ast->get_option_value_from_array('tracking_info_settings','table_content_font_weight',$wcast_customizer_settings->defaults['table_content_font_weight']);
				
				$tracking_info_settings = get_option('tracking_info_settings');
				$tracking_info_settings['table_content_font_weight'] = 400;
				
				if($table_content_font_weight < 400){
					update_option('tracking_info_settings',$tracking_info_settings);
				}
				
				update_option( 'wc_advanced_shipment_tracking', '3.18');					
			}	
		}
	}
	
	/**
	 * function for add provider image in uploads directory under wp-content/uploads/ast-shipping-providers
	*/
	public function add_provider_image_in_upload_directory(){		
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';
		
		if(!is_dir($ast_directory)) {
			wp_mkdir_p( $ast_directory );	
		}
				
		$url = 'https://trackship.info/wp-json/WCAST/v1/Provider';		
		$resp = wp_remote_get( $url );							
		if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
			$providers = json_decode($resp['body'],true);
			foreach($providers as $provider){
				$provider_name = $provider['shipping_provider'];
				$img_url = $provider['img_url'];
				$img_slug = sanitize_title($provider_name);
				$img = $ast_directory.'/'.$img_slug.'.png';
				$ch = curl_init(); 
		
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_URL, $img_url); 
				
				$data = curl_exec($ch); 
				curl_close($ch); 							
				file_put_contents($img, $data);
			}
		}	
	}
	
	/**
	 * get providers list from trackship and update providers in database
	*/
	public function update_shipping_providers(){
		global $wpdb;		
		$url = 'https://trackship.info/wp-json/WCAST/v1/Provider';		
		$resp = wp_remote_get( $url );
		
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';
		
		if(!is_dir($ast_directory)) {
			wp_mkdir_p( $ast_directory );	
		}
				
		if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
		
			$providers = json_decode($resp['body'],true);
			
			$providers_name = array();
			
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 1" );			
			foreach ( $default_shippment_providers as $key => $val ){
				$shippment_providers[ $val->provider_name ] = $val;						
			}
	
			foreach ( $providers as $key => $val ){
				$providers_name[ $val['provider_name'] ] = $val;						
			}					
			
			foreach($providers as $provider){
				
				$provider_name = $provider['shipping_provider'];
				$provider_url = $provider['provider_url'];
				$shipping_country = $provider['shipping_country'];
				$ts_slug = $provider['shipping_provider_slug'];
				$trackship_supported = $provider['trackship_supported'];
				
				if(isset($shippment_providers[$provider_name])){				
					$db_provider_url = $shippment_providers[$provider_name]->provider_url;
					$db_shipping_country = $shippment_providers[$provider_name]->shipping_country;
					$db_ts_slug = $shippment_providers[$provider_name]->ts_slug;
					$db_trackship_supported = $shippment_providers[$provider_name]->trackship_supported;
					
					if(($db_provider_url != $provider_url) || ($db_shipping_country != $shipping_country) || ($db_ts_slug != $ts_slug) || ($db_trackship_supported != $trackship_supported)){
						$data_array = array(
							'ts_slug' => $ts_slug,
							'provider_url' => $provider_url,
							'shipping_country' => $shipping_country,
							'trackship_supported' => $trackship_supported,							
						);
						$where_array = array(
							'provider_name' => $provider_name,			
						);					
						$wpdb->update( $this->table, $data_array, $where_array);					
					}
				} else{
					$img_url = $provider['img_url'];
					$img_slug = sanitize_title($provider_name);
					$img = $ast_directory.'/'.$img_slug.'.png';
					
					$ch = curl_init(); 
	
					curl_setopt($ch, CURLOPT_HEADER, 0); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_URL, $img_url); 
				
					$data = curl_exec($ch); 
					curl_close($ch); 
					
					file_put_contents($img, $data); 			
																	
					$data_array = array(
						'shipping_country' => sanitize_text_field($shipping_country),
						'provider_name' => sanitize_text_field($provider_name),
						'ts_slug' => $ts_slug,
						'provider_url' => sanitize_text_field($provider_url),			
						'display_in_order' => 0,
						'shipping_default' => 1,
						'trackship_supported' => $provider['trackship_supported'],
					);
					$result = $wpdb->insert( $this->table, $data_array );				
				}		
			}		
			foreach($default_shippment_providers as $db_provider){
	
				if(!isset($providers_name[$db_provider->provider_name])){				
					$where = array(
						'provider_name' => $db_provider->provider_name,
						'shipping_default' => 1
					);
					$wpdb->delete( $this->table, $where );					
				}
			}
		}	
	}			
}