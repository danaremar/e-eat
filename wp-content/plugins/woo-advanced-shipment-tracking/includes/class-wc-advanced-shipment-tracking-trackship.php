<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Trackship {
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {								
		
		global $wpdb;
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
			
	}
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;		
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return smswoo_license
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init(){	
		
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		
		//load trackship css js 
		add_action( 'admin_enqueue_scripts', array( $this, 'trackship_styles' ), 4);
		
		add_action('admin_menu', array( $this, 'register_woocommerce_trackship_menu' ), 99 );		
		
		//ajax save admin trackship settings
		add_action( 'wp_ajax_wc_ast_trackship_form_update', array( $this, 'wc_ast_trackship_form_update_callback' ) );
		add_action( 'wp_ajax_trackship_tracking_page_form_update', array( $this, 'trackship_tracking_page_form_update_callback' ) );
		add_action( 'wp_ajax_ts_late_shipments_email_form_update', array( $this, 'ts_late_shipments_email_form_update_callback' ) );
		
		$api_enabled = get_option( "wc_ast_api_enabled", 0);
		if( $api_enabled == true ){
			//add Shipment status column after tracking
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'wc_add_order_shipment_status_column_header'), 20 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wc_add_order_shipment_status_column_content') );
			
			//add bulk action - get shipment status
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_get_shipment_status'), 10, 1 );
			
			// Make the action from selected orders to get shipment status
			add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'get_shipment_status_handle_bulk_action_edit_shop_order'), 10, 3 );
			
			// Bulk shipment status sync ajax call from settings
			add_action( 'wp_ajax_bulk_shipment_status_from_settings', array( $this, 'bulk_shipment_status_from_settings_fun' ) );
			
			// Bulk shipment status sync for empty balance ajax call from settings
			add_action( 'wp_ajax_bulk_shipment_status_for_empty_balance_from_settings', array( $this, 'bulk_shipment_status_for_empty_balance_from_settings_fun' ) );
			
			// Bulk shipment status sync for please do connection status ajax call from settings
			add_action( 'wp_ajax_bulk_shipment_status_for_do_connection_from_settings', array( $this, 'bulk_shipment_status_for_do_connection_from_settings_fun' ) );
			
			// The results notice from bulk action on orders
			add_action( 'admin_notices', array( $this, 'shipment_status_bulk_action_admin_notice' ) );
			
			// add 'get_shipment_status' order meta box order action
			add_action( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_get_shipment_status_actions' ) );
			add_action( 'woocommerce_order_action_get_shipment_status_edit_order', array( $this, 'process_order_meta_box_actions_get_shipment_status' ) );
			
			// add bulk order filter for exported / non-exported orders
			$wc_ast_show_shipment_status_filter = get_option( 'wc_ast_show_shipment_status_filter', 0 );
			if( $wc_ast_show_shipment_status_filter == 1 ){
				add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipment_status') , 20 );
				add_filter( 'request', array( $this, 'filter_orders_by_shipment_status_query' ) );
			}
		}
		
		// trigger when order status changed to shipped or completed
		add_action( 'woocommerce_order_status_completed', array( $this, 'trigger_woocommerce_order_status_completed'), 10, 1 );
		
		add_action( 'send_order_to_trackship', array( $this, 'trigger_woocommerce_order_status_completed'), 10, 1 );
		
		add_action( 'woocommerce_order_status_updated-tracking', array( $this, 'trigger_woocommerce_order_status_completed'), 10, 1 );
		
		// filter for shipment status
		add_filter("trackship_status_filter", array($this, "trackship_status_filter_func"), 10 , 1);
		
		// filter for shipment status icon
		add_filter("trackship_status_icon_filter", array($this, "trackship_status_icon_filter_func"), 10 , 2);				
		
		add_action( 'wcast_retry_trackship_apicall', array( $this, 'wcast_retry_trackship_apicall_func' ) );
		
		add_action( 'wp_ajax_update_shipment_status_email_status', array( $this, 'update_shipment_status_email_status_fun') );

		add_action( 'wp_ajax_update_enable_late_shipments_email', array( $this, 'update_enable_late_shipments_email_fun') );
	
		add_action( 'ast_shipment_tracking_end', array( $this, 'display_shipment_tracking_info'), 10, 2 );
		
		add_action( 'delete_tracking_number_from_trackship', array( $this, 'delete_tracking_number_from_trackship'), 10, 3 );
		
		//fix shipment tracking for deleted tracking
		add_action("fix_shipment_tracking_for_deleted_tracking", array( $this, 'func_fix_shipment_tracking_for_deleted_tracking' ), 10, 3 );
		
		$api_enabled = get_option( "wc_ast_api_enabled", 0);
		if( $api_enabled == true ){
			add_action( 'wp_dashboard_setup', array( $this, 'ast_add_dashboard_widgets') );	
		}
		
		//filter in shipped orders
		add_filter( 'is_order_shipped', array( $this, "check_tracking_exist" ),10,2);
		add_filter( 'is_order_shipped', array( $this, "check_order_status" ),5,2);	
	}
	
	/**
	* Load trackship styles.
	*/
	public function trackship_styles($hook) {
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';	
		
		wp_register_style( 'trackship_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/trackship.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );		
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'trackship_script',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/trackship.js', array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking()->version );
		wp_localize_script( 'trackship_script', 'trackship_script', array(
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),				
			),			
		) );
		
		//wp_register_style( 'material-css',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/material.css', array(), wc_advanced_shipment_tracking()->version );		
		//wp_register_script( 'material-js', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/material.min.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
		
		if(!isset($_GET['page'])) {
			return;
		}
		
		if( $_GET['page'] != 'trackship-for-woocommerce' && $_GET['page'] != 'woocommerce-advanced-shipment-tracking' ) {			
			return;
		}		
		
		wp_enqueue_style( 'wp-color-picker' );
		//wp_enqueue_style( 'material-css' );
		wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_style( 'trackship_styles' );		
		wp_enqueue_style( 'woocommerce_admin_styles' );		
		
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );	
		//wp_enqueue_script( 'material-js' );				
		wp_enqueue_script( 'trackship_script' );				
	}

	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_trackship_menu() {
		$wc_ast_api_key = get_option('wc_ast_api_key'); 
		if($wc_ast_api_key){
			add_submenu_page( 'woocommerce', 'TrackShip', 'TrackShip', 'manage_woocommerce', 'trackship-for-woocommerce', array( $this, 'trackship_page_callback' ) ); 
		}
	}
	
	/*
	* callback for Shipment Tracking page
	*/
	public function trackship_page_callback(){ 
		$wc_ast_api_key = get_option('wc_ast_api_key'); ?>
		<div class="zorem-layout">
			<div class="zorem-layout__header">
				<?php if($wc_ast_api_key){ ?>
				<h1 class="zorem-layout__header-breadcrumbs"><span><a href="<?php echo esc_url( admin_url( '/admin.php?page=wc-admin' ) ); ?>"><?php _e('WooCommerce', 'woocommerce'); ?></a></span><span><a href="<?php echo esc_url( admin_url( '/admin.php?page=trackship-for-woocommerce' ) ); ?>">TrackShip</a></span><span class="header-breadcrumbs-last"><?php _e('Settings', 'woocommerce'); ?></span></h1>
				<?php } else{ ?>
				<h1 class="zorem-layout__header-breadcrumbs"><span><a href="<?php echo esc_url( admin_url( '/admin.php?page=wc-admin' ) ); ?>"><?php _e('WooCommerce', 'woocommerce'); ?></a></span><span><a href="<?php echo esc_url( admin_url( '/admin.php?page=trackship-for-woocommerce' ) ); ?>"><?php _e('Shipment Tracking', 'woo-advanced-shipment-tracking'); ?></a></span><span class="header-breadcrumbs-last">TrackShip</span></h1>	
				<?php } ?>
				<div class="zorem-layout__logo-panel">
					<img class="header-plugin-logo" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png">
					<?php if($wc_ast_api_key){ ?>	
					<div class="trackship_menu trackship_dropdown">
						<span class="dashicons dashicons-menu trackship-dropdown-menu"></span>
						<ul class="trackship-dropdown-content">
							<li><a href="javaScript:void(0);" data-label="<?php _e('Settings', 'woocommerce'); ?>" data-tab="trackship" data-section="content_trackship_dashboard"><?php _e('Settings', 'woocommerce'); ?></a></li>
							<li><a href="javaScript:void(0);" data-label="<?php _e('Tracking Page', 'woo-advanced-shipment-tracking'); ?>" data-tab="tracking-page" data-section="content_tracking_page"><?php _e('Tracking Page', 'woo-advanced-shipment-tracking'); ?></a></li>
							<li><a href="javaScript:void(0);" data-label="<?php _e('Notifications', 'woo-advanced-shipment-tracking'); ?>" data-tab="notifications" data-section="content_status_notifications"><?php _e('Notifications', 'woo-advanced-shipment-tracking'); ?></a></li>
							<li><a href="javaScript:void(0);" data-label="<?php _e('Tools', 'woo-advanced-shipment-tracking'); ?>" data-tab="tools" data-section="content_tools"><?php _e('Tools', 'woo-advanced-shipment-tracking'); ?></a></li>
							<li><a target="blank" href="https://trackship.info/documentation/?utm_source=wpadmin&utm_medium=ts_settings&utm_campaign=docs"><?php _e( 'Documentation', 'woo-advanced-shipment-tracking' ); ?></a></li>
							<li><a href="https://trackship.info/my-account/?utm_source=wpadmin&utm_medium=ts_settings&utm_campaign=dashboard" target="blank">TrackShip Account</a></li>
						</ul>	
					</div>	
					<?php } ?>	
				</div>
			</div>		
			<?php require_once( 'views/trackship_settings.php' );?>	
			
			<div id="trackship_settings_snackbar" class="ast_snackbar"><?php _e( 'Data saved successfully.', 'woo-advanced-shipment-tracking' )?></div>	
		</div >
	<?php }	
	
	/*
	* include file on plugin load
	*/
	public function on_plugins_loaded() {					
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-intransit-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-failure-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-outfordelivery-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-delivered-email-customizer.php';require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-returntosender-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-availableforpickup-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-onhold-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-late-shipments-email-customizer.php';
		
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/trackship-email-manager.php';
	}
	
	/*
	* settings form save
	*/
	function wc_ast_trackship_form_update_callback(){
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_trackship_form', 'wc_ast_trackship_form_nonce' ) ) {
			
			$data2 = $this->get_trackship_general_data();
			$data3 = $this->get_trackship_automation_data();			
			
			foreach( $data2 as $key2 => $val2 ){
				update_option( $key2, sanitize_text_field( $_POST[ $key2 ] ) );
			}
			
			foreach( $data3 as $key3 => $val3 ){
				update_option( $key3, sanitize_text_field( $_POST[ $key3 ] ) );
			}				
			
			echo json_encode( array('success' => 'true') );die();
		}
	}
	
	/*
	* tracking page form save
	*/
	public function trackship_tracking_page_form_update_callback(){
		if ( ! empty( $_POST ) && check_admin_referer( 'trackship_tracking_page_form', 'trackship_tracking_page_form_nonce' ) ) {
			
			$data1 = $this->get_trackship_page_data();
			
			foreach( $data1 as $key1 => $val1 ){
				update_option( $key1, sanitize_text_field( $_POST[ $key1 ] ) );
			}
			
			echo json_encode( array('success' => 'true') );die();
		}
	}
	
	/*
	* late shipmenta form save
	*/
	public function ts_late_shipments_email_form_update_callback(){
		if ( ! empty( $_POST ) && check_admin_referer( 'ts_late_shipments_email_form', 'ts_late_shipments_email_form_nonce' ) ) {
			
			$wcast_late_shipments_days = isset( $_POST['wcast_late_shipments_days'] ) ? $_POST['wcast_late_shipments_days'] : '';
			$wcast_late_shipments_email_to = isset( $_POST['wcast_late_shipments_email_to'] ) ? $_POST['wcast_late_shipments_email_to'] : '';			
			$wcast_late_shipments_email_subject = isset( $_POST['wcast_late_shipments_email_subject'] ) ? $_POST['wcast_late_shipments_email_subject'] : '';			
			$wcast_late_shipments_email_content = isset( $_POST['wcast_late_shipments_email_content'] ) ? $_POST['wcast_late_shipments_email_content'] : '';
			$wcast_late_shipments_trigger_alert = isset( $_POST['wcast_late_shipments_trigger_alert'] ) ? $_POST['wcast_late_shipments_trigger_alert'] : '';			
			$wcast_late_shipments_daily_digest_time = isset( $_POST['wcast_late_shipments_daily_digest_time'] ) ? $_POST['wcast_late_shipments_daily_digest_time'] : '';
			$wcast_enable_late_shipments_admin_email = isset( $_POST['wcast_enable_late_shipments_admin_email'] ) ? $_POST['wcast_enable_late_shipments_admin_email'] : '';

			$late_shipments_email_settings = array(
				'wcast_enable_late_shipments_admin_email' => $wcast_enable_late_shipments_admin_email,
				'wcast_late_shipments_days' => $wcast_late_shipments_days,
				'wcast_late_shipments_email_to' => $wcast_late_shipments_email_to,
				'wcast_late_shipments_email_subject' => $wcast_late_shipments_email_subject,
				'wcast_late_shipments_email_content' => $wcast_late_shipments_email_content,
				'wcast_late_shipments_trigger_alert' => $wcast_late_shipments_trigger_alert,
				'wcast_late_shipments_daily_digest_time' => $wcast_late_shipments_daily_digest_time,
			);
			
			update_option( 'late_shipments_email_settings', $late_shipments_email_settings );
			
			$Late_Shipments = new WC_Advanced_Shipment_Tracking_Late_Shipments();
			$Late_Shipments->remove_cron();
			$Late_Shipments->setup_cron();
		}
	}

	/*
	* get settings tab array data
	* return array
	*/
	function get_trackship_page_data(){		
		$wc_ast_api_key = get_option('wc_ast_api_key');
		$trackers_balance = get_option( 'trackers_balance' );
		if($wc_ast_api_key){
			$connected = true;
			$show_trackship_field = true;
			$show_trackship_description = false;
		} else{
			$connected = false;
			$show_trackship_field = false;
			$show_trackship_description = true;
		}
			
		$page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' );
		$wc_ast_trackship_page_id = get_option('wc_ast_trackship_page_id');
		$post = get_post($wc_ast_trackship_page_id); 
		$slug = $post->post_name;
		
		if($slug != 'ts-shipment-tracking'){
			$page_desc = __( 'You must add the shortcode [wcast-track-order] to the selected page in order for the tracking page to work.', 'woo-advanced-shipment-tracking' );
		} else{
			$page_desc = '';
		}		
		
		$form_data = array(			
			'wc_ast_trackship_page_id' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Select Tracking Page', 'woo-advanced-shipment-tracking' ),
				'options'   => $page_list,				
				'show' => $show_trackship_field,
				'desc' => $page_desc,
				'class'     => '',
			),
			'wc_ast_trackship_other_page' => array(
				'type'		=> 'text',
				'title'		=> __( 'Select Tracking Page', 'woo-advanced-shipment-tracking' ),			
				'show' => $show_trackship_field,				
				'class'     => '',
			),			
			'wc_ast_use_tracking_page' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Use the tracking page in the customer email/my account tracking link', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',
			),
			'wc_ast_select_tracking_page_layout' => array(
				'type'		=> 'radio',
				'title'		=> __( 'Tracking Page Layout', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'options'   => array( 
					"" =>__( 'Select', 'woocommerce' ),
					"t_layout_1" =>__( 'Layout 1', '' ),
					"t_layout_2" =>__( 'Layout 2', '' ),
				),	
				'class'     => '',
			),
			'wc_ast_select_border_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Select content border color for tracking page', 'woo-advanced-shipment-tracking' ),				
				'class'		=> 'color_field',
				'show' => $show_trackship_field,				
			),
			'wc_ast_link_to_shipping_provider' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Add a link to the Shipping provider page', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',
			),
			'wc_ast_hide_tracking_provider_image' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Hide Shipping Provider Image', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',
			),			
			'wc_ast_hide_tracking_events' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Hide tracking events details', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',
			),	
			'wc_ast_remove_trackship_branding' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Remove TrackShip branding', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',
			),			
		);
		return $form_data;
	}	
	
	/*
	* get settings tab array data
	* return array
	*/
	function get_trackship_general_data(){		
		$wc_ast_api_key = get_option('wc_ast_api_key');		
		if($wc_ast_api_key){
			$connected = true;
			$show_trackship_field = true;
			$show_trackship_description = false;
		} else{
			$connected = false;
			$show_trackship_field = false;
			$show_trackship_description = true;
		}				
		
		$form_data = array(	
			'wc_ast_api_enabled' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable TrackShip', 'woo-advanced-shipment-tracking' ),
				'show' => $show_trackship_field,
				'class'     => '',
			),									
			'wc_ast_show_shipment_status_filter' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Display Shipment Status Filter on Orders admin', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',				
			),
		);
		return $form_data;
	}
	
	/*
	* get settings tab array data
	* return array
	*/
	function get_trackship_automation_data(){		
		$wc_ast_api_key = get_option('wc_ast_api_key');		
		$wc_ast_status_delivered = get_option( 'wc_ast_status_delivered' );
		if($wc_ast_api_key){
			$connected = true;
			$show_trackship_field = true;
			$show_trackship_description = false;
		} else{
			$connected = false;
			$show_trackship_field = false;
			$show_trackship_description = true;
		}	
		if($wc_ast_status_delivered){
			$disabled_change_to_delivered = false;
		} else{
			$disabled_change_to_delivered = true;
		}		
		
		$form_data = array(	
			'wc_ast_status_change_to_delivered' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Automatically set the Order Status to Delivered when the shipment is delivered ', 'woo-advanced-shipment-tracking' ),				
				'tooltip'		=> __( "To enable this option, the 'Delivered' order status should be enabled in the AST settings.", 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',
				'disabled'  => $disabled_change_to_delivered,
			),			
		);
		return $form_data;
	}
	
	/**
	 * Adds 'shipment_status' column header to 'Orders' page immediately after 'woocommerce-advanced-shipment-tracking' column.
	 *
	 * @param string[] $columns
	 * @return string[] $new_columns
	 */
	function wc_add_order_shipment_status_column_header( $columns ) {
		wp_enqueue_style( 'trackship_styles' );
		$new_columns = array();
	
		foreach ( $columns as $column_name => $column_info ) {
	
			$new_columns[ $column_name ] = $column_info;				
			
			if ( 'woocommerce-advanced-shipment-tracking' === $column_name ) {			
				$new_columns['shipment_status'] = __( 'Shipment status', 'woo-advanced-shipment-tracking' );
			}
		}
		return $new_columns;
	}
	
	/**
	 * Adds 'shipment_status' column content to 'Orders' page.
	 *
	 * @param string[] $column name of column being displayed
	 */
	function wc_add_order_shipment_status_column_content( $column ) {
		global $post;
		if ( 'shipment_status_old' === $column ) {
			
			$shipment_status = get_post_meta( $post->ID, "shipment_status", true);
			
			if( is_array($shipment_status) ){
				foreach( $shipment_status as $data ){
					$status = $data["status"];
					$est_delivery_date = $data["est_delivery_date"];
					echo "<div class='ast-shipment-status shipment-".sanitize_title($status)."' >".apply_filters("trackship_status_filter",$status) . apply_filters( "trackship_status_icon_filter", "", $status )."</div>";
					
					$date = $data["status_date"];
					if( $date ){
						$date = date( "Y-m-d", strtotime($date) );
						echo "<span class=description>on {$date}</span>";
					}
					if( $est_delivery_date ){
						echo "<div>EST Delivery: {$est_delivery_date}</div>";
					}
				}
			}
		}
		
		if ( 'shipment_status' === $column ) {
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $post->ID );
			$shipment_status = get_post_meta( $post->ID, "shipment_status", true);				
			$wp_date_format = get_option( 'date_format' );
			if($wp_date_format == 'd/m/Y'){
				$date_format = 'd/m'; 
			} else{
				$date_format = 'm/d';
			}
			if ( count( $tracking_items ) > 0 ) {
				?>
                	<ul class="wcast-shipment-status-list">
                    	<?php foreach ( $tracking_items as $key => $tracking_item ) { 
								if( !isset($shipment_status[$key]) ){
									echo '<li class="tracking-item-'.$tracking_item['tracking_id'].'"></li>';continue;
								}
								$has_est_delivery = false;
								
								if(isset($shipment_status[$key]['pending_status'])){
									$status = $shipment_status[$key]['pending_status'];
								} else{
									$status = $shipment_status[$key]['status'];	
								}
								
								$status_date = $shipment_status[$key]['status_date'];
								
								if(isset($shipment_status[$key]['est_delivery_date']))$est_delivery_date = $shipment_status[$key]['est_delivery_date'];								
								
								if( $status != 'delivered' && $status != 'return_to_sender' && !empty($est_delivery_date) )$has_est_delivery = true;				
                            ?>
                            <li id="shipment-item-<?php echo $tracking_item['tracking_id'];?>" class="tracking-item-<?php echo $tracking_item['tracking_id'];?> open_tracking_details" data-orderid="<?php echo $post->ID; ?>" data-tracking_id="<?php echo $tracking_item['tracking_id']; ?>">                            	
                                <div class="ast-shipment-status shipment-<?php echo sanitize_title($status)?> has_est_delivery_<?php echo ( $has_est_delivery ? 1 : 0 )?>">
									<?php echo apply_filters( "trackship_status_icon_filter", "", $status );?>
									<span class="ast-shipment-tracking-status"><?php echo apply_filters("trackship_status_filter",$status);?></span>
									<?php if($status_date != ''){ ?>
									<span class="showif_has_est_delivery_0 ft11">on <?php echo date( $date_format, strtotime($status_date))?></span>
									<?php } ?>
                                    <?php if( $has_est_delivery){?>
                                    	<span class="wcast-shipment-est-delivery ft11">Est. Delivery(<?php echo date( $date_format, strtotime($est_delivery_date)); ?>)</span>
									<?php } ?>
                                </div>
                            </li>
						<?php } ?>
                    </ul>
				<?php
			} else {
				echo '–';
			}
		}
	}
	
	/*
	* add bulk action
	* Change order status to delivered
	*/
	function add_bulk_actions_get_shipment_status($bulk_actions){
		$bulk_actions['get_shipment_status'] = 'Get Shipment Status';
		return $bulk_actions;
	}
	
	/*
	* order bulk action for get shipment status
	*/
	function get_shipment_status_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ){
		
		if ( $action !== 'get_shipment_status' )
			return $redirect_to;
	
		$processed_ids = array();
		
		$order_count = count($post_ids);
		
		if($order_count > 100){
			//return $redirect_to;
		}
		
		foreach ( $post_ids as $post_id ) {
						
			wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $post_id ) );			
			$processed_ids[] = $post_id;
			
		}
	
		return $redirect_to = add_query_arg( array(
			'get_shipment_status' => '1',
			'processed_count' => count( $processed_ids ),
			'processed_ids' => implode( ',', $processed_ids ),
		), $redirect_to );
	}
	
	/*
	* bulk shipment status action for completed order with tracking details and without shipment status
	*/
	public static function bulk_shipment_status_from_settings_fun(){
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		$orders = wc_get_orders( $args );		
		foreach($orders as $order){
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			if($tracking_items){
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);				
				foreach ( $tracking_items as $key => $tracking_item ) { 
					if( !isset($shipment_status[$key]) ){						
						wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $order_id ) );					
					}
				}									
			}			
		}
		$url = admin_url('/edit.php?post_type=shop_order');		
		echo $url;die();		
	}
	
	/*
	* bulk shipment status action for "TrackShip balance is 0" status
	*/
	public static function bulk_shipment_status_for_empty_balance_from_settings_fun(){
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		$orders = wc_get_orders( $args );		
		
		foreach($orders as $order){
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			if($tracking_items){				
				
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);		
				
				foreach ( $tracking_items as $key => $tracking_item ) { 					
					if($shipment_status[$key]['pending_status'] == 'TrackShip balance is 0'){						
						wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $order_id ) );		
					}
				}									
			}			
		}
		
		$url = admin_url('/edit.php?post_type=shop_order');		
		echo $url;die();		
	}
	
	/*
	* bulk shipment status action for "TrackShip balance is 0" status
	*/
	public static function bulk_shipment_status_for_do_connection_from_settings_fun(){
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		$orders = wc_get_orders( $args );		
		
		foreach($orders as $order){
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			if($tracking_items){				
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);						
				foreach ( $tracking_items as $key => $tracking_item ) { 					
					if( $shipment_status[$key]['pending_status'] == 'TrackShip connection issue' ){						
						wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $order_id ) );		
					}
				}									
			}			
		}
		
		$url = admin_url('/edit.php?post_type=shop_order');		
		echo $url;die();		
	}

	/*
	* The results notice from bulk action on orders
	*/
	function shipment_status_bulk_action_admin_notice() {
		if ( empty( $_REQUEST['get_shipment_status'] ) ) return; // Exit
	
		$count = intval( $_REQUEST['processed_count'] );
	
		printf( '<div id="message" class="updated fade"><p>' .
			_n( 'The shipment status updates will run in the background, please refresh the page in a few minutes.',
			'The shipment status updates will run in the background, please refresh the page in a few minutes.',
			$count,
			'get_shipment_status'
		) . '</p></div>', $count );
	}

	/**
	 * Add 'get_shipment_status' link to order actions select box on edit order page
	 *
	 * @since 1.0
	 * @param array $actions order actions array to display
	 * @return array
	 */
	public function add_order_meta_box_get_shipment_status_actions( $actions ) {

		// add download to CSV action
		$actions['get_shipment_status_edit_order'] = __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' );
		return $actions;
	}

	/*
	* order details meta box action
	*/
	public function process_order_meta_box_actions_get_shipment_status( $order ){
		$this->trigger_woocommerce_order_status_completed( $order->get_id() );
	}	
	
	/**
	 * Add bulk filter for Shipment status in orders list
	 *
	 * @since 2.4
	 */
	public function filter_orders_by_shipment_status(){
		global $typenow;

		if ( 'shop_order' === $typenow ) {			

			$terms = array(
				'pending_trackship' => (object) array( 'term' => __( 'Pending TrackShip', 'woo-advanced-shipment-tracking' ) ),
				'unknown' => (object) array( 'term' => __( 'Unknown', 'woo-advanced-shipment-tracking' ) ),
				'pre_transit' => (object) array( 'term' => __( 'Pre Transit', 'woo-advanced-shipment-tracking' ) ),
				'in_transit' => (object) array( 'term' => __( 'In Transit', 'woo-advanced-shipment-tracking' ) ),
				'available_for_pickup' => (object) array( 'term' => __( 'Available For Pickup', 'woo-advanced-shipment-tracking' ) ),
				'out_for_delivery' => (object) array( 'term' => __( 'Out For Delivery', 'woo-advanced-shipment-tracking' ) ),
				'delivered' => (object) array( 'term' => __( 'Delivered', 'woo-advanced-shipment-tracking' ) ),
				'failed_attempt' => (object) array( 'term' => __( 'Failed Attempt', 'woo-advanced-shipment-tracking' ) ),
				'cancelled' => (object) array( 'term' => __( 'Cancelled', 'woocommerce' ) ),
				'carrier_unsupported' => (object) array( 'term' => __( 'Carrier Unsupported', 'woo-advanced-shipment-tracking' ) ),
				'return_to_sender' => (object) array( 'term' => __( 'Return To Sender', 'woo-advanced-shipment-tracking' ) ),				
				'INVALID_TRACKING_NUM' => (object) array( 'term' => __( 'Invalid Tracking Number', 'woo-advanced-shipment-tracking' ) ),
			);

			?>
			<select name="_shop_order_shipment_status" id="dropdown_shop_order_shipment_status">
				<option value=""><?php _e( 'Filter by shipment status', 'woo-advanced-shipment-tracking' ); ?></option>
				<?php foreach ( $terms as $value => $term ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_shipment_status'] ) ? selected( $value, $_GET['_shop_order_shipment_status'], false ) : '' ); ?>>
					<?php printf( '%1$s', esc_html( $term->term ) ); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<?php
		}
	}		
	
	/**
	 * Process bulk filter action for shipment status orders
	 *
	 * @since 3.0.0
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_orders_by_shipment_status_query( $vars ){
		global $typenow;		
		if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_shipment_status'] ) && $_GET['_shop_order_shipment_status'] != '') {
			$vars['meta_key']   = 'shipment_status';
			$vars['meta_value'] = $_GET['_shop_order_shipment_status'];
			$vars['meta_compare'] = 'LIKE';						
		}

		return $vars;
	}	
	
	/*
	* trigger when order status changed to shipped or completed or update tracking
	* param $order_id
	*/	
	function trigger_woocommerce_order_status_completed( $order_id ){
		
		//error_log( "Order complete for order $order_id", 0 );
		$order = wc_get_order( $order_id );
		$order_shipped = apply_filters( 'is_order_shipped', true, $order );
		
		//error_log( "Order shipped :  $order_shipped", 0 );
		if( $order_shipped ){
			$api_enabled = get_option( "wc_ast_api_enabled", 0);
			if( $api_enabled ){
				$api = new WC_Advanced_Shipment_Tracking_Api_Call;
				$array = $api->get_trackship_apicall( $order_id );				
			}
		}
	}
	
	/*
	* filter for shipment status
	*/
	function trackship_status_filter_func( $status ){
		switch ($status) {
			case "in_transit":
				$status = __( 'In Transit', 'woo-advanced-shipment-tracking' );
				break;
			case "on_hold":
				$status = __( 'On Hold', 'woo-advanced-shipment-tracking' );
				break;
			case "pre_transit":
				$status = __( 'Pre Transit', 'woo-advanced-shipment-tracking' );
				break;
			case "delivered":
				$status = __( 'Delivered', 'woo-advanced-shipment-tracking' );
				break;
			case "out_for_delivery":
				$status = __( 'Out For Delivery', 'woo-advanced-shipment-tracking' );
				break;
			case "available_for_pickup":
				$status = __( 'Available For Pickup', 'woo-advanced-shipment-tracking' );
				break;
			case "return_to_sender":
				$status = __( 'Return To Sender', 'woo-advanced-shipment-tracking' );
				break;
			case "failure":
				$status = __( 'Failed Attempt', 'woo-advanced-shipment-tracking' );
				break;
			case "exception":
				$status = __( 'Exception', 'woo-advanced-shipment-tracking' );
				break;	
			case "unknown":
				$status = __( 'Unknown', 'woo-advanced-shipment-tracking' );
				break;
			case "pending_trackship":
				$status = __( 'Pending TrackShip', 'woo-advanced-shipment-tracking' );
				break;
			case "INVALID_TRACKING_NUM":
				$status = __( 'Invalid Tracking Number', 'woo-advanced-shipment-tracking' );
				break;
			case "carrier_unsupported":
				$status = __( 'Carrier Unsupported', 'woo-advanced-shipment-tracking' );
				break;
			case "invalid_user_key":
				$status = __( 'Invalid User Key', 'woo-advanced-shipment-tracking' );
				break;
			case "wrong_shipping_provider":
				$status = __( 'Wrong Shipping Provider', 'woo-advanced-shipment-tracking' );
				break;	
			case "deleted":
				$status = __( 'Deleted', 'woocommerce' );
				break;		
				
		}
		return $status;
	}
	
	/*
	* filter for shipment status icon
	*/
	function trackship_status_icon_filter_func( $html, $status ){
		switch ($status) {
			case "in_transit":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "on_hold":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;	
			case "pre_transit":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "delivered":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "out_for_delivery":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "available_for_pickup":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "return_to_sender":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "failure":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "exception":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;	
			case "unknown":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "pending_trackship":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "INVALID_TRACKING_NUM":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "wrong_shipping_provider":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;	
			case "invalid_user_key":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "carrier_unsupported":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;				
			default:
				$html = '<span class="shipment-icon icon-default">';
				break;

		}
		return $html;
	}

	/*
	* retry trackship api call
	*/
	function wcast_retry_trackship_apicall_func( $order_id ){
		$logger = wc_get_logger();
		$context = array( 'source' => 'retry_trackship_apicall' );
		$logger->info( "Retry trackship api call for Order id : ".$order_id, $context );
		$this->trigger_woocommerce_order_status_completed( $order_id );
	}

	/*
	* update all shipment status email status
	*/
	public function update_shipment_status_email_status_fun(){	
		$status_settings = get_option($_POST['settings_data']); 
		$status_settings[$_POST['id']] = wc_clean($_POST['wcast_enable_status_email']);
		update_option( $_POST['settings_data'], $status_settings );		
		exit;
	}
	
	public function update_enable_late_shipments_email_fun(){		
		$status_settings[$_POST['id']] = wc_clean($_POST['wcast_enable_late_shipments_email']);
		update_option( $_POST['settings_data'], $status_settings );			
		exit;
	}

	/*
	* get trackship bulk actions tab array data
	* return array
	*/
	function get_trackship_bulk_actions_data(){			
		
		$completed_order_with_tracking = $this->completed_order_with_tracking();
		$completed_order_with_zero_balance = $this->completed_order_with_zero_balance();							
		$completed_order_with_do_connection = $this->completed_order_with_do_connection();
		
		if($completed_order_with_tracking > 0){
			$disable_bulk_sync = false;
		} else{
			$disable_bulk_sync = true;
		}						
		
		if($completed_order_with_zero_balance > 0){
			$disable_bulk_sync_zero_balance = false;
		} else{
			$disable_bulk_sync_zero_balance = true;
		}																	
				
		if($completed_order_with_do_connection > 0){
			$disable_bulk_sync_do_connection = false;			
		} else{
			$disable_bulk_sync_do_connection = true;			
		}

		$wc_ast_status_shipped = get_option('wc_ast_status_shipped');
		
		if($wc_ast_status_shipped == 1){
			$completed_order_label = '<span class="shipped_label">shipped</span>';			
		} else{
			$completed_order_label = '<span class="shipped_label">completed</span>';			
		}
		
		$form_data = array(						
			'wc_ast_bulk_shipment_status' => array(
				'type'		=> 'button',
				'title'		=> sprintf(__('You got %s %s orders with tracking info that were not sent to track on TrackShip', 'woo-advanced-shipment-tracking'), $completed_order_with_tracking , $completed_order_label),
				'label' => __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ),
				'show' => true,
				'disable' => $disable_bulk_sync,
				'button_class'     => 'bulk_shipment_status_button',
				'class'     => '',
			),
			'wc_ast_bulk_shipment_status_for_zero_tracker_balace' => array(
				'type'		=> 'button',
				'title'		=> sprintf(__('You got %s %s orders with shipment status “TrackShip balance is 0”', 'woo-advanced-shipment-tracking'), $completed_order_with_zero_balance , $completed_order_label),
				'label' => __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ),
				'show' => true,
				'disable' => $disable_bulk_sync_zero_balance,
				'button_class'     => 'bulk_shipment_status_button_for_empty_balance',
				'class'     => '',
			),
			'wc_ast_bulk_shipment_status_for_trackship_connection_issue' => array(
				'type'		=> 'button',
				'title'		=> sprintf(__('You got %s %s orders with shipment status  “TrackShip connection issue”', 'woo-advanced-shipment-tracking'), $completed_order_with_do_connection , $completed_order_label),
				'label' => __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ),
				'show' => true,
				'disable' => $disable_bulk_sync_do_connection,
				'button_class'     => 'bulk_shipment_status_button_for_connection_issue',
				'class'     => '',
			),
		);
		return $form_data;
	}
	
	/*
	* get completed order with tracking that not sent to TrackShip
	* return number
	*/
	function completed_order_with_tracking(){
		// Get orders completed.
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);
		
		$orders = wc_get_orders( $args );
		
		$completed_order_with_tracking = 0;
		
		foreach($orders as $order){
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			if($tracking_items){
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);
				foreach ( $tracking_items as $key => $tracking_item ) { 				
					if( !isset($shipment_status[$key]) ){						
						$completed_order_with_tracking++;		
					}
				}									
			}			
		}
		return $completed_order_with_tracking;
	}
	
	/*
	* get completed order with Trackship Balance 0 status
	* return number
	*/
	function completed_order_with_zero_balance(){
		
		// Get orders completed.
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		
		$orders = wc_get_orders( $args );
		
		$completed_order_with_zero_balance = 0;
		
		foreach($orders as $order){
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			
			if($tracking_items){				
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);		
				foreach ( $tracking_items as $key => $tracking_item ) { 					
					if(isset($shipment_status[$key]['pending_status']) && $shipment_status[$key]['pending_status'] == 'TrackShip balance is 0'){
						$completed_order_with_zero_balance++;		
					}
				}									
			}			
		}				
		return $completed_order_with_zero_balance;
	}
	
	/*
	* get completed order with Trackship connection issue status
	* return number
	*/
	function completed_order_with_do_connection(){
		
		// Get orders completed.
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		
		$orders = wc_get_orders( $args );
		
		$completed_order_with_do_connection = 0;
		
		foreach($orders as $order){
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			if($tracking_items){				
				$shipment_status = get_post_meta( $order_id, "shipment_status", true);				
				foreach ( $tracking_items as $key => $tracking_item ) { 					
					if(isset($shipment_status[$key]['pending_status']) && $shipment_status[$key]['pending_status'] == 'TrackShip connection issue'){
						$completed_order_with_do_connection++;		
					}
				}									
			}			
		}				
		return $completed_order_with_do_connection;
	}
	
	/**
	 * Shipment tracking info html in orders details page
	 */
	public function display_shipment_tracking_info( $order_id, $item ){
		$shipment_status = get_post_meta( $order_id, "shipment_status", true);		
		$tracking_id = $item['tracking_id'];
		
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		$tracking_items = $ast->get_tracking_items( $order_id );
		
		$wp_date_format = get_option( 'date_format' );
		
		if($wp_date_format == 'd/m/Y'){
			$date_format = 'd/m'; 
		} else{
			$date_format = 'm/d';
		}
		
		if ( count( $tracking_items ) > 0 ) {
			foreach ( $tracking_items as $key => $tracking_item ) {
				if( $tracking_id == $tracking_item['tracking_id'] ){
					if( isset( $shipment_status[$key] )){
						$has_est_delivery = false;
						$data = $shipment_status[$key];						
						
						if(isset($data['pending_status'])){
							$status = $data['pending_status'];
						} else{
							$status = $data['status'];	
						}
						
						$status_date = $data['status_date'];
						
						if(!empty($data["est_delivery_date"]))$est_delivery_date = $data["est_delivery_date"];
						
						if( $status != 'delivered' && $status != 'return_to_sender' && !empty($est_delivery_date) )$has_est_delivery = true;
						?>	
						<div class="ast-shipment-status-div">	
                        <span class="open_tracking_details ast-shipment-status shipment-<?php echo sanitize_title($status)?>" data-orderid="<?php echo $order_id; ?>" data-tracking_id="<?php echo $tracking_id; ?>"><?php echo apply_filters( "trackship_status_icon_filter", "", $status )?> <strong><?php echo apply_filters("trackship_status_filter",$status)?></strong></span>
						<?php if($status_date != ''){ ?>
							<span class="">on <?php echo date( $date_format, strtotime($status_date))?></span>
						<?php } ?>	
                        <br>
                        <?php if( $has_est_delivery ){?>
                            <span class="wcast-shipment-est-delivery ft11">Est. Delivery(<?php echo date( $date_format, strtotime($est_delivery_date))?>)</span>
                        <?php } ?>
						</div>	
                        <?php
					}
				}
			}
		}
	}

	/**
	 * Delete tracking information from TrackShip when tracking deleted from AST
	 */
	public function delete_tracking_number_from_trackship( $tracking_items, $tracking_id, $order_id ){
		
		$api_enabled = get_option( "wc_ast_api_enabled", 0);
		
		if( $api_enabled ){			
			foreach($tracking_items as $tracking_item){				
				if($tracking_item['tracking_id'] == $_POST['tracking_id']){					
					$tracking_number = $tracking_item['tracking_number'];
					$tracking_provider = $tracking_item['tracking_provider'];					
					$api = new WC_Advanced_Shipment_Tracking_Api_Call;
					$array = $api->delete_tracking_number_from_trackship( $order_id, $tracking_number, $tracking_provider );
				}				
			}						
		}	
	}
	
	/*
	* fix shipment tracking for deleted tracking
	*/
	public function func_fix_shipment_tracking_for_deleted_tracking( $order_id, $key, $item ){
		$shipment_status = get_post_meta( $order_id, "shipment_status", true);
		if( isset( $shipment_status[$key] ) ){
			unset($shipment_status[$key]);
			update_post_meta( $order_id, "shipment_status", $shipment_status);
		}
	}

	/**
	 * code for check if tracking number in order is delivered or not
	*/
	public function check_tracking_delivered( $order_id ){
		$delivered = true;
		$shipment_status = get_post_meta( $order_id, "shipment_status", true);
		$wc_ast_status_delivered = get_option('wc_ast_status_delivered');						
		
		foreach( (array)$shipment_status as $shipment ){
			$status = $shipment['status'];
			if( $status != 'delivered' ){
				$delivered = false;
			}
		}
		if( count($shipment_status) > 0 && $delivered == true && $wc_ast_status_delivered){
			//trigger order deleivered
			$delivered_enabled = get_option( "wc_ast_status_change_to_delivered", 0);
			if( $delivered_enabled ){
				$order = wc_get_order( $order_id );
				$order_status  = $order->get_status();
				if( $order_status == 'completed' || $order_status == 'updated-tracking' ){
					$order->update_status('delivered');
				}
			}
		}
	}

	/**
	 * code for trigger shipment status email
	*/
	public function trigger_tracking_email( $order_id, $old_status, $new_status, $tracking_item, $shipment_status ){
		$order = wc_get_order( $order_id );		
		require_once( 'email-manager.php' );		
		
		if( $old_status != $new_status){			
			if($new_status == 'delivered'){
				wc_trackship_email_manager()->delivered_shippment_status_email_trigger($order_id, $order, $old_status, $new_status, $tracking_item);
			} elseif($new_status == 'failure' || $new_status == 'in_transit' || $new_status == 'on_hold' || $new_status == 'out_for_delivery' || $new_status == 'available_for_pickup' || $new_status == 'return_to_sender'){
				wc_trackship_email_manager()->shippment_status_email_trigger($order_id, $order, $old_status, $new_status, $tracking_item);
			}	
			do_action( 'ast_trigger_ts_status_change',$order_id, $old_status, $new_status, $tracking_item, $shipment_status );				
		}
	}	
	
	/**
	* Add a new dashboard widget.
	*/
	public function ast_add_dashboard_widgets() {
		//amcharts js	
		wp_enqueue_script( 'amcharts', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/amcharts/amcharts.js' );
		wp_enqueue_script( 'amcharts-light-theme', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/amcharts/light.js' );
		wp_enqueue_script( 'amcharts-serial', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/amcharts/serial.js' );		
		
		wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_add_dashboard_widget( 'trackship_dashboard_widget', 'Tracking Analytics <small>(last 30 days)</small>', array( $this, 'dashboard_widget_function') );
	}
	
	/**
	* Output the contents of the dashboard widget
	*/
	public function dashboard_widget_function( $post, $callback_args ) {				
				
		global $wpdb;		
		$paid_order_statuses =  array('completed','delivered','shipped');		
		$shipment_status_results = $wpdb->get_results( "
			SELECT p.ID, pm.* FROM {$wpdb->prefix}posts AS p
			INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
			WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $paid_order_statuses ) . "' )
			AND p.post_type LIKE 'shop_order'
			AND pm.meta_key = 'shipment_status'
			AND post_date > '" . date('Y-m-d', strtotime('-30 days')) . "'
		" );

		$tracking_items_results = $wpdb->get_results( "
			SELECT p.ID, pm.* FROM {$wpdb->prefix}posts AS p
			INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
			WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $paid_order_statuses ) . "' )
			AND p.post_type LIKE 'shop_order'
			AND pm.meta_key = '_wc_shipment_tracking_items'
			AND post_date > '" . date('Y-m-d', strtotime('-30 days')) . "'
		" );		
					
		$shipment_status = array();
		$shipment_status_merge = array();
		$tracking_item_merge = array();
		
		foreach($shipment_status_results as $order){
			$order_id = $order->ID;														
			$shipment_status = unserialize($order->meta_value);			
						
			if(is_array($shipment_status)){
				$shipment_status_merge = array_merge($shipment_status_merge, $shipment_status);				
			}					
		}
				
		foreach($tracking_items_results as $order){
			$order_id = $order->ID;						
			$tracking_items = unserialize($order->meta_value);
			
			if($tracking_items){								
				foreach ( $tracking_items as $key => $tracking_item ) { 				
					if( isset($shipment_status[$key]) ){							
						$tracking_item_merge[] = $tracking_item;							
					}
				}								
			}			
		}
		
		$shipment_status_arr = array();
		
		foreach ((array)$shipment_status_merge as $key => $item) {
			if(isset($item['status'])){
				$shipment_status_arr[$item['status']][$key] = $item;
			}
		}
		
		$tracking_provider_arr = array();

		foreach ($tracking_item_merge as $key => $item) {			
			$tracking_provider = $wpdb->get_var( "SELECT provider_name FROM {$this->table} WHERE ts_slug = '".$item['tracking_provider']."'" );
			$tracking_provider_arr[$tracking_provider][$key] = $item;
		}		
		
		$tracking_issue_array = array();
		foreach($shipment_status_arr as $status => $val){
			if($status == 'carrier_unsupported' || $status == 'INVALID_TRACKING_NUM' || $status == 'unknown' || $status == 'wrong_shipping_provider'){
				$tracking_issue_array[$status] = $val; 
			}
		}
		
		ksort($shipment_status_arr, SORT_NUMERIC);
		ksort($tracking_provider_arr, SORT_NUMERIC);							
		?>
		<script type="text/javascript">
			 AmCharts.makeChart("ast_dashboard_status_chart",
				{
					"type": "serial",
					"categoryField": "shipment_status",
					"startDuration": 1,
					"handDrawScatter": 4,
					"theme": "light",
					"categoryAxis": {
						"autoRotateAngle": 0,
						"autoRotateCount": 0,
						"autoWrap": true,
						"gridPosition": "start",
						"minHorizontalGap": 10,
						"offset": 1
					},
					"trendLines": [],
					"graphs": [
						{
							"balloonText": " [[shipment_status]] : [[value]]",
							"bulletBorderThickness": 7,
							"colorField": "color",
							"fillAlphas": 1,
							"id": "AmGraph-1",
							"lineColorField": "color",
							"title": "graph 1",
							"type": "column",
							"valueField": "count"
						}
					],
					"guides": [],
					"valueAxes": [
						{
							"id": "ValueAxis-1",
							"title": ""
						}
					],
					"allLabels": [],
					"balloon": {},
					"titles": [
						{
							"id": "Title-1",
							"size": 15,
							"text": ""
						}
					],
					"dataProvider": [
						<?php								
						foreach($shipment_status_arr as $status => $array){ ?>
							{
								"shipment_status": "<?php echo apply_filters("trackship_status_filter",$status); ?>",
								"count": <?php echo count($array); ?>,
								"color": "#BBE285",								
							},
						<?php
						} ?>
					]					
				}
			);
		</script>
		<script type="text/javascript">
			 AmCharts.makeChart("ast_dashboard_providers_chart",
				{
					"type": "serial",
					"categoryField": "shipment_provider",
					"startDuration": 1,
					"handDrawScatter": 4,
					"theme": "light",
					"categoryAxis": {
						"autoRotateAngle": 0,
						"autoRotateCount": 0,
						"autoWrap": true,
						"gridPosition": "start",
						"minHorizontalGap": 10,
						"offset": 1
					},
					"trendLines": [],
					"graphs": [
						{
							"balloonText": " [[shipment_provider]] : [[value]]",
							"bulletBorderThickness": 7,
							"colorField": "color",
							"fillAlphas": 1,
							"id": "AmGraph-1",
							"lineColorField": "color",
							"title": "graph 1",
							"type": "column",
							"valueField": "count"
						}
					],
					"guides": [],
					"valueAxes": [
						{
							"id": "ValueAxis-1",
							"title": ""
						}
					],
					"allLabels": [],
					"balloon": {},
					"titles": [
						{
							"id": "Title-1",
							"size": 15,
							"text": ""
						}
					],
					"dataProvider": [
						<?php								
						foreach($tracking_provider_arr as $provider => $array){ ?>
							{
								"shipment_provider": "<?php echo $provider; ?>",
								"count": <?php echo count($array); ?>,
								"color": "#BBE285",	
							},
						<?php
						} ?>
					]					
				}
			);
		</script>	
		<style>
		a[href="http://www.amcharts.com"] {
			display: none !important;
		}
		</style>	
		<div class="ast-dashborad-widget">			
			
			<input id="tab_s_providers" type="radio" name="tabs" class="widget_tab_input" checked>
			<label for="tab_s_providers" class="widget_tab_label first_label"><?php _e('Shipment Providers', 'woo-advanced-shipment-tracking'); ?></label>
			
			<input id="tab_s_status" type="radio" name="tabs" class="widget_tab_input">
			<label for="tab_s_status" class="widget_tab_label"><?php _e('Shipment Status', 'woo-advanced-shipment-tracking'); ?></label>
			
			<input id="tab_t_issues" type="radio" name="tabs" class="widget_tab_input">
			<label for="tab_t_issues" class="widget_tab_label"><?php _e('Tracking issues', 'woo-advanced-shipment-tracking'); ?></label>
			
			<section id="content_s_providers" class="widget_tab_section">
				<?php if($tracking_provider_arr){ ?>
					<div id="ast_dashboard_providers_chart" class="" style="width: 100%;height: 300px;"></div>
				<?php } else{ ?>
					<p style="padding: 8px 12px;"><?php _e('data not available.', 'woo-advanced-shipment-tracking'); ?></p>
				<?php } ?>
			</section>	
			
			<section id="content_s_status" class="widget_tab_section">	
				<?php if($shipment_status_arr){ ?>
					<div id="ast_dashboard_status_chart" class="" style="width: 100%;height: 300px;"></div>				
				<?php } else{ ?>
					<p style="padding: 8px 12px;"><?php _e('data not available.', 'woo-advanced-shipment-tracking'); ?></p>
				<?php } ?>
			</section>

			<section id="content_t_issues" class="widget_tab_section">	
				<?php if($tracking_issue_array){ ?>					
					<table class="table widefat fixed striped" style="border: 0;border-bottom: 1px solid #e5e5e5;">
						<tbody>
							<?php foreach($tracking_issue_array as $status => $array){ ?>
								<tr>
									<td><a href="<?php echo get_site_url(); ?>/wp-admin/edit.php?s&post_status=all&post_type=shop_order&_shop_order_shipment_status=<?php echo $status; ?>"><?php echo apply_filters("trackship_status_filter",$status); ?></a></td>
									<td><?php echo count($array); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } else{ ?>
					<p style="padding: 8px 12px;"><?php _e('data not available.', 'woo-advanced-shipment-tracking'); ?></p>
				<?php } ?>
			</section>			
			
		</div>
		<div class="widget_footer">	
			<a class="" href="https://trackship.info/my-account/analytics/" target="blank"><?php _e( 'View more on TrackShip','woo-advanced-shipment-tracking' ); ?></a>
		</div>
	<?php }
	
	/**
	* Create tracking page after store is connected
	*/
	public function create_tracking_page(){
		if(version_compare(get_option( 'wc_advanced_shipment_tracking_ts_page' ),'1.0', '<') ){
			$new_page_title = 'Shipment Tracking';
			$new_page_slug = 'ts-shipment-tracking';		
			$new_page_content = '[wcast-track-order]';       
			//don't change the code below, unless you know what you're doing
			$page_check = get_page_by_title($new_page_title);		
	
			if(!isset($page_check->ID)){
				$new_page = array(
					'post_type' => 'page',
					'post_title' => $new_page_title,
					'post_name' => $new_page_slug,
					'post_content' => $new_page_content,
					'post_status' => 'publish',
					'post_author' => 1,
				);
				$new_page_id = wp_insert_post($new_page);	
				update_option( 'wc_ast_trackship_page_id', $new_page_id );	
			}
			update_option( 'wc_advanced_shipment_tracking_ts_page', '1.0');					
		}	
	}
	
	/*
	* tracking number filter
	* if number not found. return false
	* if number found. return true
	*/
	function check_tracking_exist( $value, $order ){
		
		if($value == true){
				
			$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );
			if( $tracking_items ){
				return true;
			} else {
				return false;
			}
		}
		return $value;
	}		
	
	/*
	* If order status is "Updated Tracking" or "Completed" than retrn true else return false
	*/
	function check_order_status($value, $order){
		$order_status  = $order->get_status(); 
		
		$all_order_status = wc_get_order_statuses();
		
		$default_order_status = array(
			'wc-pending' => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold' => 'On hold',
			'wc-completed' => 'Completed',
			'wc-delivered' => 'Delivered',
			'wc-cancelled' => 'Cancelled',
			'wc-refunded' => 'Refunded',
			'wc-failed' => 'Failed'			
		);
		
		foreach($default_order_status as $key=>$value){
			unset($all_order_status[$key]);
		}
		
		$custom_order_status = $all_order_status;
		
		foreach($custom_order_status as $key=>$value){
			unset($custom_order_status[$key]);			
			$key = str_replace("wc-", "", $key);		
			$custom_order_status[] = $key;
		}				
		
		if($order_status == 'updated-tracking' || $order_status == 'completed' || in_array( $order_status, $custom_order_status )){
			return true;			
		} else {
			return false;
		}
		return $value;				
	}
}	