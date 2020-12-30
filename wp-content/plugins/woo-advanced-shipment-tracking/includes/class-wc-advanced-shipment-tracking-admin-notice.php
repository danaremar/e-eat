<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin_notice {

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
		$this->init();	
    }
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Admin_notice
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
		//add_action( 'ast_settings_admin_notice', array( $this, 'ast_compatibility_with_shipstation_notice' ) );	
		//add_action('admin_init', array( $this, 'ast_compatibility_with_shipstation_notice_ignore' ) );			
		//
		//add_action( 'ast_settings_admin_notice', array( $this, 'ast_compatibility_with_wc_services_notice' ) );	
		//add_action('admin_init', array( $this, 'ast_compatibility_with_wc_services_notice_ignore' ) );
		//
		//add_action( 'ast_settings_admin_notice', array( $this, 'trackship_admin_notice' ) );	
		//add_action('admin_init', array( $this, 'trackship_admin_notice_ignore' ) );	
	}		
	
	/*
	* Display admin notice on plugin install or update
	*/
	public function trackship_admin_notice(){ 		
		
		$wc_ast_api_key = get_option('wc_ast_api_key');
		if($wc_ast_api_key)return;			
		
		if ( get_option('trackship_admin_notice_ignore') ) return;
		
		$dismissable_url = esc_url(  add_query_arg( 'trackship-ignore-notice', 'true' ) );
		?>		
		<style>		
		.wp-core-ui .notice.trakcship-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #59c889;
		}
		.wp-core-ui .notice.trakcship-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.trakcship-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.ts_notice_btn {
			background: transparent;
			color: #3c4858;
			border-color: #59c889;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>	
		<div class="notice updated notice-success trakcship-dismissable-notice">			
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>			
			<h3>Automate your post-shipping workflow!</h3>
			<p>Get ready to for the Shopping Season, TrackShip fully integrates into your store admin, auto-tracks your orders, automates your fulfillment workflow and allows you to provide Superior Post-Purchase experience to your customers!</p>
			<a class="button-primary ts_notice_btn" target="blank" href="https://trackship.info/my-account/?register=1">Start Your Free Trial</a>
			<a class="button-primary ts_notice_btn" href="<?php echo $dismissable_url; ?>">No Thanks</a>				
		</div>
	<?php 		
	}	

	public function trackship_admin_notice_ignore(){
		if (isset($_GET['trackship-ignore-notice'])) {
			update_option( 'trackship_admin_notice_ignore', 'true' );
		}
	}		
	
	/*
	* Display admin notice if WooCommerce Shipstation plugin is active
	*/
	public function ast_compatibility_with_shipstation_notice(){
		
		if ( !is_plugin_active( 'woocommerce-shipstation-integration/woocommerce-shipstation.php' ) )return;
		if ( get_option('ast_compatibility_with_shipstation_notice_ignore') ) return;
		
		$dismissable_url = esc_url(  add_query_arg( 'ast-shipstation-ignore-notice', 'true' ) );
		?>		
		<style>		
		.wp-core-ui .notice.ast-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #005B9A;
		}
		.wp-core-ui .notice.ast-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.ast-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.btn_review_notice {
			background: transparent;
			color: #005b9a;
			border-color: #74c2e1;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>	
		<div class="notice updated notice-success ast-dismissable-notice">
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h3>Auto-sync Tracking from ShipStation to AST</h3>	
			<p>We noticed that you use the ShipStation Integration plugin and the Advanced Shipment Tracking (AST) plugins. You can use the ShipStation tracking add-on for AST to auto-sync the tracking numbers created by ShipStation into the AST shipment tracking order meta!</p>			
			<a class="button-primary btn_review_notice" target="blank" href="https://www.zorem.com/product/shipstation-tracking-add-on/">Get this add-on</a>	
			<a class="button-primary btn_review_notice" href="<?php echo $dismissable_url; ?>">No Thanks</a>	
		</div>
	<?php 	
	}
	
	/*
	* Display admin notice if WooCommerce services plugin is active
	*/
	public function ast_compatibility_with_wc_services_notice(){
		
		if ( !is_plugin_active( 'woocommerce-services/woocommerce-services.php' ) )return;
		if ( get_option('ast_compatibility_with_wc_services_notice_ignore') ) return;
		
		$dismissable_url = esc_url(  add_query_arg( 'ast-wc-services-ignore-notice', 'true' ) );
		?>		
		<style>		
		.wp-core-ui .notice.ast-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #005B9A;
		}
		.wp-core-ui .notice.ast-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.ast-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.btn_review_notice {
			background: transparent;
			color: #005b9a;
			border-color: #74c2e1;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>	
		<div class="notice updated notice-success ast-dismissable-notice">
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h3>Auto-sync Tracking from WooCommerce Shipping to AST</h3>	
			<p>We noticed that you use the WooCommerce Shipping & Tax plugin and the Advanced Shipment Tracking (AST) plugins. You can use the WC Shipping Tracking add-on for AST to auto-sync the tracking numbers created by WC Shipping into the AST shipment tracking order meta!</p>
			<a class="button-primary btn_review_notice" target="blank" href="https://www.zorem.com/product/wc-shipping-tracking-add-on/">Get this add-on</a>
			<a class="button-primary btn_review_notice" href="<?php echo $dismissable_url; ?>">No Thanks</a>	
		</div>
	<?php 	
	}

	public function ast_compatibility_with_shipstation_notice_ignore(){
		if (isset($_GET['ast-shipstation-ignore-notice'])) {
			update_option( 'ast_compatibility_with_shipstation_notice_ignore', 'true' );
		}
	}	
	
	public function ast_compatibility_with_wc_services_notice_ignore(){
		if (isset($_GET['ast-wc-services-ignore-notice'])) {
			update_option( 'ast_compatibility_with_wc_services_notice_ignore', 'true' );
		}
	}	
}