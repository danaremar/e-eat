<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Local_Pickup_admin {

	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return WC_Local_pickup_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0
	*/
	public function __construct() {
		$this->init();		
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init(){
		
		//adding hooks
		
		add_action('admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );
		
		//ajax save admin api settings
		add_action( 'wp_ajax_wclp_setting_form_update', array( $this, 'wclp_setting_form_update_callback') );
		add_action( 'wp_ajax_wclp_osm_form_update', array( $this, 'wclp_osm_form_update_callback') );			
		add_action( 'wp_ajax_wclp_location_edit_form_update', array( $this, 'wclp_location_edit_form_update_callback') );
		
		// Register new status
		add_action( 'init', array( $this, 'register_pickup_order_status') );
		
		// Add to list of WC Order statuses
		add_filter( 'wc_order_statuses', array( $this, 'add_pickup_to_order_statuses') );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_change_order_status'), 50, 1 );				
		
		// Add to custom email for WC Order statuses
		add_filter( 'woocommerce_email_before_order_table', array( $this, 'add_location_address_detail_emails' ), 2, 4 );
		
		// Add Addition content for processing email
		add_filter( 'woocommerce_email_before_order_table', array( $this, 'add_addional_content_on_processing_email' ), 1, 4 );
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_local_pickup_order_status_actions_button'), 100, 2 );
		
		add_action( 'woocommerce_view_order', array( $this, 'add_location_address_detail_order' ), 10, 2 );
		
		add_action( 'woocommerce_order_details_before_order_table', array( $this, 'add_location_address_detail_order_received' ), 10, 2 );
		
		add_action( 'admin_footer', array( $this, 'footer_function'),1 );
		
		add_action( 'wp_ajax_wclp_update_state_dropdown', array( $this, 'wclp_update_state_dropdown_fun') );
		add_action( 'wp_ajax_wclp_update_work_hours_list', array( $this, 'wclp_update_work_hours_list_fun') );
		add_action( 'wp_ajax_wclp_update_edit_location_form', array( $this, 'wclp_update_edit_location_form_fun') );
		add_action( 'wp_ajax_wclp_apply_work_hours', array( $this, 'wclp_apply_work_hours_fun') );
		
		add_filter( 'woocommerce_valid_order_statuses_for_order_again', array( $this, 'add_reorder_button_pickup'), 50, 1 );
		add_filter( 'admin_body_class', array( $this, 'wp_body_classes') );
		
	}
	
	function wp_body_classes( $classes ) {
		if( isset($_GET['page']) && $_GET['page'] == 'local_pickup') {
			$classes .= ' woocommerce_page_local_pickup';
		}
		return $classes;
	}

	
	/*
	* Admin Menu add function
	* WC sub menu 
	*/
	public function register_woocommerce_menu() {
		
		if( class_exists( 'Advanced_local_pickup_PRO' ) ) {
			$menu_label = 'Local Pickup <strong style="color:#009933;">Pro</strong>';	
		} else {
			$menu_label = 'Local Pickup';	
		}
		
		add_submenu_page( 'woocommerce', 'Local Pickup', $menu_label, 'manage_options', 'local_pickup', array( $this, 'woocommerce_local_pickup_page_callback' ) ); //woocommerce_local_pickup_page_callback
	}
	
	/*
	* callback for Advanced Local Pickup page
	*/
	public function woocommerce_local_pickup_page_callback(){		
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		$section = isset( $_GET['section'] ) ? $_GET['section'] : '';
		?>
		<div class="zorem-layout__header">
			<h1 class="zorem-layout__header-breadcrumbs">
				<span><a href="<?php echo esc_url( admin_url( '/admin.php?page=wc-admin' ) ); ?>"><?php _e('WooCommerce', 'woocommerce'); ?></a></span>
				<span><a href="<?php echo esc_url( admin_url( '/admin.php?page=local_pickup' ) ); ?>"><?php _e('Advanced Local Pickup', 'advanced-local-pickup-for-woocommerce'); ?></a></span>
				<span class="header-breadcrumbs-last">
					<?php if((isset($_GET['tab']) && $_GET['tab']=='settings') || !isset($_GET['tab'])){ _e('Settings', 'woocommerce'); }?>
					<?php if(isset($_GET['tab']) && $_GET['tab']=='locations'){ _e('Pickup Locations', 'advanced-local-pickup-for-woocommerce'); }?>
					<?php if(isset($_GET['tab']) && $_GET['tab']=='addon'){ _e('Add-ons', 'advanced-local-pickup-for-woocommerce'); }?>
				</span>
			</h1>
			<div class="zorem-layout__logo-panel">
				<img class="header-plugin-logo" src="<?php echo wc_local_pickup()->plugin_dir_url()?>assets/images/alp-logo.png">			
			</div>
		</div>
		<div class="woocommerce wclp_admin_layout">
            <div class="wclp_admin_content">
                <input id="tab1" type="radio" name="tabs" class="wclp_tab_input" data-label="<?php _e('Settings', 'woocommerce'); ?>" data-tab="settings" checked>
				<a for="tab1" href="admin.php?page=local_pickup&tab=settings" class="wclp_tab_label first_label <?php echo ( 'settings' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'woocommerce'); ?></a>
				<input id="tab3" type="radio" name="tabs" class="wclp_tab_input" data-label="<?php _e('Pickup Locations', 'advanced-local-pickup-for-woocommerce'); ?>" data-tab="locations" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'locations'){ echo 'checked'; } ?>>
				<a for="tab3" href="admin.php?page=local_pickup&tab=locations" class="wclp_tab_label <?php echo ( 'locations' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php _e('Pickup Locations', 'advanced-local-pickup-for-woocommerce'); ?></a>
				<input id="tab4" type="radio" name="tabs" class="wclp_tab_input" data-label="<?php _e('Add-ons', 'advanced-local-pickup-for-woocommerce'); ?>" data-tab="addon" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'addon'){ echo 'checked'; } ?>>
				<a for="tab4" href="admin.php?page=local_pickup&tab=addon" class="wclp_tab_label <?php echo ( 'addon' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php _e('Add-ons', 'advanced-local-pickup-for-woocommerce'); ?></a>
				
				<div class="wclp_nav_doc_section">					
						<a target="blank" href="https://www.zorem.com/docs/advanced-local-pickup-for-woocommerce/"><?php _e('Documentation', 'advanced-local-pickup-for-woocommerce'); ?></a>
                </div>
                <?php require_once( 'views/wclp_setting_tab.php' ); ?>
				<?php require_once( 'views/wclp_locations_tab.php' ); ?>
				<?php require_once( 'views/wclp_addon_tab.php' ); ?>
            </div>
        </div>
        <div id="wclp-toast-example" aria-live="assertive" aria-atomic="true" aria-relevant="text" class="mdl-snackbar mdl-js-snackbar">
            <div class="mdl-snackbar__text"></div>
            <button type="button" class="mdl-snackbar__action"></button>
        </div>
        <?php
	}
	
	
	/*
	* settings form save for Setting tab
	*/
	function wclp_setting_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wclp_setting_form_action', 'wclp_setting_form_nonce_field' ) ) {
				
			if(isset($_POST['wclp_processing_additional_content'])){
				update_option( 'wclp_processing_additional_content',  $_POST['wclp_processing_additional_content'] );
			}
						
			$wclp_show_pickup_instruction_opt = array(
				'display_in_processing_email' => sanitize_text_field($_POST['wclp_show_pickup_instruction']['display_in_processing_email']),
				'display_in_order_received_page' => sanitize_text_field($_POST['wclp_show_pickup_instruction']['display_in_order_received_page']),
				'display_in_order_details_page' => sanitize_text_field($_POST['wclp_show_pickup_instruction']['display_in_order_details_page']),
			);			
			update_option( 'wclp_show_pickup_instruction', $wclp_show_pickup_instruction_opt);
			
	
			// local pickup setting html hook
			do_action('wclp_general_setting_save_hook');
						
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* settings form save for Setting tab
	*/
	function wclp_osm_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wclp_osm_form_action', 'wclp_osm_form_nonce_field' ) ) {
			
			update_option( 'wclp_status_ready_pickup', sanitize_text_field( $_POST[ 'wclp_status_ready_pickup' ] ));
			update_option( 'wclp_ready_pickup_status_label_color', sanitize_text_field( $_POST[ 'wclp_ready_pickup_status_label_color' ] ));
			update_option( 'wclp_ready_pickup_status_label_font_color', sanitize_text_field( $_POST[ 'wclp_ready_pickup_status_label_font_color' ] ));
			update_option( 'wclp_status_picked_up', sanitize_text_field( $_POST[ 'wclp_status_picked_up' ] ));
			update_option( 'wclp_pickup_status_label_color', sanitize_text_field( $_POST[ 'wclp_pickup_status_label_color' ] ));
			update_option( 'wclp_pickup_status_label_font_color', sanitize_text_field( $_POST['wclp_pickup_status_label_font_color'] ));			
			
			$wclp_enable_pickup_email = get_option('woocommerce_customer_pickup_order_settings');									
			
			if($_POST['wclp_enable_pickup_email'] == 1){
				update_option( 'customizer_pickup_order_settings_enabled', sanitize_text_field( $_POST['wclp_enable_pickup_email'] ));
				$enabled = 'yes';
			} else{
				update_option( 'customizer_pickup_order_settings_enabled', sanitize_text_field( '' ));	
				$enabled = 'no';
			}
			
			$opt = array(
				'enabled' => $enabled,
				'subject' => $wclp_enable_pickup_email['subject'],
				'heading' => $wclp_enable_pickup_email['heading'],
				'additional_content' => $wclp_enable_pickup_email['additional_content'],
				'recipient' => $wclp_enable_pickup_email['recipient'],
				'email_type' => $wclp_enable_pickup_email['email_type'],
			);
			update_option( 'woocommerce_customer_pickup_order_settings', wc_clean( $opt ) );
			
			$wclp_enable_ready_pickup_email = get_option('woocommerce_customer_ready_pickup_order_settings');									
			if($_POST['wclp_enable_ready_pickup_email'] == 1){
				update_option( 'customizer_ready_pickup_order_settings_enabled', sanitize_text_field( $_POST['wclp_enable_ready_pickup_email'] ));
				$enabled = 'yes';
			} else{
				update_option( 'customizer_ready_pickup_order_settings_enabled', sanitize_text_field( '' ));	
				$enabled = 'no';
			}
			
			$opt = array(
				'enabled' => $enabled,
				'subject' => $wclp_enable_ready_pickup_email['subject'],
				'heading' => $wclp_enable_ready_pickup_email['heading'],
				'additional_content' => $wclp_enable_ready_pickup_email['additional_content'],
				'recipient' => $wclp_enable_ready_pickup_email['recipient'],
				'email_type' => $wclp_enable_ready_pickup_email['email_type'],
			);
			update_option( 'woocommerce_customer_ready_pickup_order_settings', wc_clean( $opt ) );						
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* get all data 
	*/
	public function get_data() {
		global $wpdb;
		$this->table = $wpdb->prefix."alp_pickup_location";
		// Avoid database table not found errors when plugin is first installed
		// by checking if the plugin option exists
		if ( empty( $this->data ) ) {
			$this->data = array();

			$wpdb->hide_errors();
			
			$results = $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY position" );//ORDER BY name ASC			
						
			$this->data = $results;
		}
		return $this->data;
	}
	
	/*
	* settings form save for Setting tab
	*/
	function wclp_location_edit_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wclp_location_edit_form_action', 'wclp_location_edit_form_nonce_field' ) ) {
						
			global $wpdb;

			$id = $_POST['id'];
			$data = $this->get_data();
			
			if($id == '0') {
				if( !class_exists( 'Advanced_local_pickup_PRO' ) && count($data) > 1 ){
					$array = array(
						'success' => 'fail',
						'msg' => 'you have not pro plguin',
					);
					echo json_encode($array);die();
				}
				
				$data = array(
					'store_name' => sanitize_text_field($_POST['wclp_store_name']),
				);
				$wpdb->insert( $this->table, $data );
				$id = $wpdb->insert_id;
			}
			
			
			//get form field
			$data = array(
				'store_name' => sanitize_text_field( $_POST['wclp_store_name'] ),
				'store_address' => sanitize_text_field( $_POST['wclp_store_address'] ),
				'store_address_2' => sanitize_text_field( $_POST['wclp_store_address_2'] ),
				'store_city' => sanitize_text_field( $_POST['wclp_store_city'] ),
				'store_country' => sanitize_text_field( $_POST['wclp_default_country'] ),
				'store_postcode' => sanitize_text_field( $_POST['wclp_store_postcode'] ),
				'store_phone' => sanitize_text_field( $_POST['wclp_store_phone'] ),
				'store_time_format' => sanitize_text_field( $_POST['wclp_default_time_format'] ),
				'store_days' => !empty($_POST['wclp_store_days']) ? serialize(wc_clean($_POST['wclp_store_days'])) : '',
				'store_instruction' => sanitize_text_field( $_POST['wclp_store_instruction'] ),
			);
			
			// local pickup location edit form save hook
			$data = apply_filters('wclp_location_edit_form_save_hook', $data);

			//check column exist
			$tabledata = $wpdb->get_row( sprintf("SELECT * FROM %s LIMIT 1", $this->table) );
			//print_r($tabledata );
			foreach( (array)$data as $key1 => $val1  ){
				if( $key1 == 'store_name' )continue;
				if(!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( "ALTER TABLE %s ADD $key1 text NOT NULL", $this->table) );
				}
			}
			
			
			$array = array('success' => 'true', 'id' => $id) ;
		
			$where = array(
				'id' => $id,
			);
			
			$result = $wpdb->update( $this->table, $data, $where );				
			
			echo json_encode( $array );die();
		} 
	} 
	
	/*
	* get data by id
	*/
	public function get_data_byid($id){
		global $wpdb;

		$this->table = $wpdb->prefix."alp_pickup_location";
		
		$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );
		
		$results = $this->get_slaches_data_byid($results);
		
		return $results;
	}
	
	/**
	 * Remove slashes from strings, arrays and objects
	 * 
	 * @param    mixed   input data
	 * @return   mixed   cleaned input data
	 */
	public function get_slaches_data_byid($results)
	{
		if (is_array($results)) {
			$results = array_map('get_slaches_data_byid', $results);
		} elseif (is_object($results)) {
			$vars = get_object_vars($results);
			foreach ($vars as $k=>$v) {
				$results->$k = stripslashes($v);
			}
		} else {
			$results = stripslashes($results);
		}
		return $results;
	}
	
	// Register new status
	function register_pickup_order_status() {
		
		$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
		if($ready_for_pickup == true){
			register_post_status( 'wc-ready-pickup', array(
				'label'                     => __( 'Ready for Pickup', 'advanced-local-pickup-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Ready for Pickup (%s)', 'Ready for Pickup (%s)' )
			) );
		}
		
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){
			register_post_status( 'wc-pickup', array(
				'label'                     => __( 'Picked up', 'advanced-local-pickup-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Picked up (%s)', 'Picked up (%s)' )
			) );
		}
		
	}
	
	// Add to list of WC Order statuses
	function add_pickup_to_order_statuses( $order_statuses ) {
	 
		$new_order_statuses = array();
	 
		// add new order status after processing
		foreach ( $order_statuses as $key => $status ) {
	 
			$new_order_statuses[ $key ] = $status;
			
			$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
			if($ready_for_pickup == true){
				if ( 'wc-processing' === $key ) {
					$new_order_statuses['wc-ready-pickup'] = __( 'Ready for Pickup', 'advanced-local-pickup-for-woocommerce' );
				}
			}
			
			$picked = get_option( "wclp_status_picked_up", 0);
			if($picked == true){
				if ( 'wc-processing' === $key ) {
					$new_order_statuses['wc-pickup'] = __( 'Picked up', 'advanced-local-pickup-for-woocommerce' );
				}
			}
		}
	 
		return $new_order_statuses;
	}
	
	// Add bulk action change status to custom order status
	function add_bulk_actions_change_order_status($bulk_actions){
		$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
		if($ready_for_pickup == true){
			$bulk_actions['mark_ready-pickup'] = __( 'Change status to Ready for pickup', 'advanced-local-pickup-for-woocommerce' );
		}
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){
			$bulk_actions['mark_pickup'] = __( 'Change status to Picked up', 'advanced-local-pickup-for-woocommerce' );
		}
		return $bulk_actions;		
	}
	
	function add_location_address_detail_order_received($order_id){		
		
		$wclp_show_pickup_instruction = get_option('wclp_show_pickup_instruction');
		
		////IF display location details not enabel then @return;
		if(!is_order_received_page()) return;

		if(!isset($wclp_show_pickup_instruction['display_in_order_received_page'])) return;
		if( $wclp_show_pickup_instruction['display_in_order_received_page'] != '1' ) return;		
		
		$order = wc_get_order($order_id);
		
		// Iterating through order shipping items
		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){			
			$shipping_method = $shipping_item_obj->get_method_id();						
		}
		
		//IF  dshipping method is not local pickup then @return;
		if( !isset($shipping_method ) ) return;
		if( isset($shipping_method ) && $shipping_method != 'local_pickup' ) return;		
		
		$local_template	= get_stylesheet_directory().'/woocommerce/emails/pickup-instruction.php';
		
		$data = $this->get_data();
		$location_id = get_option('location_defualt', min($data)->id);
		
		$location = $this->get_data_byid($location_id);
		
		$country_code = isset($location) ? $location->store_country : get_option('woocommerce_default_country');
		
		$split_country = explode( ":", $country_code );
		$store_country = isset($split_country[0]) ? $split_country[0] : '';
		$store_state   = isset($split_country[1]) ? $split_country[1] : '';
				
		$store_days = isset($location) ? unserialize($location->store_days) : array();
		$all_days = array(
			'sunday' => __( 'Sunday', 'default' ),
			'monday' => __( 'Monday', 'default'),
			'tuesday' => __( 'Tuesday', 'default' ),
			'wednesday' => __( 'Wednesday', 'default' ),
			'thursday' => __( 'Thursday', 'default' ),
			'friday' => __( 'Friday', 'default' ),
			'saturday' => __( 'Saturday', 'default' ),
		);
		$w_day = array_slice($all_days ,get_option('start_of_week'));
		foreach($all_days as $key=>$val){
			$w_day[$key] = $val;
		}
		foreach($store_days as $key => $val) {
			if($w_day[$key]) {
				$w_day[$key] = $val;
			}
		}
				
		$wclp_default_time_format = isset($location) ? $location->store_time_format : '24';
		if($wclp_default_time_format == '12'){
			foreach($w_day as $key=>$val){	
				if(isset($val['wclp_store_hour'])){
					$last_digit = explode(':', $val['wclp_store_hour']);
					if(end($last_digit) == '00') {
						$val['wclp_store_hour'] = date('g:ia', strtotime($val['wclp_store_hour']));
					} else {
						$val['wclp_store_hour'] = date('g:ia', strtotime($val['wclp_store_hour']));
					}
				}
				if(isset($val['wclp_store_hour_end'])){
					$last_digit = explode(':', $val['wclp_store_hour_end']);
					if(end($last_digit) == '00') {
						$val['wclp_store_hour_end'] = date('g:ia', strtotime($val['wclp_store_hour_end']));
					} else {
						$val['wclp_store_hour_end'] = date('g:ia', strtotime($val['wclp_store_hour_end']));
					}
				}
				$w_day[$key] = $val;				
			}	
		}
		
		if ( file_exists( $local_template ) && is_writable( $local_template )){	
			wc_get_template( 'myaccount/pickup-instruction.php', array( 'w_day' => $w_day, 'location' => $location, 'store_country' => $store_country, 'store_state' => $store_state ), 'advanced-local-pickup-for-woocommerce/', get_stylesheet_directory() . '/woocommerce/' );
		} else{
			wc_get_template( 'myaccount/pickup-instruction.php', array( 'w_day' => $w_day, 'location' => $location, 'store_country' => $store_country, 'store_state' => $store_state ), 'advanced-local-pickup-for-woocommerce/', wc_local_pickup()->get_plugin_path() . '/templates/' );	
		}			
		
	}					
	
	function add_location_address_detail_order($order_id){		
		
		$wclp_show_pickup_instruction = get_option('wclp_show_pickup_instruction');
		////IF display location details not enabel then @return;
		
		if(!is_view_order_page()) return;

		if(!isset($wclp_show_pickup_instruction['display_in_order_details_page'])) return;
		if( $wclp_show_pickup_instruction['display_in_order_details_page'] != '1' ) return; 
		
		$order = wc_get_order($order_id);
		
		// Iterating through order shipping items
		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){			
			$shipping_method = $shipping_item_obj->get_method_id();						
		}
		
		//IF  dshipping method is not local pickup then @return;
		if( !isset($shipping_method ) ) return;
		if( isset($shipping_method ) && $shipping_method != 'local_pickup' ) return;		
		
		$local_template	= get_stylesheet_directory().'/woocommerce/emails/pickup-instruction.php';
		
		$data = $this->get_data();
		$location_id = get_option('location_defualt', min($data)->id);
		
		$location = $this->get_data_byid($location_id);
		
		$country_code = isset($location) ? $location->store_country : get_option('woocommerce_default_country');
		
		$split_country = explode( ":", $country_code );
		$store_country = isset($split_country[0]) ? $split_country[0] : '';
		$store_state   = isset($split_country[1]) ? $split_country[1] : '';
				
		$store_days = isset($location) ? unserialize($location->store_days) : array();
		$all_days = array(
			'sunday' => __( 'Sunday', 'default' ),
			'monday' => __( 'Monday', 'default'),
			'tuesday' => __( 'Tuesday', 'default' ),
			'wednesday' => __( 'Wednesday', 'default' ),
			'thursday' => __( 'Thursday', 'default' ),
			'friday' => __( 'Friday', 'default' ),
			'saturday' => __( 'Saturday', 'default' ),
		);
		$w_day = array_slice($all_days ,get_option('start_of_week'));
		foreach($all_days as $key=>$val){
			$w_day[$key] = $val;
		}
		foreach($store_days as $key => $val) {
			if($w_day[$key]) {
				$w_day[$key] = $val;
			}
		}
				
		$wclp_default_time_format = isset($location) ? $location->store_time_format : '24';
		if($wclp_default_time_format == '12'){
			foreach($w_day as $key=>$val){	
				if(isset($val['wclp_store_hour'])){
					$last_digit = explode(':', $val['wclp_store_hour']);
					if(end($last_digit) == '00') {
						$val['wclp_store_hour'] = date('g:ia', strtotime($val['wclp_store_hour']));
					} else {
						$val['wclp_store_hour'] = date('g:ia', strtotime($val['wclp_store_hour']));
					}
				}
				if(isset($val['wclp_store_hour_end'])){
					$last_digit = explode(':', $val['wclp_store_hour_end']);
					if(end($last_digit) == '00') {
						$val['wclp_store_hour_end'] = date('g:ia', strtotime($val['wclp_store_hour_end']));
					} else {
						$val['wclp_store_hour_end'] = date('g:ia', strtotime($val['wclp_store_hour_end']));
					}
				}
				$w_day[$key] = $val;				
			}	
		}
		
		if ( file_exists( $local_template ) && is_writable( $local_template )){	
			wc_get_template( 'myaccount/pickup-instruction.php', array( 'w_day' => $w_day, 'location' => $location, 'store_country' => $store_country, 'store_state' => $store_state ), 'advanced-local-pickup-for-woocommerce/', get_stylesheet_directory() . '/woocommerce/' );
		} else{
			wc_get_template( 'myaccount/pickup-instruction.php', array( 'w_day' => $w_day, 'location' => $location, 'store_country' => $store_country, 'store_state' => $store_state ), 'advanced-local-pickup-for-woocommerce/', wc_local_pickup()->get_plugin_path() . '/templates/' );	
		}			
		
	}	
	
	public function add_location_address_detail_emails($order, $sent_to_admin, $plain_text, $email) {		
		//IF display location details not enabel then @return;
		$wclp_show_pickup_instruction = get_option('wclp_show_pickup_instruction');
		
		if(!isset($wclp_show_pickup_instruction['display_in_processing_email']) && $email->id == 'customer_processing_order') return;
		if( $wclp_show_pickup_instruction['display_in_processing_email'] != '1'  && $email->id == 'customer_processing_order') return; 
		
		if( class_exists( 'Advanced_local_pickup_PRO' ) ){
			$wclp_location_display_opt = get_option('wclp_location_display_controls');
			if(!isset($wclp_location_display_opt['display_in_renewal_email']) && $email->id == 'customer_completed_renewal_order') return;
			if( $wclp_location_display_opt['display_in_renewal_email'] != '1'  && $email->id == 'customer_completed_renewal_order') return;
		}
		// Iterating through order shipping items
		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){			
			$shipping_method = $shipping_item_obj->get_method_id();						
		}
				
		//IF  dshipping method is not local pickup then @return;
		if( !isset($shipping_method ) ) return;
		if( isset($shipping_method ) && $shipping_method != 'local_pickup' ) return;		
		
		$local_template	= get_stylesheet_directory().'/woocommerce/emails/pickup-instruction.php';
		
		$data = $this->get_data();
		$location_id = get_option('location_defualt', min($data)->id);
		
		$location = $this->get_data_byid($location_id);
		
		$country_code = isset($location) ? $location->store_country : get_option('woocommerce_default_country');
		
		$split_country = explode( ":", $country_code );
		$store_country = isset($split_country[0]) ? $split_country[0] : '';
		$store_state   = isset($split_country[1]) ? $split_country[1] : '';
				
		$store_days = isset($location) ? unserialize($location->store_days) : array();
		$all_days = array(
			'sunday' => __( 'Sunday', 'default' ),
			'monday' => __( 'Monday', 'default'),
			'tuesday' => __( 'Tuesday', 'default' ),
			'wednesday' => __( 'Wednesday', 'default' ),
			'thursday' => __( 'Thursday', 'default' ),
			'friday' => __( 'Friday', 'default' ),
			'saturday' => __( 'Saturday', 'default' ),
		);
		$w_day = array_slice($all_days ,get_option('start_of_week'));
		foreach($all_days as $key=>$val){
			$w_day[$key] = $val;
		}
		foreach($store_days as $key => $val) {
			if($w_day[$key]) {
				$w_day[$key] = $val;
			}
		}
				
		$wclp_default_time_format = isset($location) ? $location->store_time_format : '24';
		if($wclp_default_time_format == '12'){
			foreach($w_day as $key=>$val){	
				if(isset($val['wclp_store_hour'])){
					$last_digit = explode(':', $val['wclp_store_hour']);
					if(end($last_digit) == '00') {
						$val['wclp_store_hour'] = date('g:ia', strtotime($val['wclp_store_hour']));
					} else {
						$val['wclp_store_hour'] = date('g:ia', strtotime($val['wclp_store_hour']));
					}
				}
				if(isset($val['wclp_store_hour_end'])){
					$last_digit = explode(':', $val['wclp_store_hour_end']);
					if(end($last_digit) == '00') {
						$val['wclp_store_hour_end'] = date('g:ia', strtotime($val['wclp_store_hour_end']));
					} else {
						$val['wclp_store_hour_end'] = date('g:ia', strtotime($val['wclp_store_hour_end']));
					}
				}
				$w_day[$key] = $val;				
			}	
		}
		
		if ( $email->id == 'customer_ready_pickup_order' || $email->id == 'customer_processing_order' ) { 

			if ( file_exists( $local_template ) && is_writable( $local_template )){	
				wc_get_template( 'emails/pickup-instruction.php', array( 'w_day' => $w_day, 'location' => $location, 'store_country' => $store_country, 'store_state' => $store_state ), 'advanced-local-pickup-for-woocommerce/', get_stylesheet_directory() . '/woocommerce/' );
			} else{
				wc_get_template( 'emails/pickup-instruction.php', array( 'w_day' => $w_day, 'location' => $location, 'store_country' => $store_country, 'store_state' => $store_state ), 'advanced-local-pickup-for-woocommerce/', wc_local_pickup()->get_plugin_path() . '/templates/' );	
			}	

	   }
		
	}
	
	function add_addional_content_on_processing_email($order, $sent_to_admin, $plain_text, $email){
				
		if( $email->id != 'customer_processing_order' ) return;
		
		// Iterating through order shipping items
		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){			
			$shipping_method = $shipping_item_obj->get_method_id();						
		}
				
		//IF  dshipping method is not local pickup then @return;
		if( !isset($shipping_method ) ) return;
		if( isset($shipping_method ) && $shipping_method != 'local_pickup' ) return;
		
		$settings = $this->wclp_general_setting_fields_func();		
		$addional_content = get_option('wclp_processing_additional_content',$settings['wclp_processing_additional_content']['default']);
		echo '<p>'._e($addional_content, 'advanced-local-pickup-for-woocommerce').'</p>';
	}
	
	/**
	 *
	 * Get times as option-list.
	 *
	 * @return string List of times
	 */
	function get_times( $default = '19:00', $interval = '+30 minutes' ) {

		$output[] = array();
		unset($output[0]);
		$current = strtotime( '00:00' );
		$end = strtotime( '23:59' );

		while( $current <= $end ) {
			$time = date( 'H:i', $current );
			$sel = ( $time == $default ) ? ' selected' : '';
		
			$output[date( 'h:i A', $current )] .= date( 'h:i A', $current );
			$current = strtotime( $interval, $current );
		}

		return $output;
	}
	
	public function wclp_update_state_dropdown_fun(){		
		$country = wc_clean($_POST['country']);
		$countries_obj   = new WC_Countries();
		$default_county_states = $countries_obj->get_states( $country );
		if(empty($default_county_states)){
			echo json_encode( array('state' => 'empty') );die();
		} else{
			ob_start();
			?>
			<option value="<?php echo $key?>"><?php _e('Select', 'woocommerce'); ?></option>
			<?php 
			foreach((array)$default_county_states as $key => $val ){?>																
				<option value="<?php echo $key?>"><?php echo $val?></option>
            <?php }
			$html = ob_get_clean();			
			echo json_encode( array('state' => $html) );die();
		}	
		echo json_encode( array('state' => 'empty') );die();		
	}
	
	public function wclp_update_work_hours_list_fun(){
		
		$data = $this->get_data();
		
		$location = $this->get_data_byid($_POST['id']);
		
		
		
		//$wclp_store_time_format = wc_clean($_POST['hour_format']);
		
		$wclp_store_time_format = '24';
		
		$all_days = array(
			'sunday' => __( 'Sunday', 'default' ),
			'monday' => __( 'Monday', 'default'),
			'tuesday' => __( 'Tuesday', 'default' ),
			'wednesday' => __( 'Wednesday', 'default' ),
			'thursday' => __( 'Thursday', 'default' ),
			'friday' => __( 'Friday', 'default' ),
			'saturday' => __( 'Saturday', 'default' ),
		);
		$days = array_slice($all_days ,get_option('start_of_week'));
		foreach($all_days as $key=>$val){
			$days[$key] = $val;
		} 
		
		ob_start();
		?>
		<div class="pickup_hours_div">
		<?php
		foreach((array)$days as $key => $val ){									
		
		$multi_checkbox_data = unserialize($location->store_days);
		
		if(isset($multi_checkbox_data[$key]['checked']) && $multi_checkbox_data[$key]['checked'] == 1){
			$checked="checked";
			$class = "hours-time";
		} else{
			$checked="";
			$class="";
		}
		
		$send_time_array = array();										
		for ( $hour = 0; $hour < 24; $hour++ ) {
			for ( $min = 0; $min < 60; $min = $min + 30 ) {
				$this_time = date( 'H:i', strtotime( "$hour:$min" ) );
				$send_time_array[ $this_time ] = $this_time;
			}	
		}
		
		?>
		<div class="wplp_pickup_duration" style="">
            <fieldset style=""><label class="" for="<?php echo $key?>" style="">
                <input type="checkbox" id="<?php echo $key?>" name="wclp_store_days[<?php echo $key?>][checked]" class="pickup_days_checkbox"  <?php echo $checked; ?> value="1"/>
                <span class="pickup_days_lable" style="width: auto;"><?php _e($val, 'advanced-local-pickup-for-woocommerce'); ?></span>	
            </label></fieldset>
            <fieldset class="wclp_pickup_time_fieldset" style="">
                
                <span class="hours <?php echo $class;?>" style="">
                    <?php if(isset($multi_checkbox_data[$key]['wclp_store_hour'])) { 
                        if($wclp_store_time_format == '12'){
                            $last_digit = explode(':', $multi_checkbox_data[$key]['wclp_store_hour']);
                            if(end($last_digit) == '00') {
                                $wclp_store_hour = date('g:ia', strtotime($multi_checkbox_data[$key]['wclp_store_hour']));
                            } else {
                                $wclp_store_hour = date('g:ia', strtotime($multi_checkbox_data[$key]['wclp_store_hour']));
                            }
                        } else {
                            $wclp_store_hour = $multi_checkbox_data[$key]['wclp_store_hour'];
                        }
                        echo $wclp_store_hour; 
                    }?>
                    - 
                    <?php if(isset($multi_checkbox_data[$key]['wclp_store_hour_end'])) { 
                        if($wclp_store_time_format == '12'){
                            $last_digit = explode(':', $multi_checkbox_data[$key]['wclp_store_hour_end']);
                            if(end($last_digit) == '00') {
                                $wclp_store_hour_end = date('g:ia', strtotime($multi_checkbox_data[$key]['wclp_store_hour_end']));
                            } else {
                                $wclp_store_hour_end = date('g:ia', strtotime($multi_checkbox_data[$key]['wclp_store_hour_end']));
                            }
                        } else {
                            $wclp_store_hour_end = $multi_checkbox_data[$key]['wclp_store_hour_end'];
                        }
                        echo $wclp_store_hour_end;
                    }?></span>
                <?php do_action('wclp_split_hours_hook', $key, $wclp_store_time_format, $location, $class); ?>
                <div id="" class="popupwrapper alp-hours-popup" style="display:none;">
                    <div class="popuprow">
                        <span class="dashicons dashicons-no-alt popup_close_icon"></span>
                        <div class="alp-hours-popup">
                            <div id="header-text">
                              <span style="width: 100px;display: inline-block;">From</span>
                              <span>To</span>
                            </div>
                             <span class="morning-time"><select class="select <?php echo $key?> wclp_pickup_time_select start" name="wclp_store_days[<?php echo $key?>][wclp_store_hour]"> <option value="" ><?php _e( 'Select', 'woocommerce' );?></option>
                                <?php foreach((array)$send_time_array as $key1 => $val1 ){
                                    if($wclp_store_time_format == '12'){
                                        $last_digit = explode(':', $val1);
                                        if(end($last_digit) == '00') {
                                            $val1 = date('g:ia', strtotime($val1));
                                        } else {
                                            $val1 = date('g:ia', strtotime($val1));
                                        }
                                    }
                                ?>
                                <option value="<?php echo $key1?>" <?php if(isset($multi_checkbox_data[$key]['wclp_store_hour']) && $multi_checkbox_data[$key]['wclp_store_hour'] == $key1){ echo 'selected'; }?>><?php echo $val1?></option>
                                <?php } ?>
                            </select>
                            <select class="select <?php echo $key?> wclp_pickup_time_select end" name="wclp_store_days[<?php echo $key?>][wclp_store_hour_end]"><option value=""><?php _e( 'Select', 'woocommerce' );?></option>
                                <?php foreach((array)$send_time_array as $key2 => $val2 ){
                                    if($wclp_store_time_format == '12'){
                                        $last_digit = explode(':', $val2);
                                        if( end($last_digit) == '00') {
                                            $val2 = date('g:ia', strtotime($val2));
                                        } else {
                                            $val2 = date('g:ia', strtotime($val2));
                                        }
                                    }
                                    ?>			
                                    <option value="<?php echo $key2?>" <?php if(isset($multi_checkbox_data[$key]['wclp_store_hour_end']) && $multi_checkbox_data[$key]['wclp_store_hour_end'] == $key2){ echo 'selected'; }?>><?php echo $val2?></option>
                                <?php } ?>
                            </select>
                            <span class="dashicons dashicons-trash" ></span>
                            </span>
                            <?php do_action('wclp_multi_hours_hook', $key, $wclp_store_time_format, $location, $send_time_array); ?>
                            <p class="add-interval" <?php if(!class_exists('Advanced_local_pickup_PRO') || (isset($multi_checkbox_data[$key]['wclp_store_hour_end2']) && $multi_checkbox_data[$key]['wclp_store_hour_end2'] != '') ){ ;echo 'style="display:none"'; }?>>+ Add Interval</p>
                        </div>
                        <?php do_action('wclp_apply_mltiple_popup_hook', $days, $key); ?>
                        <button type="button" class="wclp-apply button-primary" value="<?php echo $key?>"><?php _e('Apply & close', 'advanced-local-pickup-for-woocommerce'); ?></button>
                        <?php do_action('wclp_apply_mltiple_on_days_hook'); ?>
                    </div>
                    <div class="popupclose"></div>
                </div>
                </fieldset>
            </div> 						
		<?php }	?>
		</div>
		<?php
		$html = ob_get_clean();	
		echo json_encode( array('pickup_hours_div' => $html) );die();
	}
	
	public function wclp_update_edit_location_form_fun(){
		
		$data = $this->get_data();
		
		$location = $this->get_data_byid($_POST['id']);
		
		ob_start();
		include('views/wclp-edit-location-form.php');
		$html = ob_get_clean();			
		echo json_encode( array('edit_location_form' => $html) );die();
	}
	
	public function wclp_apply_work_hours_fun(){

		global $wpdb;
		$days = $_POST['days'];
		$location = $this->get_data_byid($_POST['id']);
		$store_days = unserialize($location->store_days);
		foreach($days as $key) {
			$store_days[$key]['checked'] = '1';
			if(isset($_POST['wclp_store_hour']) && isset($_POST['wclp_store_hour_end'])){
				$store_days[$key]['wclp_store_hour'] = sanitize_text_field($_POST['wclp_store_hour']);
				$store_days[$key]['wclp_store_hour_end'] = sanitize_text_field($_POST['wclp_store_hour_end']);
			}
			if(isset($_POST['wclp_store_hour2']) && isset($_POST['wclp_store_hour_end2'])){
				$store_days[$key]['wclp_store_hour2'] = sanitize_text_field($_POST['wclp_store_hour2']);
				$store_days[$key]['wclp_store_hour_end2'] = sanitize_text_field($_POST['wclp_store_hour_end2']);
			}
		}
		$location = array( 'store_days' => serialize($store_days) ); 				
		$wpdb->update( $this->table, $location, array('id' => wc_clean($_POST['id'])) );
		
		$this->wclp_update_work_hours_list_fun();						
			
	}
	
	function wclp_general_setting_fields_func() {		
		$show_pickup_instraction_option = array( 
			"display_in_processing_email" => __( 'Processing order email', 'advanced-local-pickup-for-woocommerce' ),
			"display_in_order_received_page" => __( 'Order received page', 'advanced-local-pickup-for-woocommerce' ),
			"display_in_order_details_page" => __( 'Customer account > order history > order details page', 'advanced-local-pickup-for-woocommerce' ),			
		);
		$settings = array(						
			'wclp_show_pickup_instruction' => array(
				//'title'    => __( '', 'advanced-local-pickup-for-woocommerce' ),				
				'id'       => 'wclp_show_pickup_instruction',
				'css'      => 'min-width:50px;',
				'default'  => '',
				'show'	   => true,
				'type'     => 'multiple_checkbox',
				'options'  => $show_pickup_instraction_option,
				'class'	   => '',
				'desc_tip' => __( '', 'advanced-local-pickup-for-woocommerce' ),
			),
			'wclp_processing_additional_content' => array(
				'title'    => __( 'Additional content on processing email in case of local pickup orders', 'advanced-local-pickup-for-woocommerce' ),
				'tooltip'  => __( 'Additional content on processing email in case of local pickup orders', 'advanced-local-pickup-for-woocommerce' ),
				'id'       => 'wclp_processing_additional_content',
				'css'      => 'min-width:50px;',
				'default'  => __( "You will receive an email when your order is ready for pickup.", 'advanced-local-pickup-for-woocommerce' ),
				'placeholder' => __( 'Additional content on processing email in case of local pickup orders', 'advanced-local-pickup-for-woocommerce' ),
				'show'	   => true,
				'type'     => 'textarea',
				'class'	   => 'additional_textarea',
				'desc_tip' => '',
			),
		);
		$settings = apply_filters( "alp_display_location_option_data_array", $settings );
		return $settings;
		
	}
	
	/*
	* get html of fields
	*/
	public function get_html2( $arrays ){
		
		$checked = '';
		?>
		
		<?php foreach( (array)$arrays as $id => $array ){
			if($array['show']){	
			?> 
		<?php if($array['type'] == 'dropdown'){ ?>               	
			<tr valign="top" class="html2_title_row <?php echo $array['class']; ?>">
				<?php if( !empty($array['title']) && isset($array['title'])){ ?>										
				<th><span class="row-label">
					<label for=""><?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
						<?php if( isset($array['tooltip']) ){?>
							<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
						<?php } ?>
					</label>
					<?php if( isset($array['desc_tip']) ){?>
							<p class="description"><?php echo $array['desc_tip']?></p>
						<?php } ?>
					<?php if( isset( $array['type'] ) && $array['type'] == 'dropdown' ){?>
						<?php
							if( isset($array['multiple']) ){
								$multiple = 'multiple';
								$field_id = $array['multiple'];
							} else {
								$multiple = '';
								$field_id = $id;
							}
						?>
						<fieldset>
							<select class="select select2" id="<?php echo $field_id?>" name="<?php echo $id?>" <?php echo $multiple;?> style="width:150px;"> <?php foreach((array)$array['options'] as $key => $val ){?>
									<?php
										$selected = '';
										if( isset($array['multiple']) ){
											if (in_array($key, (array)$this->data->$field_id ))$selected = 'selected';
										} else {
											if( get_option($array['id']) == (string)$key )$selected = 'selected';
										}
									
									?>
									<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
								<?php } ?><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
							</select>
						</fieldset>
					<?php } ?></span>
				</th>
				<?php } ?>
			</tr>
		<?php }
		if( !empty($array['title']) && $array['type'] == 'textarea' ){ ?>	             	
		<tr valign="top" class="html2_title_row <?php echo $array['class']; ?>">
			<th><span class="row-label">
				<label for=""><?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
					<?php if( isset($array['tooltip']) ){?>
						<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
					<?php } ?>
				</label>
				<?php if( isset($array['desc_tip']) ){?>
						<p class="description"><?php echo $array['desc_tip']?></p>
					<?php } ?>
				</span>
			</th>
		</tr>
		<tr valign="top" class="html2_title_row <?php echo $array['class']; ?>">
			<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
				<fieldset>
					<textarea rows="4" cols="20" class="input-text regular-input" type="textarea" name="<?php echo $id?>" id="<?php echo $id?>" style="" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>"><?php echo stripslashes(get_option($array['id'],$array['default'])); ?></textarea>
				</fieldset>
			</td>
		</tr>
		<?php } ?>
		<?php if($array['type'] != 'dropdown' && $array['type'] != 'textarea'){ ?>
			<tr class="<?php echo $array['class'];?>">
				<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
					<?php if( $array['type'] == 'checkbox' ){								
																					
							if(get_option($array['id'])){
								$checked = 'checked';
							} else{
								$checked = '';
							} 
						
						if(isset($array['disabled']) && $array['disabled'] == true){
							$disabled = 'disabled';
							$checked = '';
						} else{
							$disabled = '';
						}							
						?>
					<?php if($array['class'] == 'toggle'){?>
					<span class="mdl-list__item-secondary-action">
						<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?php echo $id?>">
							<input type="hidden" name="<?php echo $id?>" value="0"/>
							<input type="checkbox" id="<?php echo $id?>" name="<?php echo $id?>" class="mdl-switch__input" <?php echo $checked ?> value="1" <?php echo $disabled; ?>/>
						</label><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
					</span>
					<?php } else { ?>
						<span class="checkbox">
							<label class="checkbx-label" for="<?php echo $id?>">
								<input type="hidden" name="<?php echo $id?>" value="0"/>
								<input type="checkbox" id="<?php echo $id?>" name="<?php echo $id?>" class="checkbox-input" <?php echo $checked ?> value="1" <?php echo $disabled; ?>/>
							</label><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
						</span>
					<?php } ?>
					<?php } elseif( $array['type'] == 'textarea' ){ ?>
								<fieldset>
									<textarea rows="4" cols="20" class="input-text regular-input" type="textarea" name="<?php echo $id?>" id="<?php echo $id?>" style="" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>"><?php echo stripslashes(get_option($array['id'],$array['default'])); ?></textarea>
								</fieldset>
					<?php }  elseif( $array['type'] == 'multiple_checkbox' ) { ?>
						<?php
							$op = 1;	
							foreach((array)$array['options'] as $key => $val ){									
									$multi_checkbox_data = get_option($id);
									if(isset($multi_checkbox_data[$key]) && $multi_checkbox_data[$key] == 1){
										$checked="checked";
									} else{
										$checked="";
									}?>
							<div class="wplp_multiple <?php echo $array['class']?>">
								<span class="wplp_multiple_checkbox">
									<label class="" for="<?php echo $key?>">
										<input type="hidden" name="<?php echo $id?>[<?php echo $key?>]" value="0"/>
										<input type="checkbox" id="<?php echo $key?>" name="<?php echo $id?>[<?php echo $key?>]" class=""  <?php echo $checked; ?> value="1"/>
										<span class="multiple_label"><?php echo $val; ?></span>	
										</br>
									</label>																		
								</span>
							</div>								
					<?php } 
					
					} else if($array['type'] == 'text') { ?>
												
						<fieldset>
							<input class="input-text regular-input " type="text" name="<?php echo $id?>" id="<?php echo $id?>" style="" value="<?php echo get_option($array['id'], get_option($array['default']))?>" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>">
						</fieldset>
					<?php } ?>
					
				</td>
			</tr>
		<?php } ?>
	<?php } } ?>
	
<?php 
	}
	
	/*
     * get_zorem_pluginlist
     * 
     * return array
    */
    public function get_zorem_pluginlist(){
		
        if ( !empty( $this->zorem_pluginlist ) ) return $this->zorem_pluginlist;
        
        if ( false === ( $plugin_list = get_transient( 'zorem_pluginlist' ) ) ) {
            
            $response = wp_remote_get( 'https://www.zorem.com/wp-json/pluginlist/v1/' );
            
            if ( is_array( $response ) && ! is_wp_error( $response ) ) {
                $body    = $response['body']; // use the content
                $plugin_list = json_decode( $body );
                set_transient( 'zorem_pluginlist', $plugin_list, 60*60*24 );
            } else {
                $plugin_list = array();
            }
        }
        return $this->zorem_pluginlist = $plugin_list;
    }
	
	/*
	* change style of available for pickup and picked up order label
	*/	
	function footer_function(){
		if ( !is_plugin_active( 'woocommerce-order-status-manager/woocommerce-order-status-manager.php' ) ) {
			$rfp_bg_color = get_option('wclp_ready_pickup_status_label_color','#365EA6');
			$rfp_color = get_option('wclp_ready_pickup_status_label_font_color','#fff');
			
			$pu_bg_color = get_option('wclp_pickup_status_label_color','#f1a451');
			$pu_color = get_option('wclp_pickup_status_label_font_color','#fff');						
			?>
			<style>
			.order-status.status-ready-pickup,.order-status-table .order-label.wc-ready-pickup{
				background: <?php echo $rfp_bg_color; ?>;
				color: <?php echo $rfp_color; ?>;
			}						
			.order-status.status-pickup,.order-status-table .order-label.wc-pickup{
				background: <?php echo $pu_bg_color; ?>;
				color: <?php echo $pu_color; ?>;
			}	
			</style>
			<?php
		}
	}
	
	/*
	* Add action button in order list to change order status from processing to ready for pickup and ready for pickup to Picked Up
	*/
	public function add_local_pickup_order_status_actions_button($actions, $order){			
		
		// Iterating through order shipping items
		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){			
			$shipping_method = $shipping_item_obj->get_method_id();						
		}
				
		//IF  dshipping method is not local pickup then @return;
		if( !isset($shipping_method ) ) return $actions;
		if( isset($shipping_method ) && $shipping_method != 'local_pickup' ) return $actions;
		
		?>
		<style>
		<?php 
		$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
		if($ready_for_pickup == true){ ?>
			.widefat .column-wc_actions a.ready_for_pickup_icon.button::after{
				content: "";
				width: 20px;
				height: 20px;
				background: url("<?php echo wc_local_pickup()->plugin_dir_url()?>assets/images/rady_for_pickup_icon.png") no-repeat;
				background-size: contain;			
				top: 3px;
				left: 2px;
			}
		<?php } ?>
		<?php 
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){ ?>
			.widefat .column-wc_actions a.picked_up_icon.button::after{
				content: "";
				width: 20px;
				height: 20px;
				background: url("<?php echo wc_local_pickup()->plugin_dir_url()?>assets/images/picked_up_icon.png") no-repeat;
				background-size: contain;			
				top: 3px;
				left: 2px;
			}		
		<?php } ?>
		</style>
		<?php
		$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
		if($ready_for_pickup == true){
			if ( $order->has_status( array( 'processing' ) ) ) {
				// Get Order ID (compatibility all WC versions)
				$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				// Set the action button
				$actions['ready_for_pickup'] = array(
					'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=ready-pickup&order_id=' . $order_id ), 'woocommerce-mark-order-status' ),
					'name'      => __( 'Mark order as ready for pickup', 'advanced-local-pickup-for-woocommerce' ),
					'action'    => "ready_for_pickup_icon", // keep "view" class for a clean button CSS
				);
				unset($actions['complete']);
			}
		}
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){
			if ( $order->has_status( array( 'ready-pickup' ) ) ) {
				// Get Order ID (compatibility all WC versions)
				$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				// Set the action button
				$actions['pickup'] = array(
					'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=pickup&order_id=' . $order_id ), 'woocommerce-mark-order-status' ),
					'name'      => __( 'Mark order as picked up', 'advanced-local-pickup-for-woocommerce' ),
					'action'    => "picked_up_icon", // keep "view" class for a clean button CSS
				);
			}
		} else {
			if ( $order->has_status( array( 'ready-pickup' ) ) ) {
				$actions['complete'] = array(
					'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
					'name'   => __( 'Complete', 'woocommerce' ),
					'action' => 'complete',
				);
			}
		}			
				
		return $actions;
	}	
	
	/*
	* add order again button for delivered order status	
	*/
	function add_reorder_button_pickup( $statuses ){
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){
			$statuses[] = 'pickup';
		}
		return $statuses;	
	}
	
	public function get_option_value_from_array($array,$key,$default_value){		
		$array_data = get_option($array);	
		$value = '';
		
		if(isset($array_data[$key])){
			$value = $array_data[$key];	
		}					
		
		if($value == ''){
			$value = $default_value;
		}
		return $value;
	}
}