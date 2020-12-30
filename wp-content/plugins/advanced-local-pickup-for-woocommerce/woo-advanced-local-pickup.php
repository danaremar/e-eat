<?php
/*
* Plugin Name: Advanced Local Pickup for WooCommerce
* Plugin URI:  https://www.zorem.com/shop
* Description: The Advanced Local Pickup (ALP) helps you handle local pickup orders more conveniently by extending the WooCommerce Local Pickup shipping method.
* Author: zorem
* Author URI: https://www.zorem.com/
* Version: 1.2.4
* Text Domain: advanced-local-pickup-for-woocommerce
* Domain Path: /lang/
* WC requires at least: 4.0
* WC tested up to: 4.8
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce_Local_Pickup {
	
	/**
	 * Local Pickup for WooCommerce
	 *
	 * @var string
	 */
	public $version = '1.2.4';
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since  1.0.0
	*/
	public function __construct() {

		// Check if Wocoomerce is activated
		if ( $this->is_wc_active() ) {
			$this->includes();
			$this->init();			
			add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		}
		add_action( 'admin_footer', array( $this, 'uninstall_notice') );
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
			<p><?php printf( __( 'Please install and activate %sWooCommerce%s for WC local pickup to work!', 'advanced-local-pickup-for-woocommerce' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Include plugin file.
	 *
	 * @since 1.0.0
	 *
	 */	
	function includes() {		
		require_once $this->get_plugin_path() . '/include/wc-local-pickup-admin.php';
		$this->admin = WC_Local_Pickup_admin::get_instance();	

		require_once $this->get_plugin_path() . '/include/wc-local-pickup-installation.php';
		$this->install = WC_Local_Pickup_install::get_instance();	
	}

	/**
	 * Initialize plugin
	 *
	 * @access private
	 * @since  1.0.0
	*/
	private function init() {
		
		//callback on activate plugin
		register_activation_hook( __FILE__, array( $this, 'table_create' ) );
		
		// Load plugin textdomain
		add_action('plugins_loaded', array($this, 'load_textdomain'));
		
		//callback for migration function
		add_action( 'admin_init', array( $this->install , 'wclp_update_install_callback' ) );
		
		//load javascript in admin
		add_action('admin_enqueue_scripts', array( $this, 'alp_script_enqueue' ) );
		
		//callback for add action link for plugin page	
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),  array( $this , 'my_plugin_action_links' ));

		add_action( 'admin_notices', array( $this, 'admin_notice_after_update' ) );		
		add_action('admin_init', array( $this, 'wplp_plugin_notice_ignore' ) );
		
		add_action( 'admin_notices', array( $this, 'admin_notice_pro_update' ) );		
		add_action('admin_init', array( $this, 'wplp_pro_notice_ignore' ) );
		
		add_action( 'wp_ajax_reassign_order_status', array( $this, 'reassign_order_status' ) );
		
		// Add to custom email for WC Order statuses
		add_filter( 'woocommerce_email_classes', array( $this, 'custom_init_emails' ) );
		add_action( 'woocommerce_order_status_ready-pickup', array( $this, 'email_trigger_ready_pickup' ), 10, 2 );
		add_action( 'woocommerce_order_status_pickup', array( $this, 'email_trigger_pickup' ), 10, 2 );
	}
	
	/**
	 * database functions
	*/
	function table_create(){
		
		global $wpdb;
		$this->table = $wpdb->prefix."alp_pickup_location";
		
		if($wpdb->get_var("show tables like '$this->table'") != $this->table) {
			$create_table_query = "
				CREATE TABLE IF NOT EXISTS `{$this->table}` (
					`id` int NOT NULL AUTO_INCREMENT,
					`store_name` text NULL,
					`store_address` text NULL,
					`store_address_2` text NULL,
					`store_city` text NULL,
					`store_country` text NULL,
					`store_postcode` text NULL,
					`store_phone` text NULL,
					`store_time_format` text NULL,
					`store_days` text NULL,
					`store_instruction` text NULL,
					PRIMARY KEY (id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
			";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $create_table_query );
		}

	}

	
	/*
	* include file on plugin load
	*/
	public function on_plugins_loaded() {		
		require_once $this->get_plugin_path() . '/include/customizer/wclp-customizer.php';				
		require_once $this->get_plugin_path() . '/include/customizer/wc-ready-pickup-email-customizer.php';
		require_once $this->get_plugin_path() . '/include/customizer/wc-pickup-email-customizer.php';
		require_once $this->get_plugin_path() . '/include/customizer/wclp-pickup-instruction-customizer.php';
		
		require_once $this->get_plugin_path() . '/include/wclp-wc-admin-notices.php';	
	}
	
	/*
	* load text domain
	*/
	public function load_textdomain(){
		load_plugin_textdomain( 'advanced-local-pickup-for-woocommerce', false, plugin_dir_path( plugin_basename(__FILE__) ) . 'lang/' );
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
	
	public static function get_plugin_domain(){
		return __FILE__;
	}
	
	/*
	* plugin file directory function
	*/	
	public function plugin_dir_url(){
		return plugin_dir_url( __FILE__ );
	}
	
	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	function my_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=local_pickup' ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>'
		), $links );
		
		if(!class_exists('Advanced_local_pickup_PRO')) {
			$links = array_merge( $links, array(
				'<a target="_blank" style="color: #45b450; font-weight: bold;" href="' . esc_url( 'https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/?utm_source=wp-admin&utm_medium=ALPPRO&utm_campaign=add-ons') . '">' . __( 'Go Pro', 'woocommerce' ) . '</a>'
			) );
		}
		
		return $links;
	}
	
	/*
	* Display admin notice on plugin install or update
	*/
	public function admin_notice_after_update(){ 		
		
		if ( get_option('wplp_review_notice_ignore') ) return;
		
		$dismissable_url = esc_url(  add_query_arg( 'wplp-review-ignore-notice', 'true' ) );
		?>		
		<style>		
		.wp-core-ui .notice.wplp-dismissable-notice{
			position: relative;
			padding-right: 38px;
		}
		.wp-core-ui .notice.wplp-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.btn_review_notice {
			background: transparent;
			color: #f1a451;
			border-color: #f1a451;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>	
		<div class="notice updated notice-success wplp-dismissable-notice">
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<p>Hey, I noticed you are using the Advanced Local Pickup Plugin - that’s awesome!</br>Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?</p>
			<p>Eran Shor</br>Founder of zorem</p>
			<a class="button-primary btn_review_notice" target="blank" href="https://wordpress.org/support/plugin/advanced-local-pickup-for-woocommerce/reviews/#new-post">Ok, you deserve it</a>
			<a class="button-primary btn_review_notice" href="<?php echo $dismissable_url; ?>">Nope, maybe later</a>
			<a class="button-primary btn_review_notice" href="<?php echo $dismissable_url; ?>">I already did</a>
		</div>
	<?php 		
	}	


	/*
	* Hide admin notice on dismiss of ignore-notice
	*/
	public function wplp_plugin_notice_ignore(){
		if (isset($_GET['wplp-review-ignore-notice'])) {
			update_option( 'wplp_review_notice_ignore', 'true' );
		}
	}
	
	/*
	* Display admin notice on plugin install or update
	*/
	public function admin_notice_pro_update(){ 		
		
		if ( get_option('wplp_pro_notice_ignore') ) return;
		
		$dismissable_url = esc_url(  add_query_arg( 'wplp-pro-ignore-notice', 'true' ) );
		?>		
		<style>		
		.wp-core-ui .notice.wplp-dismissable-notice{
			position: relative;
			padding-right: 38px;
		}
		.wp-core-ui .notice.wplp-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.btn_pro_notice {
			background: transparent;
			color: #395da4;
			border-color: #395da4;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>	
		<div class="notice updated notice-success wplp-dismissable-notice">
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h3>Advanced Local Pickup PRO</h3>
			<p>We just released a <a href="https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/">Pro</a> version for Advanced Local Pickup with multiple pickup locations, split work hours, local pickup discounts and more..</p>
			<p>Enjoy our early bird discount, use code ALPPRO10 during checkout.</p>
			<a class="button-primary btn_pro_notice" target="blank" href="https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/">Go Pro ></a>
		</div>
	<?php 		
	}	


	/*
	* Hide admin notice on dismiss of ignore-notice
	*/
	public function wplp_pro_notice_ignore(){
		if (isset($_GET['wplp-pro-ignore-notice'])) {
			update_option( 'wplp_pro_notice_ignore', 'true' );
		}
	}
	
	/*
	* Add admin javascript
	*/	
	public function alp_script_enqueue() {
		
		
		// Add condition for css & js include for admin page  
		if(!isset($_GET['page'])) {
				return;
		}
		if(  $_GET['page'] != 'local_pickup') {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';	
		// Add the color picker css file       
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
			
		// Add the WP Media 
		wp_enqueue_media();
		
		// Add tiptip js and css file
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
	
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		
		wp_enqueue_style('select2-wclp', plugins_url('assets/css/select2.min.css', __FILE__ ));
		wp_enqueue_script('select2-wclp', plugins_url('assets/js/select2.min.js', __FILE__));
		
		wp_enqueue_script( 'alp-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array(), $this->version );
		wp_enqueue_script( 'alp-material-min-js', plugin_dir_url(__FILE__) . 'assets/js/material.min.js', array(), $this->version );
		wp_enqueue_style( 'alp-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), $this->version );
		wp_enqueue_style( 'alp-material-css', plugin_dir_url(__FILE__) . 'assets/css/material.css', array(), $this->version );
		
		wp_localize_script( 'alp-admin-js', 'alp_object', 
		  	array( 
				'admin_url' => admin_url(),
			) 
		);
	}
	
	// Add to custom email for WC Order statuses
	public function custom_init_emails( $emails ) {

		// Include the email class file if it's not included already
		if (!defined('WC_LOCAL_PICKUP_TEMPLATE_PATH')) define('WC_LOCAL_PICKUP_TEMPLATE_PATH', wc_local_pickup()->get_plugin_path() . '/templates/');
		$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
		if($ready_for_pickup == true){
			if ( ! isset( $emails[ 'WC_Email_Customer_Ready_Pickup_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Ready_Pickup_Order' ] = include_once( 'include/emails/ready-pickup-order.php' );
			}
		}
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){
			if ( ! isset( $emails[ 'WC_Email_Customer_Pickup_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Pickup_Order' ] = include_once( 'include/emails/pickup-order.php' );
			}
		}
	
	    return $emails;		
	}
	
	/**
	 * Send email when order status change to "pickuped"
	 *
	*/
	public function email_trigger_ready_pickup($order_id, $order = false){
		$ready_for_pickup = get_option( "wclp_status_ready_pickup", 0);
		if($ready_for_pickup == true){
			WC()->mailer()->emails['WC_Email_Customer_Ready_Pickup_Order']->trigger( $order_id, $order );
		}
	}
	
	/**
	 * Send email when order status change to "pickuped"
	 *
	*/
	public function email_trigger_pickup($order_id, $order = false){		
		$picked = get_option( "wclp_status_picked_up", 0);
		if($picked == true){
			WC()->mailer()->emails['WC_Email_Customer_Pickup_Order']->trigger( $order_id, $order );
		}
	}
	
	/*
	* Plugin uninstall code 
	*/	
	public function uninstall_notice(){
		$screen = get_current_screen();
		
		if($screen->parent_file != 'plugins.php') return;
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';	
		wp_enqueue_style( 'alp-admin-js',  plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), $this->version );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_enqueue_script( 'jquery-blockui' );
		
		$ready_pickup_count = wc_orders_count( 'ready-pickup' );
		$pickup_count = wc_orders_count( 'pickup' );
		
		$order_statuses = wc_get_order_statuses();
		unset($order_statuses['wc-ready-pickup']);				
		unset($order_statuses['wc-pickup']);
		
		if($ready_pickup_count > 0 || $pickup_count > 0){ ?>
			<script>
				jQuery(document).on("click","[data-slug='advanced-local-pickup-for-woocommerce'] .deactivate a",function(e){			
					e.preventDefault();
					jQuery('.alp_uninstall_popup').show();
					var theHREF = jQuery(this).attr("href");
					jQuery(document).on("click",".alp_uninstall_plugin",function(e){
						jQuery("body").block({
							message: null,
							overlayCSS: {
								background: "#fff",
								opacity: .6
							}	
						});	
						var form = jQuery('#wplp_order_reassign_form');
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
				jQuery(document).on("click",".alp_popupclose",function(e){
					jQuery('.alp_uninstall_popup').hide();
				});
				jQuery(document).on("click",".alp_uninstall_close",function(e){
					jQuery('.alp_uninstall_popup').hide();
				});
			</script>
			<div id="" class="alp_popupwrapper alp_uninstall_popup" style="display:none;">
				<div class="alp_popuprow" style="text-align: left;max-width: 380px;">
					<h3 class="alp_popup_title">Advanced Local Pickup for WooCommerce</h2>
					<form method="post" id="wplp_order_reassign_form">					
					<?php if( $ready_pickup_count > 0 ){ ?>
						
						<p><?php echo sprintf(__('We detected %s orders that use the Ready for pickup order status, You can reassign these orders to a different status', 'advanced-local-pickup-for-woocommerce'), $ready_pickup_count); ?></p>
						
						<select id="reassign_ready_pickup_order" name="reassign_ready_pickup_order" class="reassign_select">
							<option value=""><?php _e('Select', 'woocommerce'); ?></option>
							<?php foreach($order_statuses as $key => $status){ ?>
								<option value="<?php echo $key; ?>"><?php echo $status; ?></option>
							<?php } ?>
						</select>
					
					<?php } ?>
					<?php if( $pickup_count > 0 ){ ?>
						
						<p><?php echo sprintf(__('We detected %s orders that use the Picked up order status, You can reassign these orders to a different status', 'advanced-local-pickup-for-woocommerce'), $pickup_count); ?></p>					
						
						<select id="reassign_pickedup_order" name="reassign_pickedup_order" class="reassign_select">
							<option value=""><?php _e('Select', 'woocommerce'); ?></option>
							<?php foreach($order_statuses as $key => $status){ ?>
								<option value="<?php echo $key; ?>"><?php echo $status; ?></option>
							<?php } ?>
						</select>
					
					<?php } ?>				
					<!--p><?php echo sprintf(__('<strong>Note:</strong> - If you use the custom order status, when you deactivate the plugin, you must register the order status, otherwise these orders will not display on your orders admin. You can find more information and the code <a href="%s" target="blank">snippet</a> to use in functions.php here.', 'advanced-local-pickup-for-woocommerce'), 'https://www.zorem.com/docs/advanced-local-pickup-for-woocommerce/plugin-settings/#code-snippets'); ?></p-->
					<p class="" style="text-align:left;">
						<input type="hidden" name="action" value="reassign_order_status">
						<input type="button" value="Uninstall" class="alp_uninstall_plugin button-primary btn_green">
						<input type="button" value="Close" class="alp_uninstall_close button-primary btn_red">				
					</p>
				</form>	
				</div>
				<div class="alp_popupclose"></div>
			</div>		
		<?php } 
	}
	
	function reassign_order_status(){
		$reassign_ready_pickup_order = $_POST['reassign_ready_pickup_order'];
		$reassign_pickedup_order = $_POST['reassign_pickedup_order'];
		
		if($reassign_ready_pickup_order != ''){
			
			$args = array(
				'status' => 'ready-pickup',
				'limit' => '-1',
			);
			
			$orders = wc_get_orders( $args );
			
			foreach($orders as $order){				
				$order_id = $order->get_id();
				$order = new WC_Order($order_id);
				$order->update_status($reassign_ready_pickup_order);				
			}			
		}
		
		if($reassign_pickedup_order != ''){
			
			$args = array(
				'status' => 'pickup',
				'limit' => '-1',
			);
			
			$ps_orders = wc_get_orders( $args );
			
			foreach($ps_orders as $order){				
				$order_id = $order->get_id();
				$order = new WC_Order($order_id);
				$order->update_status($reassign_pickedup_order);				
			}			
		}
		exit;
		echo 1;die();		
	}
}

/**
 * Returns an instance of Woocommerce_local_pickup.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return Woocommerce_local_pickup
*/
function wc_local_pickup() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new Woocommerce_local_pickup();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_local_pickup();