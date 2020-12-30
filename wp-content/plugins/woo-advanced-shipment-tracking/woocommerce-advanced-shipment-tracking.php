<?php
/**
 * @wordpress-plugin
 * Plugin Name: Advanced Shipment Tracking for WooCommerce 
 * Plugin URI: https://www.zorem.com/products/woocommerce-advanced-shipment-tracking/ 
 * Description: Add shipment tracking information to your WooCommerce orders and provide customers with an easy way to track their orders. Shipment tracking Info will appear in customers accounts (in the order panel) and in WooCommerce order complete email. 
 * Version: 3.2.0.1
 * Author: zorem
 * Author URI: https://www.zorem.com 
 * License: GPL-2.0+
 * License URI: 
 * Text Domain: woo-advanced-shipment-tracking 
 * WC tested up to: 4.8
*/


class zorem_woocommerce_advanced_shipment_tracking {
	
	/**
	 * WooCommerce Advanced Shipment Tracking version.
	 *
	 * @var string
	 */
	public $version = '3.2.0.1';
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {
		
		$this->plugin_file = __FILE__;
		// Add your templates to this array.
		
		if(!defined('SHIPMENT_TRACKING_PATH')) define( 'SHIPMENT_TRACKING_PATH', $this->get_plugin_path());		
		
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
		
		if ( $this->is_wc_active() ) {			
			// Include required files.
			$this->includes();
					
			// Init REST API.
			$this->init_rest_api();
			
			//start adding hooks
			$this->init();
			
			//admin class init
			$this->admin->init();
			
			//admin class init
			$this->settings->init();
			
			//plugin install class init
			$this->install->init();
			
			//plugin admin_notice class init
			$this->admin_notice->init();													
			
			add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );	
			
			if(!class_exists('trackship_for_woocommerce')){				
				$this->late_shipments->init();
				$this->trackship->init();
			}
			
			add_action( 'admin_footer', array( $this, 'uninstall_notice') );	
			add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'ast_plugin_action_links' ) );	
		}		
    }
	
	/**
	 * Check if WooCommerce is active
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}
		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Display WC active notice
	 *
	 * @access public
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p><?php printf( __( 'Please install and activate %sWooCommerce%s for Advanced Shipment Tracking for WooCommerce!', 'woo-advanced-shipment-tracking' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}
	
	/*
	* init when class loaded
	*/
	public function init(){								
		
		add_action( 'init', array( $this, 'wst_load_textdomain'));
		register_activation_hook( __FILE__, array( $this->install, 'woo_shippment_tracking_install' ));
		
		add_action( 'add_meta_boxes', array( $this->actions, 'add_meta_box' ) );		
		add_action( 'woocommerce_view_order', array( $this->actions, 'show_tracking_info_order' ) );			
		
		//add_filter( 'woocommerce_my_account_my_orders_columns', array( $this->actions, 'add_column_my_account_orders' ) );
		add_action( 'woocommerce_my_account_my_orders_actions', array( $this->actions, 'add_column_my_account_orders_ast_track_column' ),10, 2 );
		
		add_action( 'wp_ajax_wc_shipment_tracking_delete_item', array( $this->actions, 'meta_box_delete_tracking' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this->actions, 'save_meta_box' ),0, 2 );	
		add_action( 'wp_ajax_wc_shipment_tracking_save_form', array( $this->actions, 'save_meta_box_ajax' ) );	

		add_action( 'wp_ajax_reassign_order_status', array( $this, 'reassign_order_status' ) );			
		
		if(isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview']){
			$preview = true;
		} else{
			$preview = false;
		}
		
		if(!$preview){			
			$tracking_info_settings = get_option('tracking_info_settings');			
			if(isset($tracking_info_settings['display_tracking_info_at']) && $tracking_info_settings['display_tracking_info_at'] == 'after_order'){
				add_action( 'woocommerce_email_order_meta', array( $this->actions, 'email_display' ), 0, 4 );
			} else{
				add_action( 'woocommerce_email_before_order_table', array( $this->actions, 'email_display' ), 0, 4 );
			}	
		}				
		
		// Custom tracking column in admin orders list.
		add_filter( 'manage_shop_order_posts_columns', array( $this->actions, 'shop_order_columns' ), 99 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this->actions, 'render_shop_order_columns' ) );
		
		add_action('admin_footer', array( $this->actions, 'custom_validation_js'));
				
		add_action( 'wp_ajax_add_inline_tracking_number', array( $this->actions, 'save_inline_tracking_number' ) );	

		add_filter( 'get_ast_provider_name', array( $this->actions, 'get_ast_provider_name_callback' ),10,2 );
		add_filter( 'get_shipping_provdider_src', array( $this->actions, 'get_shipping_provdider_src_callback' ));		
				
		//load css js 
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'admin_styles' ), 4);
		
		//Custom Woocomerce menu
		add_action('admin_menu', array( $this->admin, 'register_woocommerce_menu' ), 99 );
				
		//ajax save admin api settings
		add_action( 'wp_ajax_wc_ast_settings_form_update', array( $this->admin, 'wc_ast_settings_form_update_callback' ) );
		
		add_action( 'wp_ajax_wc_ast_custom_order_status_form_update', array( $this->admin, 'wc_ast_custom_order_status_form_update') );

		$wc_ast_status_delivered = get_option('wc_ast_status_delivered');		
		if($wc_ast_status_delivered == 1) 
		add_action( 'woocommerce_order_status_delivered', array( $this, 'email_trigger_delivered' ), 10, 2 );	
		
		$wc_ast_status_partial_shipped = get_option('wc_ast_status_partial_shipped');		
		if($wc_ast_status_partial_shipped == 1) 
		add_action( 'woocommerce_order_status_partial-shipped', array( $this, 'email_trigger_partial_shipped' ), 10, 2 );			
	
		$wc_ast_status_updated_tracking = get_option('wc_ast_status_updated_tracking');		
		if($wc_ast_status_updated_tracking == 1) 
		add_action( 'woocommerce_order_status_updated-tracking', array( $this, 'email_trigger_updated_tracking' ), 10, 2 );	
					
		if(!class_exists('trackship_for_woocommerce')){	
			add_action( 'template_redirect', array( $this->front, 'preview_tracking_page') );		
		}
	}		
	
	/**
	 * Send email when order status change to "Delivered"
	 *
	*/
	public function email_trigger_delivered($order_id, $order = false){			
		require_once( 'includes/email-manager.php' );				
		WC()->mailer()->emails['WC_Email_Customer_Delivered_Order']->trigger( $order_id, $order );		
	}
	
	/**
	 * Send email when order status change to "Partial Shipped"
	 *
	*/
	public function email_trigger_partial_shipped($order_id, $order = false){		
		require_once( 'includes/email-manager.php' );				
		WC()->mailer()->emails['WC_Email_Customer_Partial_Shipped_Order']->trigger( $order_id, $order );
	}	
	
	/**
	 * Send email when order status change to "Updated Tracking"
	 *
	*/
	public function email_trigger_updated_tracking($order_id, $order = false){		
		require_once( 'includes/email-manager.php' );					
		WC()->mailer()->emails['WC_Email_Customer_Updated_Tracking_Order']->trigger( $order_id, $order );
	}	
	
	/**
	 * Init advanced shipment tracking REST API.
	 *
	*/
	private function init_rest_api() {
		add_action( 'rest_api_init', array( $this, 'rest_api_register_routes' ) );
	}
		
	/*** Method load Language file ***/
	function wst_load_textdomain() {
		load_plugin_textdomain( 'woo-advanced-shipment-tracking', false, dirname( plugin_basename(__FILE__) ) . '/lang' );
	}
		
	/**
	 * Gets the absolute plugin path without a trailing slash, e.g.
	 * /path/to/wp-content/plugins/plugin-directory.
	 *
	 * @return string plugin path
	 */
	public function get_plugin_path() {
		if ( isset( $this->plugin_path ) ) {
			return $this->plugin_path;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		return $this->plugin_path;
	}	
	
	/**
	 * Get shipping providers with normalized values (respect decimal separator
	 * settings), for display.
	 *
	 * @return array
	 */
	public function get_normalized_shipping_providers() {
		$shipping_providers = $this->get_shipping_providers( ARRAY_A );		
		$decimal_separator = wc_get_price_decimal_separator();
		$normalize_keys = array(
			'id',
			'provider_name',
			'shipping_country',
			'provider_url',
		);

		foreach ( $shipping_providers as $index => $shipping_provider ) {
			foreach ( $normalize_keys as $key ) {
				if ( ! isset( $shipping_provider[ $key ] ) ) {
					continue;
				}

				$shipping_providers[ $index ][ $key ] = str_replace( '.', $decimal_separator, $shipping_providers[ $index ][ $key ] );
			}
		}

		return $shipping_providers;
	}
	
	/**
	 * Get raw shipping providers from the DB.
	 *
	 * @param string $output Output format.
	 * @return mixed
	 */
	public function get_shipping_providers( $output = OBJECT ) {
		global $wpdb;
		$woo_shippment_table_name = $wpdb->prefix . 'woo_shippment_provider';
		return $wpdb->get_results( "
			SELECT * FROM {$woo_shippment_table_name}			
			WHERE shipping_default = 0
			ORDER BY id ASC
		", $output );
	}
	
	/*
	* include files
	*/
	private function includes(){				
		
		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking.php';
		$this->actions = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		
		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-install.php';
		$this->install = WC_Advanced_Shipment_Tracking_Install::get_instance();
		
		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-admin-notice.php';
		$this->admin_notice = WC_Advanced_Shipment_Tracking_Admin_notice::get_instance();
		
		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-admin.php';
		$this->admin = WC_Advanced_Shipment_Tracking_Admin::get_instance();	

		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-settings.php';
		$this->settings = WC_Advanced_Shipment_Tracking_Settings::get_instance();														
		
		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-license.php';
		$this->license = WC_Advanced_Shipment_Tracking_License::get_instance();

		if(!class_exists('trackship_for_woocommerce')){						
			
			require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-late-shipments.php';
			$this->late_shipments = WC_Advanced_Shipment_Tracking_Late_Shipments::get_instance();
			
			require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-trackship.php';
			$this->trackship = WC_Advanced_Shipment_Tracking_Trackship::get_instance();		
			
			require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-front.php';
			$this->front = WC_Advanced_Shipment_Tracking_Front::get_instance();
			
			//api call function 
			require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-api-call.php';
		}
		
		
		//cron function
		require_once $this->get_plugin_path() . '/includes/class-wc-advanced-shipment-tracking-cron.php';						
		
		require_once $this->get_plugin_path() . '/includes/email-manager.php';				
	}
	
	/**
	 * Register shipment tracking routes.
	 *
	 * @since 1.5.0
	 */
	public function rest_api_register_routes() {
		if ( ! is_a( WC()->api, 'WC_API' ) ) {
			return;
		}
		
		require_once $this->get_plugin_path() . '/includes/api/class-wc-advanced-shipment-tracking-rest-api-controller.php';
		
		// Register route with default namespace wc/v3.
		$ast_api_controller = new WC_Advanced_Shipment_Tracking_REST_API_Controller();
		$ast_api_controller->register_routes();				
		
		// These are all the same code but with different namespaces for compatibility reasons.
		$ast_api_controller_v1 = new WC_Advanced_Shipment_Tracking_REST_API_Controller();
		$ast_api_controller_v1->set_namespace( 'wc/v1' );
		$ast_api_controller_v1->register_routes();

		$ast_api_controller_v2 = new WC_Advanced_Shipment_Tracking_REST_API_Controller();
		$ast_api_controller_v2->set_namespace( 'wc/v2' );
		$ast_api_controller_v2->register_routes();
		
		$ast_api_controller_v3 = new WC_Advanced_Shipment_Tracking_REST_API_Controller();
		$ast_api_controller_v3->set_namespace( 'wc/v3' );
		$ast_api_controller_v3->register_routes();
		
		$shipment_api_controller_v3 = new WC_Advanced_Shipment_Tracking_REST_API_Controller();
		$shipment_api_controller_v3->set_namespace( 'wc-shipment-tracking/v3' );
		$shipment_api_controller_v3->register_routes();
		
	}
	
	/*
	* include file on plugin load
	*/
	public function on_plugins_loaded() {		
		require_once $this->get_plugin_path() . '/includes/email-manager.php';
		
		require_once $this->get_plugin_path() . '/includes/customizer/class-wcast-customizer.php';
		
		require_once $this->get_plugin_path() . '/includes/customizer/class-wc-tracking-info-customizer.php';	
		
		require_once $this->get_plugin_path() . '/includes/customizer/class-wc-email-customizer.php';
		
		require_once $this->get_plugin_path() . '/includes/customizer/class-wc-partial-shipped-email-customizer.php';						
		
		require_once $this->get_plugin_path() . '/includes/customizer/class-wc-updated-tracking-email-customizer.php';				

		require_once $this->get_plugin_path() . '/includes/tracking-info.php';

		require_once $this->get_plugin_path() . '/includes/class-wc-admin-notices.php';		
	}
	
	/*
	* return plugin directory URL
	*/
	public function plugin_dir_url(){
		return plugin_dir_url( __FILE__ );
	}		
	
	/*
	* Plugin uninstall code 
	*/	
	public function uninstall_notice(){
		$screen = get_current_screen();
		
		if($screen->parent_file == 'plugins.php'){
			wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		}
		
		$delivered_count = wc_orders_count( 'delivered' );
		$ps_count = wc_orders_count( 'partial-shipped' );
		$ut_count = wc_orders_count( 'updated-tracking' );
		
		$order_statuses = wc_get_order_statuses(); 
		
		unset($order_statuses['wc-partial-shipped']);				
		unset($order_statuses['wc-updated-tracking']);
		unset($order_statuses['wc-delivered']);
		
		if($delivered_count > 0 || $ps_count > 0 || $ut_count > 0){ ?>
		
		<script>
		
		jQuery(document).on("click","[data-slug='woo-advanced-shipment-tracking'] .deactivate a",function(e){			
			e.preventDefault();
			jQuery('.uninstall_popup').show();
			var theHREF = jQuery(this).attr("href");
			jQuery(document).on("click",".uninstall_plugin",function(e){
				jQuery("body").block({
					message: null,
					overlayCSS: {
						background: "#fff",
						opacity: .6
					}	
				});	
				var form = jQuery('#order_reassign_form');
				jQuery.ajax({
					url: ajaxurl,		
					data: form.serialize(),		
					type: 'POST',		
					success: function(response) {
						jQuery("body").unblock();			
						window.location.href = theHREF;
					},
					error: function(response) {
						console.log(response);			
					}
				});				
			});			
		});
		
		jQuery(document).on("click",".popupclose",function(e){
			jQuery('.uninstall_popup').hide();
		});
		
		jQuery(document).on("click",".uninstall_close",function(e){
			jQuery('.uninstall_popup').hide();
		});

		jQuery(document).on("click",".popup_close_icon",function(e){
			jQuery('.uninstall_popup').hide();
		});	
		</script>
		<div id="" class="popupwrapper uninstall_popup" style="display:none;">
			<div class="popuprow">
				<div class="popup_header">
					<h3 class="popup_title">Advanced Shipment Tracking for WooCommerce</h3>
					<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ast-logo.png" class="poppup_header_logo">
					<span class="dashicons dashicons-no-alt popup_close_icon"></span>
				</div>
				<div class="popup_body">				
					<form method="post" id="order_reassign_form">					
						<?php if( $delivered_count > 0 ){ ?>
							
							<p><?php echo sprintf(__('We detected %s orders that use the Delivered order status, You can reassign these orders to a different status', 'woo-advanced-shipment-tracking'), $delivered_count); ?></p>
							
							<select id="reassign_delivered_order" name="reassign_delivered_order" class="reassign_select">
								<option value=""><?php _e('Select', 'woocommerce'); ?></option>
								<?php foreach($order_statuses as $key => $status){ ?>
									<option value="<?php echo $key; ?>"><?php echo $status; ?></option>
								<?php } ?>
							</select>
						
						<?php } ?>
						<?php if( $ps_count > 0 ){ ?>
							
							<p><?php echo sprintf(__('We detected %s orders that use the Partially Shipped order status, You can reassign these orders to a different status', 'woo-advanced-shipment-tracking'), $ps_count); ?></p>					
							
							<select id="reassign_ps_order" name="reassign_ps_order" class="reassign_select">
								<option value=""><?php _e('Select', 'woocommerce'); ?></option>
								<?php foreach($order_statuses as $key => $status){ ?>
									<option value="<?php echo $key; ?>"><?php echo $status; ?></option>
								<?php } ?>
							</select>
						
						<?php } ?>
						<?php if( $ut_count > 0 ){ ?>
							
							<p><?php echo sprintf(__('We detected %s orders that use the Updated Tracking order status, You can reassign these orders to a different status', 'woo-advanced-shipment-tracking'), $ut_count); ?></p>
							
							<select id="reassign_ut_order" name="reassign_ut_order" class="reassign_select">
								<option value=""><?php _e('Select', 'woocommerce'); ?></option>
								<?php foreach($order_statuses as $key => $status){ ?>
									<option value="<?php echo $key; ?>"><?php echo $status; ?></option>
								<?php } ?>
							</select>
						
						<?php } ?>
						<p>	
							<input type="hidden" name="action" value="reassign_order_status">
							<input type="button" value="Uninstall" class="uninstall_plugin button-primary btn_green">
							<input type="button" value="Close" class="uninstall_close button-primary btn_red">				
						</p>
					</form>	
				</div>	
			</div>
			<div class="popupclose"></div>
		</div>
		<?php }
	}
	
	function reassign_order_status(){
		$reassign_delivered_order = $_POST['reassign_delivered_order'];
		$reassign_ps_order = $_POST['reassign_ps_order'];
		$reassign_ut_order = $_POST['reassign_ut_order'];				
		
		if($reassign_delivered_order != ''){
			
			$args = array(
				'status' => 'delivered',
				'limit' => '-1',
			);
			
			$orders = wc_get_orders( $args );
			
			foreach($orders as $order){				
				$order_id = $order->get_id();
				$order = new WC_Order($order_id);
				$order->update_status($reassign_delivered_order);				
			}			
		}
		
		if($reassign_ps_order != ''){
			
			$args = array(
				'status' => 'partial-shipped',
				'limit' => '-1',
			);
			
			$ps_orders = wc_get_orders( $args );
			
			foreach($ps_orders as $order){				
				$order_id = $order->get_id();
				$order = new WC_Order($order_id);
				$order->update_status($reassign_ps_order);				
			}			
		}
		
		if($reassign_ut_order != ''){
			
			$args = array(
				'status' => 'updated-tracking',
				'limit' => '-1',
			);
			
			$ut_orders = wc_get_orders( $args );
			
			foreach($ut_orders as $order){				
				$order_id = $order->get_id();
				$order = new WC_Order($order_id);
				$order->update_status($reassign_ut_order);				
			}			
		}
		echo 1;die();		
	}
	
	/**
	* Add plugin action links.
	*
	* Add a link to the settings page on the plugins.php page.
	*
	* @since 2.6.5
	*
	* @param  array  $links List of existing plugin action links.
	* @return array         List of modified plugin action links.
	*/
	function ast_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=woocommerce-advanced-shipment-tracking' ) ) . '">' . __( 'Settings' ) . '</a>'
		), $links );
		return $links;
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
function wc_advanced_shipment_tracking() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new zorem_woocommerce_advanced_shipment_tracking();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_advanced_shipment_tracking();