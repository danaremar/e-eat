<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin {
	
	var $item_code = 'ast_per_product';
	var $store_url = 'https://www.zorem.com/';
	var $license_status;
	var $license_key;
	var $license_email;
	var $zorem_pluginlist;
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {
		
		$this->license_status = 'ast_product_license_status';		
		$this->license_key = 'ast_product_license_key';
		$this->license_email = 'ast_product_license_email';								
		
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
	 * @return WC_Advanced_Shipment_Tracking_Admin
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
		//cron_schedules
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval') );							
		
		// add bulk order tracking number filter for exported / non-exported orders			
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'filter_orders_by_tracking_number_query' ) );			
		
		// add bulk order filter for exported / non-exported orders
		add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipping_provider') , 20 );	
		add_filter( 'request', array( $this, 'filter_orders_by_shipping_provider_query' ) );					
		
		add_filter( 'woocommerce_email_title', array( $this, 'change_completed_woocommerce_email_title'), 10, 2 );
		
		
		add_action( 'wp_ajax_wc_ast_upload_csv_form_update', array( $this, 'upload_tracking_csv_fun') );
		add_action( 'wp_ajax_wc_ast_upload_csv_form_action', array( $this, 'wc_ast_upload_csv_form_action') );

		add_action( 'wp_ajax_update_delivered_order_email_status', array( $this, 'update_delivered_order_email_status_fun') );

		add_action( 'admin_footer', array( $this, 'footer_function'),1 );									
		
		add_action( 'wp_ajax_update_email_preview_order', array( $this, 'update_email_preview_order_fun') );
		
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_delivered_order_status_actions_button'), 100, 2 );		
		add_filter( 'woocommerce_admin_order_preview_actions', array( $this, 'additional_admin_order_preview_buttons_actions'), 5, 2 );
		
		//Shipping Provider Action
		add_action( 'wp_ajax_filter_shipiing_provider_by_status', array( $this, 'filter_shipiing_provider_by_status_fun') );				

		add_action( 'wp_ajax_add_custom_shipment_provider', array( $this, 'add_custom_shipment_provider_fun') );
		
		add_action( 'wp_ajax_get_provider_details', array( $this, 'get_provider_details_fun') );
		
		add_action( 'wp_ajax_update_custom_shipment_provider', array( $this, 'update_custom_shipment_provider_fun') );
		
		add_action( 'wp_ajax_reset_default_provider', array( $this, 'reset_default_provider_fun') );
		
		add_action( 'wp_ajax_woocommerce_shipping_provider_delete', array( $this, 'woocommerce_shipping_provider_delete' ) );				
		
		add_action( 'wp_ajax_update_provider_status_active', array( $this, 'update_provider_status_active_fun') );
		
		add_action( 'wp_ajax_update_provider_status_inactive', array( $this, 'update_provider_status_inactive_fun') );
		
		add_action( 'wp_ajax_reset_shipping_providers_database', array( $this, 'reset_shipping_providers_database_fun') );
		
		add_action( 'wp_ajax_update_default_provider', array( $this, 'update_default_provider_fun') );
		
		add_action( 'wp_ajax_update_shipment_status', array( $this, 'update_shipment_status_fun') );
		
		add_action( 'wp_ajax_update_custom_order_status_email_display', array( $this, 'update_custom_order_status_email_display_fun') );		
	}					
	
	/*
	* add_cron_interval
	*/
	function add_cron_interval( $schedules ){
		$schedules['wc_ast_1hr'] = array(
			'interval' => 60*60,//1 hour
			'display'  => esc_html__( 'Every one hour' ),
		);
		$schedules['wc_ast_6hr'] = array(
			'interval' => 60*60*6,//6 hour
			'display'  => esc_html__( 'Every six hour' ),
		);
		$schedules['wc_ast_12hr'] = array(
			'interval' => 60*60*12,//6 hour
			'display'  => esc_html__( 'Every twelve hour' ),
		);
		$schedules['wc_ast_1day'] = array(
			'interval' => 60*60*24*1,//1 days
			'display'  => esc_html__( 'Every one day' ),
		);
		$schedules['wc_ast_2day'] = array(
			'interval' => 60*60*24*2,//2 days
			'display'  => esc_html__( 'Every two day' ),
		);
		$schedules['wc_ast_7day'] = array(
			'interval' => 60*60*24*7,//7 days
			'display'  => esc_html__( 'Every Seven day' ),
		);
		
		//every 5 sec for batch proccessing
		$schedules['wc_ast_2min'] = array(
			'interval' => 2*60,//1 hour
			'display'  => esc_html__( 'Every two min' ),
		);
		return $schedules;
	}
	
	/*
	* get shipped orders
	*/
	function get_shipped_orders() {
		$range = get_option('wc_ast_api_date_range', 30 );
		$args = array(
			'status'	=> 'wc-completed',
			'limit'		=> -1,
		);
		if( $range != 0 ){
			$start = strtotime( date( 'Y-m-d 00:00:00', strtotime( '-'.$range.' days' ) ));
			$end = strtotime( date( 'Y-m-d 23:59:59', strtotime( '-1 days' ) ));
			$args['date_completed'] = $start.'...'.$end;
		}
		
		return $orders = wc_get_orders( $args );
	}	
	
	/**
	* Load admin styles.
	*/
	public function admin_styles($hook) {						
		
		if(!isset($_GET['page'])) {
			return;
		}
		if( $_GET['page'] != 'woocommerce-advanced-shipment-tracking') {
			return;
		}
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';				

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		
		wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_enqueue_style( 'front_style',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/front.css', array(), wc_advanced_shipment_tracking()->version );	
		
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);				
		
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		
		wp_enqueue_script( 'selectWoo');
		wp_enqueue_script( 'wc-enhanced-select');
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style( 'wp-color-picker' );
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		
		
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );		
		wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');		
		wp_enqueue_style('thickbox');		
		
		//wp_enqueue_style( 'material-css',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/material.css', array(), wc_advanced_shipment_tracking()->version );		
		//wp_enqueue_script( 'material-js', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/material.min.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );						
		
		wp_enqueue_script( 'ajax-queue', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/jquery.ajax.queue.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);
				
		wp_enqueue_script( 'advanced_shipment_tracking_settings', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/settings.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );

		wp_enqueue_script( 'advanced_shipment_tracking_datatable', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/datatable.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );

		wp_enqueue_script( 'advanced_shipment_tracking_datatable_jquery', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/datatable.jquery.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );		
		
		wp_enqueue_script( 'front-js', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/front.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
		
		wp_register_script( 'shipment_tracking_table_rows', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/shipping_row.js' , array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking()->version );
		wp_localize_script( 'shipment_tracking_table_rows', 'shipment_tracking_table_rows', array(
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),
				'delete_provider' => __( 'Really delete this entry? This will not be undo.', 'woo-advanced-shipment-tracking' ),
				'upload_only_csv_file' => __( 'You can upload only csv file.', 'woo-advanced-shipment-tracking' ),
				'browser_not_html' => __( 'This browser does not support HTML5.', 'woo-advanced-shipment-tracking' ),
				'upload_valid_csv_file' => __( 'Please upload a valid CSV file.', 'woo-advanced-shipment-tracking' ),
			),
			'delete_rates_nonce' => wp_create_nonce( "delete-rate" ),
		) );
		wp_enqueue_media();	
	}
	
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Shipment Tracking', __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ), 'manage_woocommerce', 'woocommerce-advanced-shipment-tracking', array( $this, 'woocommerce_advanced_shipment_tracking_page_callback' ) ); 
	}
	
	/*
	* Sort by Country ascending
	*/
	public function sortByCountryAsc($a, $b) {
		return strcmp($a->country, $b->country);
	}
	
	/*
	* Sort by Country descending
	*/
	public function sortByCountryDesc($a, $b) {
		return strcmp($b->country, $a->country);
	}
	
	/*
	* callback for Shipment Tracking page
	*/
	public function woocommerce_advanced_shipment_tracking_page_callback(){		  
		global $order;
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();
		
		global $wpdb;
		$woo_shippment_table_name = $this->table;		
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $woo_shippment_table_name WHERE display_in_order = 1" );		
		
		foreach($default_shippment_providers as $key => $value){			
			$search  = array('(US)', '(UK)');
			$replace = array('', '');
			if($value->shipping_country && $value->shipping_country != 'Global'){
				$country = str_replace($search, $replace, $WC_Countries->countries[$value->shipping_country]);
				$default_shippment_providers[$key]->country = $country;			
			} elseif($value->shipping_country && $value->shipping_country == 'Global'){
				$default_shippment_providers[$key]->country = 'Global';
			}
		}	
		$checked = '';	
		if(isset($_GET['tab'])){
			if($_GET['tab'] == 'settings'){
					
			}
		}		
		
		wp_enqueue_script( 'shipment_tracking_table_rows' );	
		$wc_ast_api_key = get_option('wc_ast_api_key');		
		 ?>		
			<div class="zorem-layout">
				<div class="zorem-layout__header">
					<h1 class="zorem-layout__header-breadcrumbs"><span><a href="<?php echo esc_url( admin_url( '/admin.php?page=wc-admin' ) ); ?>"><?php _e('WooCommerce', 'woocommerce'); ?></a></span><span><a href="<?php echo esc_url( admin_url( '/admin.php?page=woocommerce-advanced-shipment-tracking' ) ); ?>"><?php _e('Shipment Tracking', 'woo-advanced-shipment-tracking'); ?></a></span><span class="header-breadcrumbs-last"><?php _e('Settings', 'woocommerce'); ?></span></h1>
					<div class="zorem-layout__logo-panel">
						<img class="header-plugin-logo" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ast-logo.png">
						<div class="ast_menu ast_dropdown">
							<span class="dashicons dashicons-ellipsis ast-dropdown-menu"></span>
							<ul class="ast-dropdown-content">
								<li><a href="javaScript:void(0);" data-label="<?php _e('Settings', 'woocommerce'); ?>" data-tab="settings" data-section="content2"><?php _e('Settings', 'woocommerce'); ?></a></li>
								<li><a href="javaScript:void(0);" data-label="<?php _e('Shipping Providers', 'woo-advanced-shipment-tracking'); ?>" data-tab="shipping-providers" data-section="content1"><?php _e('Shipping Providers', 'woo-advanced-shipment-tracking'); ?></a></li>
								<li><a href="javaScript:void(0);" data-label="<?php _e('CSV Import', 'woo-advanced-shipment-tracking'); ?>" data-tab="bulk-upload" data-section="content4"><?php _e('CSV Import', 'woo-advanced-shipment-tracking'); ?></a></li>
								<?php if($wc_ast_api_key){ ?>
								<li><a href="<?php echo esc_url( admin_url( '/admin.php?page=trackship-for-woocommerce' ) ); ?>" data-label="<?php _e('CSV Import', 'woo-advanced-shipment-tracking'); ?>">TrackShip</a></li>
								<?php } ?>
								<li><a href="javaScript:void(0);" data-label="<?php _e('Add-ons', 'woo-advanced-shipment-tracking'); ?>" data-tab="addons" data-section="content6"><?php _e('Add-ons', 'woo-advanced-shipment-tracking'); ?></a></li>
							</ul>	
						</div>					
					</div>
				</div>
			<?php do_action('ast_settings_admin_notice');?>		
            <div class="woocommerce zorem_admin_layout">
                <div class="ast_admin_content" >
					<div class="ast_nav_div">
						<!--input id="tab2" type="radio" name="tabs" class="tab_input" data-tab="settings" data-label="<?php _e('Settings', 'woocommerce'); ?>" checked>
						<label for="tab2" class="tab_label first_label"><?php _e('Settings', 'woocommerce'); ?></label>
						
						<input id="tab1" type="radio" name="tabs" class="tab_input" data-tab="shipping-providers" data-label="<?php _e('Shipping Providers', 'woocommerce'); ?>" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'shipping-providers'){ echo 'checked'; } ?>>
						<label for="tab1" class="tab_label"><?php _e('Shipping Providers', 'woo-advanced-shipment-tracking'); ?></label>                                        
						<input id="tab4" type="radio" name="tabs" class="tab_input" data-tab="bulk-upload" data-label="<?php _e('CSV Import', 'woocommerce'); ?>" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'bulk-upload'){ echo 'checked'; } ?>>
						<label for="tab4" class="tab_label"><?php _e('CSV Import', 'woo-advanced-shipment-tracking'); ?></label>																		
						
						<?php if($wc_ast_api_key){ ?>
						<a class="menu_trackship_link" href="<?php echo esc_url( admin_url( '/admin.php?page=trackship-for-woocommerce' ) ); ?>">TrackShip</a>
						<?php } ?>						
						
						<input id="tab6" type="radio" name="tabs" class="tab_input" data-tab="addons" data-label="<?php _e('Add-ons', 'woocommerce'); ?>" <?php if(isset($_GET['tab']) && ($_GET['tab'] == 'addons' || $_GET['tab'] == 'license')){ echo 'checked'; } ?>>
						<label for="tab6" class="tab_label"><?php _e('Add-ons', 'woo-advanced-shipment-tracking'); ?></label-->
						
						<?php $this->get_html_menu_tab( $this->get_ast_tab_settings_data()); ?>
						
						<div class="nav_doc_section">					
							<a target="blank" class="doc_link" href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/"><?php _e( 'Documentation', 'woo-advanced-shipment-tracking' ); ?></a>
							<a href="JavaScript:void(0);" class="open_video_popup"><?php _e( 'How to Video', 'woo-advanced-shipment-tracking' ); ?></a>
						</div>					
						<?php 
							require_once( 'views/admin_options_shipping_provider.php' );
							require_once( 'views/admin_options_settings.php' );
							require_once( 'views/admin_options_bulk_upload.php' );		
							require_once( 'views/admin_options_trackship_integration.php' );
							do_action('ast_paypal_settings_panel');
			   				require_once( 'views/admin_options_addons.php' ); ?>	
					</div>                   					
                </div>				
            </div>            			
			
			<div id="ast_settings_snackbar" class="ast_snackbar"><?php _e( 'Data saved successfully.', 'woo-advanced-shipment-tracking' )?></div>
			
			<div id="" class="popupwrapper how_to_video_popup" style="display:none;">
				<div class="popuprow">
					<div class="videoWrapper">
					<iframe id="how_to_video" src="https://www.youtube.com/embed/QOVbwfgXQdU" frameborder="0"  allowfullscreen></iframe>
					</div>
				</div>
				<div class="popupclose"></div>
			</div>
			
			<div id="" class="popupwrapper ts_video_popup" style="display:none;">
				<div class="popuprow">
					<div class="videoWrapper">
					<iframe id="ts_video" src="https://www.youtube.com/embed/PhnqDorKN_c" frameborder="0"  allowfullscreen></iframe>
					</div>
				</div>
				<div class="popupclose"></div>
			</div>
			<div id="" class="popupwrapper import_tracking_video_popup" style="display:none;">
				<div class="popuprow">
					<div class="videoWrapper">
					<iframe id="import_tracking_video" src="https://www.youtube.com/embed/aX6fud-W7pc" frameborder="0"  allowfullscreen></iframe>
					</div>
				</div>
				<div class="popupclose"></div>
			</div>	
		</div >		
	<?php
		if(isset( $_GET['tab'] ) && $_GET['tab'] == 'trackship'){ ?>
			<script>
			jQuery("#tab3").trigger('click');
			</script>
		<?php }	
	}
	
	public function get_ast_tab_settings_data(){	
			
		$setting_data = array(
			'tab2' => array(					
				'title'		=> __( 'Settings', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label first_label',
				'data-tab'  => 'settings',
				'data-label' => __( 'Settings', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 1,	
			),
			'tab1' => array(					
				'title'		=> __( 'Shipping Providers', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'shipping-providers',
				'data-label' => __( 'Shipping Providers', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 2,
			),			
			'tab4' => array(					
				'title'		=> __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'bulk-upload',
				'data-label' => __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 3,
			),
			'trackship' => array(					
				'title'		=> 'TrackShip',
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'trackship',
				'data-label' => 'TrackShip',
				'name'  => 'tabs',
				'position'  => 4,
			),
			'tab6' => array(					
				'title'		=> __( 'Add-ons', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'addons',
				'data-label' => __( 'Add-ons', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 6,
			),	
		);
		$setting_data = apply_filters( 'ast_menu_tab_options', $setting_data );
		return $setting_data;
	}
	
	public function get_html_menu_tab( $arrays ){ 
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'settings';
		if( $tab == 'license')$tab = 'addons';
		foreach( (array)$arrays as $id => $array ){
			if($id == 'trackship'){
				$wc_ast_api_key = get_option('wc_ast_api_key');		
				if($wc_ast_api_key){ ?>
					<a class="menu_trackship_link" href="<?php echo esc_url( admin_url( '/admin.php?page=trackship-for-woocommerce' ) ); ?>">TrackShip</a>
				<?php }
			} else{ ?>
			<input class="tab_input" id="<?php echo $id?>" name="<?php echo $array['name']; ?>" type="radio"  data-tab="<?php echo $array['data-tab']; ?>" data-label="<?php echo $array['data-label']; ?>" <?php if($tab == $array['data-tab']){ echo 'checked'; } ?> />
			<label class="<?php echo $array['class']; ?>" for="<?php echo $id?>"><?php echo $array['title']; ?></label>
			<?php } }
	}	
	/*
	* get html of fields
	*/
	public function get_html( $arrays ){
		
		$checked = '';
		?>
		<table class="form-table settings-form-table">
			<tbody>
            	<?php foreach( (array)$arrays as $id => $array ){
				
					if($array['show']){
					?>
                	<?php if($array['type'] == 'title'){ ?>
                		<tr valign="top titlerow">
                        	<th colspan="2"><h3><?php echo $array['title']?></h3></th>
                        </tr>    	
                    <?php continue;} ?>					
				<tr valign="top" class="<?php echo $array['class']; ?>">
					<?php if($array['type'] != 'desc'){ ?>										
					<th scope="row" class="titledesc"  >
						<label for=""><?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
							<?php if( isset($array['tooltip']) ){?>
                            	<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
                            <?php } 
							if(isset($array['desc']) && $array['desc'] != ''){ ?>
								<p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
							<?php } ?>	
                        </label>
					</th>
					<?php } ?>
					<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
                    	<?php if( $array['type'] == 'checkbox' ){								
							if($id === 'wcast_enable_delivered_email'){
								$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings');
								
								if($wcast_enable_delivered_email['enabled'] == 'yes' || $wcast_enable_delivered_email['enabled'] == 1){
									$checked = 'checked';
								} else{
									$checked = '';									
								}
								
							} elseif($id === 'wcast_enable_partial_shipped_email'){
								$wcast_enable_partial_shipped_email = get_option('woocommerce_customer_partial_shipped_order_settings');

								if($wcast_enable_partial_shipped_email['enabled'] == 'yes' || $wcast_enable_partial_shipped_email['enabled'] == 1){
									$checked = 'checked';
								} else{
									$checked = '';									
								}								
							} else{																		
								if(get_option($id)){
									$checked = 'checked';
								} else{
									$checked = '';
								} 
							} 
							
							if(isset($array['disabled']) && $array['disabled'] == true){
								$disabled = 'disabled';
								$checked = '';
							} else{
								$disabled = '';
							}							
							?>
							<input type="hidden" name="<?php echo $id?>" value="0"/>
							<input class="tgl tgl-flat" id="<?php echo $id?>" name="<?php echo $id?>" type="checkbox" <?php echo $checked ?> value="1"/>
							<label class="tgl-btn" for="<?php echo $id?>"></label>	
                        <?php } 
								elseif( isset( $array['type'] ) && $array['type'] == 'multiple_checkbox' ){ ?>
									<?php
									foreach((array)$array['options'] as $key => $val ){
										$multi_checkbox_data = get_option($id);
										if(isset($multi_checkbox_data[$key]) && $multi_checkbox_data[$key] == 1){
											$checked="checked";
										} else{
											$checked="";
										} ?>
									<span class=" multiple_checkbox <?php if(isset($val['class'])){ echo $val['class']; } ?>">
										<label class="" for="<?php echo $key?>">
											<input type="hidden" name="<?php echo $id?>[<?php echo $key?>]" value="0"/>
											<input type="checkbox" name="<?php echo $id?>[<?php echo $key?>]" class=""  <?php echo $checked; ?> value="1"/>											
											<span class="multiple_label"><?php echo $val['status']; ?></span>											
										</label>																		
									</span>												
									<?php } 
								}
								elseif( isset( $array['type'] ) && $array['type'] == 'inline_checkbox' ){
									
									$checkbox_array = $array['checkbox_array'];
									foreach($checkbox_array as $c_name => $c_label){
									
										if(get_option($c_name)){ 
											$checked = 'checked'; 
										} else{ 
											$checked = ''; 
										} ?>	
									<p class="inline_checkbox">
										<input type="hidden" name="<?php echo $c_name?>" value="0"/>
										<input id="<?php echo $c_name?>" name="<?php echo $c_name?>" type="checkbox" <?php echo $checked ?> value="1"/>
										<label for="<?php echo $c_name?>"><?php echo $c_label?></label>	
									</p>	
								<?php } }
								elseif( isset( $array['type'] ) && $array['type'] == 'dropdown' ){
								
								if( isset($array['multiple']) ){
									$multiple = 'multiple';
									$field_id = $array['multiple'];
								} else {
									$multiple = '';
									$field_id = $id;
								}
							?>
                        	<fieldset>
								<select class="select select2" id="<?php echo $field_id?>" name="<?php echo $id?>" <?php echo $multiple;?>>    <?php foreach((array)$array['options'] as $key => $val ){?>
                                    	<?php $selected = '';
											if( isset($array['multiple']) ){
												if (in_array($key, (array)$this->data->$field_id ))$selected = 'selected';
											} else {
												if( get_option($id) == (string)$key )$selected = 'selected';
											} ?>
										<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
                                    <?php } ?>
								</select>
							</fieldset>
                        <?php } elseif( isset( $array['type'] ) && $array['type'] == 'radio' ){ ?>                        	
                        	<fieldset>
								<?php foreach((array)$array['options'] as $key => $val ){
									$selected = '';									
									if( get_option($id,$array['default']) == (string)$key )$selected = 'checked'; ?>
									<span class="radio_section multiple_checkbox">
										<label class="" for="<?php echo $id?>_<?php echo $key?>">												
											<input type="radio" id="<?php echo $id?>_<?php echo $key?>" name="<?php echo $id?>" class="<?php echo $id?>"  value="<?php echo $key?>" <?php echo $selected?>/>
											<span class=""><?php echo $val; ?></span>	
											</br>
										</label>																		
									</span>	
                                <?php } ?>								
							</fieldset>
                        <?php } elseif( $array['type'] == 'key_field' ){ ?>
							<fieldset>                                
								<?php if($array['connected'] == true){ ?>
									<a href="https://my.trackship.info/" target="_blank">
										<span class="api_connected"><label><?php _e( 'Connected', 'woo-advanced-shipment-tracking' ); ?></label><span class="dashicons dashicons-yes"></span></span></a>
								<?php } ?>								
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'label' ){ ?>
							<fieldset>
                               <label><?php echo $array['value']; ?></label>
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'tooltip_button' ){ ?>
							<fieldset>
								<a href="<?php echo $array['link']; ?>" class="button-primary" target="<?php echo $array['target'];?>"><?php echo $array['link_label'];?></a>
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'button' ){ ?>
							<fieldset>
								<button class="button-primary btn_green2 <?php echo $array['button_class'];?>" <?php if($array['disable']  == 1){ echo 'disabled'; }?>><?php echo $array['label'];?></button>
							</fieldset>
						<?php }
						else { ?>
                                                    
                        	<fieldset>
                                <input class="input-text regular-input " type="text" name="<?php echo $id?>" id="<?php echo $id?>" style="" value="<?php echo get_option($id)?>" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>">
                            </fieldset>
                        <?php } ?>
                        
					</td>
				</tr>						
	<?php } } ?>
			</tbody>
		</table>
	<?php }

	/*
	* get html of fields
	*/
	public function get_html_ul( $arrays ){ ?>
		<ul class="settings_ul">
		<?php foreach( (array)$arrays as $id => $array ){
			if($array['show']){ 
				if( $array['type'] == 'checkbox' ){
					if(get_option($id)){
						$checked = 'checked';
					} else{
						$checked = '';
					}
				?>
					<li>
						<input type="hidden" name="<?php echo $id?>" value="0"/>
						<input class="tgl tgl-flat" id="<?php echo $id?>" name="<?php echo $id?>" type="checkbox" <?php echo $checked ?> value="1"/>
						<label class="tgl-btn" for="<?php echo $id?>"></label>						
						
						<label class="setting_ul_checkbox_label"><?php echo $array['title']?>
						<?php if( isset($array['tooltip']) ){?>
							<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
						<?php } ?>
						</label>						
					</li>	
				<?php } if( $array['type'] == 'radio' ){ ?>
					<li class="settings_radio_li">
						<label><strong><?php echo $array['title']?></strong></label>	
						<?php foreach((array)$array['options'] as $key => $val ){
							$selected = '';									
							if( get_option($id,$array['default']) == (string)$key )$selected = 'checked'; ?>
							<span class="radio_section">
								<label class="" for="<?php echo $id?>_<?php echo $key?>">												
									<input type="radio" id="<?php echo $id?>_<?php echo $key?>" name="<?php echo $id?>" class="<?php echo $id?>"  value="<?php echo $key?>" <?php echo $selected?>/>
									<span class=""><?php echo $val; ?></span>	
									</br>
								</label>																		
							</span>
                        <?php } ?>
					</li>					
				<?php } if($array['type'] == 'multiple_checkbox'){ ?>
					<li>
						<div><label for=""><?php echo $array['title']?></label></div>
						<div class="multiple_checkbox_parent">
							<?php $op = 1;	
							foreach((array)$array['options'] as $key => $val ){
								if($val['type'] == 'default'){											
									$multi_checkbox_data = get_option($id);
									if(isset($multi_checkbox_data[$key]) && $multi_checkbox_data[$key] == 1){
										$checked="checked";
									} else{
										$checked="";
									}?>
							<span class="multiple_checkbox">
								<label class="" for="">
									<input type="hidden" name="<?php echo $id?>[<?php echo $key?>]" value="0"/>
									<input type="checkbox" id="<?php echo $key?>" name="<?php echo $id?>[<?php echo $key?>]" class=""  <?php echo $checked; ?> value="1"/>											
									<span class="multiple_label"><?php echo $val['status']; ?></span>
									</br>
								</label>																		
							</span>												
							<?php } } ?>
						</div>
						<div class="multiple_checkbox_parent">
							<?php foreach((array)$array['options'] as $key => $val ){									
								if($val['type'] == 'custom'){
									$multi_checkbox_data = get_option($id);																			
									if(isset($multi_checkbox_data[$key]) && $multi_checkbox_data[$key] == 1){
										$checked="checked";
									} else{
										$checked="";
									}
							if($op == 1){ ?>
								<div style="margin-bottom: 5px;">
									<strong><?php _e( 'Custom Order Statuses', 'woo-advanced-shipment-tracking' ); ?></strong>
								</div>
							<?php } ?>
							<span class="multiple_checkbox">
								<label class="" for="">	
									<input type="hidden" name="<?php echo $id?>[<?php echo $key?>]" value="0"/>
									<input type="checkbox" id="<?php echo $key?>" name="<?php echo $id?>[<?php echo $key?>]" class=""  <?php echo $checked; ?> value="1"/>
									<span class="multiple_label"><?php echo $val['status']; ?></span>
									</br>
								</label>																		
							</span>
							<?php $op++; } } ?>
						</div>	
					</li>	
				<?php } 
			}
		} ?>                
		</ul>	
	<?php }	
	
	/*
	* get html of fields
	*/
	public function get_html_2( $arrays ){
		
		$checked = '';
		?>
		<table class="form-table table-layout-2">
			<tbody>
            	<?php foreach( (array)$arrays as $id => $array ){
				
				if($array['show']){ ?>                						
				<tr valign="top" class="<?php echo $array['class']; ?>">
				
					<th scope="row" class="titledesc"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
                    	<?php if( $array['type'] == 'checkbox' ){								
							if($id === 'wcast_enable_delivered_email'){
								$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings');
								
								if($wcast_enable_delivered_email['enabled'] == 'yes' || $wcast_enable_delivered_email['enabled'] == 1){
									$checked = 'checked';
								} else{
									$checked = '';									
								}
								
							} elseif($id === 'wcast_enable_partial_shipped_email'){
								$wcast_enable_partial_shipped_email = get_option('woocommerce_customer_partial_shipped_order_settings');

								if($wcast_enable_partial_shipped_email['enabled'] == 'yes' || $wcast_enable_partial_shipped_email['enabled'] == 1){
									$checked = 'checked';
								} else{
									$checked = '';									
								}								
							} else{																		
								if(get_option($id)){
									$checked = 'checked';
								} else{
									$checked = '';
								} 
							} 
							
							if(isset($array['disabled']) && $array['disabled'] == true){
								$disabled = 'disabled';
								$checked = '';
							} else{
								$disabled = '';
							}							
							?>
							<input type="hidden" name="<?php echo $id?>" value="0"/>
							<input class="tgl tgl-flat" id="<?php echo $id?>" name="<?php echo $id?>" type="checkbox" <?php echo $checked ?> <?php echo $disabled; ?> value="1"/>
							<label class="tgl-btn" for="<?php echo $id?>"></label>	
                        <?php } elseif( isset( $array['type'] ) && $array['type'] == 'dropdown' ){?>
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
								<select class="select select2" id="<?php echo $field_id?>" name="<?php echo $id?>" <?php echo $multiple;?>>    <?php foreach((array)$array['options'] as $key => $val ){?>
                                    	<?php
											$selected = '';
											if( isset($array['multiple']) ){
												if (in_array($key, (array)$this->data->$field_id ))$selected = 'selected';
											} else {
												if( get_option($id) == (string)$key )$selected = 'selected';
											}
                                        
										?>
										<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
                                    <?php } ?>
								</select>
							</fieldset>
                        <?php }						
						elseif( $array['type'] == 'label' ){ ?>
							<fieldset>
                               <label><?php echo $array['value']; ?></label>
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'tooltip_button' ){ ?>
							<fieldset>
								<a href="<?php echo $array['link']; ?>" class="button-primary" target="<?php echo $array['target'];?>"><?php echo $array['link_label'];?></a>
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'button' ){ ?>
							<fieldset>
								<button class="button-primary btn_green2 <?php echo $array['button_class'];?>" <?php if($array['disable']  == 1){ echo 'disabled'; }?>><?php echo $array['label'];?></button>
							</fieldset>
						<?php }
						else { ?>
                                                    
                        	<fieldset>
                                <input class="input-text regular-input " type="text" name="<?php echo $id?>" id="<?php echo $id?>" style="" value="<?php echo get_option($id)?>" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>">
                            </fieldset>
                        <?php } ?>
                        
					</th>
					<?php if($array['type'] != 'desc'){ ?>										
					<th class="forminp">
						<label for=""><span><?php echo $array['title']?><?php if( isset($array['tooltip']) ){?>
                            	<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
                            <?php } ?></span><span class="html2_title1"><?php echo $array['title1']?></span></label>						
					</th>
					<?php } ?>
				</tr>
				<?php if(isset($array['desc']) && $array['desc'] != ''){ ?>
					<tr class="<?php echo $array['class']; ?>"><td colspan="2" style=""><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p></td></tr>
				<?php } ?>				
	<?php } } ?>
			</tbody>
		</table>
	<?php 
	}	
	
	/*
	* return licence valid
	* return licence status
	* return licence key
	* return licence email
	*/
	public function licence_valid() {
		return get_option( $this->license_status, false);
	}
	public function get_license_status() {
		return get_option( $this->license_status, false);
	}
	public function get_license_key() {
		return get_option( $this->license_key, false);
	}
	public function get_license_email() {
		return get_option( $this->license_email, false);
	}						
	
	/*
	* get settings tab array data
	* return array
	*/
	function get_settings_data(){
		
		$wc_ast_status_shipped = get_option('wc_ast_status_shipped',0);
		if($wc_ast_status_shipped == 1){
			$completed_order_label = __( 'Shipped', 'woo-advanced-shipment-tracking' );	
			$mark_as_shipped_label = __( 'Default "mark as <span class="shipped_label">shipped</span>"', 'woo-advanced-shipment-tracking' );	
			$mark_as_shipped_tooltip = __( "This means that the 'mark as <span class='shipped_label'>shipped</span>' will be selected by default when adding tracking info to orders.", 'woo-advanced-shipment-tracking' );
		} else{
			$completed_order_label = __( 'Completed', 'woocommerce' );
			$mark_as_shipped_label = __( 'Default "mark as <span class="shipped_label">completed</span>"', 'woo-advanced-shipment-tracking' );
			$mark_as_shipped_tooltip = __( "This means that the 'mark as <span class='shipped_label'>completed</span>' will be selected by default when adding tracking info to orders.", 'woo-advanced-shipment-tracking' );	
		}
		
		$all_order_status = wc_get_order_statuses();
		
		$default_order_status = array(
			'wc-pending' => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold' => 'On hold',
			'wc-completed' => 'Completed',
			'wc-delivered' => 'Delivered',			
			'wc-cancelled' => 'Cancelled',
			'wc-refunded' => 'Refunded',
			'wc-failed' => 'Failed',
			'wc-ready-pickup' => 'Ready for Pickup',		
			'wc-pickup' => 'Picked up',	
			'wc-partial-shipped' => 'Partially Shipped',		
			'wc-updated-tracking' => 'Updated Tracking',				
		);
		foreach($default_order_status as $key=>$value){
			unset($all_order_status[$key]);
		}
		$custom_order_status = $all_order_status;
		foreach($custom_order_status as $key=>$value){
			unset($custom_order_status[$key]);			
			$key = str_replace("wc-", "", $key);		
			$custom_order_status[$key] = array(
				'status' => __( $value, '' ),
				'type' => 'custom',
			);
		}
		
		$order_status = array( 
			"processing" => array(
				'status' => __( 'Processing', 'woocommerce' ),
				'type' => 'default',
			),
			"completed" => array(
				'status' => $completed_order_label,
				'type' => 'default',
			),
			"partial-shipped" => array(
				'status' => __( 'Partially Shipped', '' ),
				'type' => 'default',
				'class' => 'partially_shipped_checkbox',
			),
			"updated-tracking" => array(
				'status' => __( 'Updated Tracking', '' ),
				'type' => 'default',
				'class' => 'updated_tracking_checkbox',
			),	
			"cancelled" => array(
				'status' => __( 'Cancelled', 'woocommerce' ),
				'type' => 'default',
			),
			"on-hold" => array(
				'status' => __( 'On Hold', 'woocommerce' ),
				'type' => 'default',
			),			
			"refunded" => array(
				'status' => __( 'Refunded', 'woocommerce' ),
				'type' => 'default',
			),
			
			"failed" => array(
				'status' => __( 'Failed', 'woocommerce' ),
				'type' => 'default',
			),
			"show_in_customer_invoice" => array(
				'status' => __( 'Customer Invoice', 'woocommerce' ),
				'type' => 'default',
			),
			"show_in_customer_note" => array(
				'status' => __( 'Customer note', 'woocommerce' ),
				'type' => 'default',
			),			
		);
		
		$actions_order_status = array( 
			"processing" => array(
				'status' => __( 'Processing', 'woocommerce' ),
				'type' => 'default',
			),
			"completed" => array(
				'status' => $completed_order_label,
				'type' => 'default',
			),
			"partial-shipped" => array(
				'status' => __( 'Partially Shipped', '' ),
				'type' => 'default',
				'class' => 'partially_shipped_checkbox',
			),
			"updated-tracking" => array(
				'status' => __( 'Updated Tracking', '' ),
				'type' => 'default',
				'class' => 'updated_tracking_checkbox',
			),	
			"on-hold" => array(
				'status' => __( 'On Hold', 'woocommerce' ),
				'type' => 'default',
			),
			"cancelled" => array(
				'status' => __( 'Cancelled', 'woocommerce' ),
				'type' => 'default',
			),		
			"refunded" => array(
				'status' => __( 'Refunded', 'woocommerce' ),
				'type' => 'default',
			),	
			"failed" => array(
				'status' => __( 'Failed', 'woocommerce' ),
				'type' => 'default',
			),					
		);
		
		$order_status_array = array_merge($order_status,$custom_order_status);	

		$action_order_status_array = array_merge($actions_order_status,$custom_order_status);							
		
		//$ast_add_tracking_options = array();
		//$ast_add_tracking_options = apply_filters( 'ast_add_tracking_options', $ast_add_tracking_options );
									
		$form_data = array(		
			'ast_completed_order_status' => array(
				'type'		=> 'inline_checkbox',
				'title'		=> __( 'General Settings', 'woo-advanced-shipment-tracking' ),
				'checkbox_array' => array( 
										"wc_ast_status_shipped" => __( 'Rename the “Completed” Order status label to “Shipped”', 'woo-advanced-shipment-tracking' ),
										"wc_ast_default_mark_shipped" => __( 'Set the "mark as shipped" option checked  when adding tracking info to orders', 'woo-advanced-shipment-tracking' )
									),
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_unclude_tracking_info' => array(
				'type'		=> 'multiple_checkbox',
				'title'		=> __( 'Order Email Display', 'woo-advanced-shipment-tracking' ),
				'desc'		=> __( 'Choose on which order emails to include the shipment tracking info', 'woo-advanced-shipment-tracking' ),
				'options'   => $order_status_array,					
				'show'		=> true,
				'class'     => '',
			),
			'wc_ast_show_orders_actions' => array(
				'type'		=> 'multiple_checkbox',
				'title'		=> __( 'Add Tracking action ', 'woo-advanced-shipment-tracking' ),
				'desc'		=> __( 'Choose for which Order status to display Add Tracking action button', 'woo-advanced-shipment-tracking' ),
				'options'   => $action_order_status_array,					
				'show'		=> true,
				'class'     => '',
			),	
			'tracking_display_my_account' => array(
				'type'		=> 'inline_checkbox',
				'title'		=> __( 'Tracking Display My Account', 'woo-advanced-shipment-tracking' ),
				'checkbox_array' => array( 
										"display_track_in_my_account" => __( 'Display Track button on the Orders history list in my-account', 'woo-advanced-shipment-tracking' ),
										"open_track_in_new_tab" => __( 'Open the track link in a new tab', 'woo-advanced-shipment-tracking' )
									),
				'show'		=> true,
				'class'     => '',
			),		
			/*'ast_add_tracking_options' => array(
				'type'		=> 'inline_checkbox',
				'title'		=> __( 'Add Tracking Options', 'woo-advanced-shipment-tracking' ),
				'checkbox_array' => $ast_add_tracking_options,				
				'show'		=> true,
				'class'     => '',
			),*/
			'wc_ast_api_date_format' => array(
				'type'		=> 'radio',
				'title'		=> __( 'API Date Format', 'woo-advanced-shipment-tracking' ),
				'desc'		=> __( 'Choose for which Order status to display', 'woo-advanced-shipment-tracking' ),
				'options'   => array(
									"d-m-Y" => 'DD/MM/YYYY',
									"m-d-Y" => 'MM/DD/YYYY',
							   ),
				'default'   => 'd-m-Y',				
				'show'		=> true,
				'class'     => '',
			),	
		);
		
		$form_data = apply_filters( 'ast_general_settings_options', $form_data );
		
		return $form_data;

	}		

	/*
	* get settings tab array data
	* return array
	*/
	function get_delivered_data(){		
		$form_data = array(			
			'wc_ast_status_delivered' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable custom order status “Delivered"', '' ),				
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_status_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Delivered Label color', '' ),				
				'class'		=> 'status_label_color_th',
				'show'		=> true,
			),
			'wc_ast_status_label_font_color' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Delivered Label font color', '' ),
				'options'   => array( 
									"" =>__( 'Select', 'woocommerce' ),
									"#fff" =>__( 'Light', '' ),
									"#000" =>__( 'Dark', '' ),
								),			
				'class'		=> 'status_label_color_th',
				'show'		=> true,
			),			
			'wcast_enable_delivered_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Delivered order status email', '' ),
				'title_link'=> "<a class='settings_edit' href='".wcast_initialise_customizer_email::get_customizer_url('custom_order_status_email','delivered')."'>".__( 'Edit', 'woocommerce' )."</a>",
				'class'		=> 'status_label_color_th',
				'show'		=> true,
			),			
		);
		return $form_data;

	}		
	
	/*
	* get updated tracking status settings array data
	* return array
	*/
	function get_updated_tracking_data(){		
		$form_data = array(			
			'wc_ast_status_updated_tracking' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable custom order status “Updated Tracking"', '' ),				
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_status_updated_tracking_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Updated Tracking Label color', '' ),				
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
			),
			'wc_ast_status_updated_tracking_label_font_color' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Updated Tracking Label font color', '' ),
				'options'   => array( 
									"" =>__( 'Select', 'woocommerce' ),
									"#fff" =>__( 'Light', '' ),
									"#000" =>__( 'Dark', '' ),
								),			
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
			),			
			'wcast_enable_updated_tracking_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Updated Tracking order status email', '' ),
				'title_link'=> "<a class='settings_edit' href='".wcast_initialise_customizer_email::get_customizer_url('custom_order_status_email','updated_tracking')."'>".__( 'Edit', 'woocommerce' )."</a>",
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
			),			
		);
		return $form_data;

	}

	/*
	* get Partially Shipped array data
	* return array
	*/
	function get_partial_shipped_data(){		
		$form_data = array(			
			'wc_ast_status_partial_shipped' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable custom order status “Partially Shipped"', '' ),				
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_status_partial_shipped_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Partially Shipped Label color', '' ),				
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),
			'wc_ast_status_partial_shipped_label_font_color' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Partially Shipped Label font color', '' ),
				'options'   => array( 
									"" =>__( 'Select', 'woocommerce' ),
									"#fff" =>__( 'Light', '' ),
									"#000" =>__( 'Dark', '' ),
								),			
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),			
			'wcast_enable_partial_shipped_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Partially Shipped order status email', '' ),
				'title_link'=> "<a class='settings_edit' href='".wcast_partial_shipped_customizer_email::get_customizer_url('custom_order_status_email','partially_shipped')."'>".__( 'Edit', 'woocommerce' )."</a>",
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),			
		);
		return $form_data;

	}	
	
	/*
	* settings form save
	*/
	function wc_ast_settings_form_update_callback(){

		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' ) ) {
			
			$data = $this->get_settings_data();						
			
			foreach( $data as $key => $val ){				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
				if(isset($val['type']) && $val['type']=='inline_checkbox' ){
					foreach((array)$val['checkbox_array'] as $key1 => $val1){
						if(isset($_POST[ $key1 ])){						
							update_option( $key1, wc_clean($_POST[ $key1 ]) );
						}
					}					
				}
			} 						
		}
	}

	/**
	* Save custom order status - eanble/disable,color,font,email
	*/
	public function wc_ast_custom_order_status_form_update(){		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_order_status_form', 'wc_ast_order_status_form_nonce' ) ) {
			$data = $this->get_delivered_data();								
			foreach( $data as $key => $val ){
				
				if($key == 'wcast_enable_delivered_email'){					
					if(isset($_POST['wcast_enable_delivered_email'])){											
						
						if($_POST['wcast_enable_delivered_email'] == 1){
							update_option( 'customizer_delivered_order_settings_enabled',wc_clean($_POST['wcast_enable_delivered_email']));
							$enabled = 'yes';
						} else{
							update_option( 'customizer_delivered_order_settings_enabled','');	
							$enabled = 'no';
						}
						
						$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings'); 
						$wcast_enable_delivered_email['enabled'] = $enabled;												
						update_option( 'woocommerce_customer_delivered_order_settings', $wcast_enable_delivered_email );	
					}	
				}
				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}	
			}						
			
			$data = $this->get_partial_shipped_data();						
			
			foreach( $data as $key => $val ){				
				
				if($key == 'wcast_enable_partial_shipped_email'){						
					if(isset($_POST['wcast_enable_partial_shipped_email'])){						
						
						if($_POST['wcast_enable_partial_shipped_email'] == 1){
							update_option( 'customizer_partial_shipped_order_settings_enabled',wc_clean($_POST['wcast_enable_partial_shipped_email']));
							$enabled = 'yes';
						} else{
							update_option( 'customizer_partial_shipped_order_settings_enabled','');
							$enabled = 'no';
						}						
						
						$wcast_enable_partial_shipped_email = get_option('woocommerce_customer_partial_shipped_order_settings');
						$wcast_enable_partial_shipped_email['enabled'] = $enabled;
						update_option( 'woocommerce_customer_partial_shipped_order_settings', $wcast_enable_partial_shipped_email );	
					}	
				}										
				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
			}
			
			$data = $this->get_updated_tracking_data();						
			
			foreach( $data as $key => $val ){				
				
				if($key == 'wcast_enable_updated_tracking_email'){						
					if(isset($_POST['wcast_enable_updated_tracking_email'])){						
						
						if($_POST['wcast_enable_updated_tracking_email'] == 1){
							update_option( 'customizer_updated_tracking_order_settings_enabled',wc_clean($_POST['wcast_enable_updated_tracking_email']));
							$enabled = 'yes';
						} else{
							update_option( 'customizer_updated_tracking_order_settings_enabled','');
							$enabled = 'no';
						}																		
						
						$wcast_enable_updated_tracking_email = get_option('woocommerce_customer_updated_tracking_order_settings');
						$wcast_enable_updated_tracking_email['enabled'] = $enabled;
						update_option( 'woocommerce_customer_updated_tracking_order_settings', $wcast_enable_updated_tracking_email );	
					}	
				}										
				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
			}
						
			echo json_encode( array('success' => 'true') );die();
		}
	}	
		
	/*
	* change style of delivered order label
	*/	
	function footer_function(){
		if ( !is_plugin_active( 'woocommerce-order-status-manager/woocommerce-order-status-manager.php' ) ) {
			$bg_color = get_option('wc_ast_status_label_color','#59c889');
			$color = get_option('wc_ast_status_label_font_color','#fff');						
			
			$ps_bg_color = get_option('wc_ast_status_partial_shipped_label_color','#1e73be');
			$ps_color = get_option('wc_ast_status_partial_shipped_label_font_color','#fff');
			
			$ut_bg_color = get_option('wc_ast_status_updated_tracking_label_color','#23a2dd');
			$ut_color = get_option('wc_ast_status_updated_tracking_label_font_color','#fff');
			?>
			<style>
			.order-status.status-delivered,.order-status-table .order-label.wc-delivered{
				background: <?php echo $bg_color; ?>;
				color: <?php echo $color; ?>;
			}					
			.order-status.status-partial-shipped,.order-status-table .order-label.wc-partially-shipped{
				background: <?php echo $ps_bg_color; ?>;
				color: <?php echo $ps_color; ?>;
			}
			.order-status.status-updated-tracking,.order-status-table .order-label.wc-updated-tracking{
				background: <?php echo $ut_bg_color; ?>;
				color: <?php echo $ut_color; ?>;
			}		
			</style>
			<?php
		}
	}		
	
	/*
	* Ajax call for upload tracking details into order from bulk upload
	*/
	function upload_tracking_csv_fun(){				
		$replace_tracking_info = $_POST['replace_tracking_info'];
		$date_format_for_csv_import = $_POST['date_format_for_csv_import'];
		update_option('date_format_for_csv_import',$date_format_for_csv_import);
		$order_id = $_POST['order_id'];			
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $wast->get_formated_order_id($order_id);
		
		$tracking_provider = $_POST['tracking_provider'];
		$tracking_number = $_POST['tracking_number'];
		$date_shipped = str_replace("/","-",$_POST['date_shipped']);
		
		if(isset($_POST['sku'])){
			$sku = $_POST['sku'];
		}
		
		if(isset($_POST['qty'])){
			$qty = $_POST['qty'];
		}
		
		if(empty($date_shipped)){
			$date_shipped = date("d-m-Y");
		}
		$replace_tracking_info = $_POST['replace_tracking_info'];			

		global $wpdb;					
		
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $this->table WHERE api_provider_name = %s", $tracking_provider );
		$shippment_provider = $wpdb->get_var( $sql );
		
		if( $shippment_provider == 0 ){			
			$sql = "SELECT COUNT(*) FROM $this->table WHERE JSON_CONTAINS(api_provider_name, '[".'"'.$tracking_provider.'"'."]')";
			$shippment_provider = $wpdb->get_var( $sql );			
		}	
		
		if( $shippment_provider == 0 ){
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $this->table WHERE provider_name = %s", $tracking_provider );
			$shippment_provider = $wpdb->get_var( $sql );
		}	 		
		
		$order = wc_get_order($order_id);		
		if ( $order === false ) {
			echo '<li class="invalid_order_id_error">Failed - Invalid Order Id - Order '.$_POST['order_id'].'</li>';exit;
		}
		
		if($shippment_provider == 0){
			echo '<li class="shipping_provider_error">Failed - Invalid Shipping Provider - Order '.$_POST['order_id'].'</li>';exit;
		}
		if(empty($tracking_number)){
			echo '<li class="tracking_number_error">Failed - Empty Tracking Number - Order '.$_POST['order_id'].'</li>';exit;
		}
		
		if(empty($date_shipped)){
			echo '<li class="empty_date_shipped_error">Failed - Empty Date Shipped - Order '.$_POST['order_id'].'</li>';exit;
		}			
		if(!$this->isDate($date_shipped,$date_format_for_csv_import)){
			echo '<li class="invalid_date_shipped_error">Failed - Invalid Date Shipped - Order '.$_POST['order_id'].'</li>';exit;
		}	
		
		if($date_format_for_csv_import == 'm-d-Y'){
			$date_array = explode("-",$date_shipped);
			$date_shipped = $date_array[1].'-'.$date_array[0].'-'.$date_array[2];			
		}
		
		if($replace_tracking_info == 1){
			$order = wc_get_order($order_id);
			
			if($order){	
				$tracking_items = $wast->get_tracking_items( $order_id );			
				
				if ( count( $tracking_items ) > 0 ) {
					foreach ( $tracking_items as $key => $item ) {					
						$tracking_exist = in_array($tracking_number, array_column($_POST['trackings'], 'tracking_number'));												
						
						if($tracking_exist == false) {
							unset( $tracking_items[ $key ] );		
						}
					}
					$wast->save_tracking_items( $order_id, $tracking_items );
				}
			}
		}
		
		if($shippment_provider && $tracking_number && $date_shipped){
					
			$tracking_provider = $this->get_provider_slug_from_name( $tracking_provider );
				
			$args = array(
				'tracking_provider' => wc_clean( $tracking_provider ),					
				'tracking_number'   => wc_clean( $_POST['tracking_number'] ),
				'date_shipped'      => wc_clean( $date_shipped ),
				'status_shipped'	=> wc_clean( $_POST['status_shipped'] ),
			);
				
			if($sku != ''){
				$tracking_items = $wast->get_tracking_items( $order_id );							
				
				$products_list = array();
				
				if($qty > 0){					
					$product_id = wc_get_product_id_by_sku( $sku );
					
					if($product_id){
						
						$product_data =  (object) array (							
							'product' => $product_id,
							'qty' => $qty,
						);
						
						$product_data_array = array();
						$product_data_array[$product_id] = $qty;

						if ( count( $tracking_items ) > 0 ) {								
							foreach ( $tracking_items as $key => $item ) {						
								if($item['tracking_number'] == $_POST['tracking_number']){
									
									if(isset($item['products_list']) && !empty($item['products_list'])){
										
										$product_list_array = array();
										foreach($item['products_list'] as $item_product_list){														
											$product_list_array[$item_product_list->product] = $item_product_list->qty;
										}																							
										
										$mearge_array = array();										
										foreach (array_keys($product_data_array + $product_list_array) as $product) {										
											$mearge_array[$product] = (int)(isset($product_data_array[$product]) ? $product_data_array[$product] : 0) + (int)(isset($product_list_array[$product]) ? $product_list_array[$product] : 0);
										}																								
										
										foreach($mearge_array as $productid => $product_qty){
											$merge_product_data[] =  (object) array (							
												'product' => $productid,
												'qty' => $product_qty,
											);
										}
											
										if(!empty($merge_product_data)){
											$tracking_items[ $key ]['products_list'] = $merge_product_data;	
											$wast->save_tracking_items( $order_id, $tracking_items );

											$order = new WC_Order( $order_id );
											
											$status_shipped = (isset($_POST["status_shipped"])?$_POST["status_shipped"]:"");
		
											if( $status_shipped == 1){			
												if('completed' == $order->get_status()){								
													do_action("send_order_to_trackship", $order_id);	
												} else{
													$order->update_status('completed');
												}			
											}
											
											if( $status_shipped == 2){
												$wc_ast_status_partial_shipped = get_option('wc_ast_status_partial_shipped');
												if($wc_ast_status_partial_shipped){			
													
													$previous_order_status = $order->get_status();
													
													if('partial-shipped' == $previous_order_status){								
														WC()->mailer()->emails['WC_Email_Customer_Partial_Shipped_Order']->trigger( $order_id, $order );	
													}
													
													$order->update_status('partial-shipped');
													do_action("send_order_to_trackship", $order_id);
												}
											}

											if( $status_shipped == 3){
												$wc_ast_status_updated_tracking = get_option('wc_ast_status_updated_tracking');
												if($wc_ast_status_updated_tracking){			
													
													$previous_order_status = $order->get_status();
													
													if('updated-tracking' == $previous_order_status){								
														WC()->mailer()->emails['WC_Email_Customer_Updated_Tracking_Order']->trigger( $order_id, $order );	
													}
													
													$order->update_status('updated-tracking');
													do_action("send_order_to_trackship", $order_id);
												}
											}	
											echo '<li class="success">Success - added tracking info to Order '.$_POST['order_id'].'</li>';
											exit;
										}		
									}											
								}	 
							}																		
						} 
						
						array_push($products_list,$product_data);	
						$product_args = array(
							'products_list' => $products_list,				
						);							
					}
				}																																	
				$args = array_merge($args,$product_args);				
			}																												
			 
			$wast->add_tracking_item( $order_id, $args );
			echo '<li class="success">Success - added tracking info to Order '.$_POST['order_id'].'</li>';exit;				
		} else{
			echo '<li class="invalid_tracking_data_error">Failed - Invalid Tracking Data</li>';exit;
		}		
	}
	
	/**
	* Check if the value is a valid date
	*
	* @param mixed $value
	*
	* @return boolean
	*/
	function isDate($date, $format = 'd-m-Y') 
	{
		if (!$date) {
			return false;
		}
			
		$d = DateTime::createFromFormat($format, $date);
		// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
		return $d && $d->format($format) === $date;
	}			
	
	/*
	* update preview order id in customizer
	*/
	public function update_email_preview_order_fun(){
		set_theme_mod('wcast_availableforpickup_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_returntosender_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_delivered_status_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_outfordelivery_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_intransit_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_onhold_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_pretransit_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_email_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));
		set_theme_mod('wcast_preview_order_id', wc_clean($_POST['wcast_preview_order_id']));		
		exit;
	}
	
	/*
	* update delivered order email status
	*/
	public function update_delivered_order_email_status_fun(){		
		$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings'); 
		$opt = array(
			'enabled' => wc_clean($_POST['wcast_enable_delivered_email']),
			'subject' => $wcast_enable_delivered_email['subject'],
			'heading' => $wcast_enable_delivered_email['heading'],
		);
		update_option( 'woocommerce_customer_delivered_order_settings', $opt );
		exit;
	}	
	
	/*
	* Change completed order email title to Shipped Order
	*/
	public function change_completed_woocommerce_email_title($email_title, $email){
		$wc_ast_status_shipped = get_option('wc_ast_status_shipped',0);		
		// Only on backend Woocommerce Settings "Emails" tab
		if($wc_ast_status_shipped == 1){
			if( isset($_GET['page']) && $_GET['page'] == 'wc-settings' && isset($_GET['tab'])  && $_GET['tab'] == 'email' ) {
				switch ($email->id) {
					case 'customer_completed_order':
						$email_title = __("Shipped Order", 'woo-advanced-shipment-tracking');
						break;
				}
			}
		}
		return $email_title;
	}
	
	/*
	* Add action button in order list to change order status from completed to delivered
	*/
	public function add_delivered_order_status_actions_button($actions, $order){
		
		wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );	
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);
		
		$wc_ast_status_delivered = get_option('wc_ast_status_delivered');
		if($wc_ast_status_delivered){
			if ( $order->has_status( array( 'completed' ) ) || $order->has_status( array( 'shipped' ) )) {
				// Get Order ID (compatibility all WC versions)
				$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				// Set the action button
				$actions['delivered'] = array(
					'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=delivered&order_id=' . $order_id ), 'woocommerce-mark-order-status' ),
					'name'      => __( 'Mark order as delivered', 'woo-advanced-shipment-tracking' ),
					'icon' => '<i class="fa fa-truck">&nbsp;</i>',
					'action'    => "delivered_icon", // keep "view" class for a clean button CSS
				);
			}	
		}			
		
		$wc_ast_show_orders_actions = get_option( 'wc_ast_show_orders_actions' );
		$order_array = array();
		
		foreach($wc_ast_show_orders_actions as $order_status => $value){
			if($value == 1)array_push($order_array, $order_status);			
		}
		
		if( $order->get_shipping_method() != 'Local pickup' && $order->get_shipping_method() != 'Local Pickup' ){		
			if ( $order->has_status( $order_array ) ) {			
				$actions['add_tracking'] = array(
					'url'       => "#".$order->get_id(),
					'name'      => __( 'Add Tracking', 'woo-advanced-shipment-tracking' ),
					'icon' => '<i class="fa fa-map-marker">&nbsp;</i>',
					'action'    => "add_inline_tracking", // keep "view" class for a clean button CSS
				);		
			}
		}
		
		$wc_ast_status_shipped = get_option('wc_ast_status_shipped');
		if($wc_ast_status_shipped){
			$actions['complete']['name'] = __( 'Mark as Shipped', 'woo-advanced-shipment-tracking' );
		}
		
		return $actions;
	}
	
	/*
	* Add delivered action button in preview order list to change order status from completed to delivered
	*/
	public function additional_admin_order_preview_buttons_actions($actions, $order){
		$wc_ast_status_delivered = get_option('wc_ast_status_delivered');
		if($wc_ast_status_delivered){
			// Below set your custom order statuses (key / label / allowed statuses) that needs a button
			$custom_statuses = array(
				'delivered' => array( // The key (slug without "wc-")
					'label'     => __("Delivered", "woo-advanced-shipment-tracking"), // Label name
					'allowed'   => array( 'completed'), // Button displayed for this statuses (slugs without "wc-")
				),
			);
		
			// Loop through your custom orders Statuses
			foreach ( $custom_statuses as $status_slug => $values ){
				if ( $order->has_status( $values['allowed'] ) ) {
					$actions['status']['group'] = __( 'Change status: ', 'woocommerce' );
					$actions['status']['actions'][$status_slug] = array(
						'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status='.$status_slug.'&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
						'name'   => $values['label'],
						'title'  => __( 'Change order status to', 'woo-advanced-shipment-tracking' ) . ' ' . strtolower($values['label']),
						'action' => $status_slug,
					);
				}
			}
		}		
		return $actions;
	}
	
	/*
	* filter shipping providers by stats
	*/
	public function filter_shipiing_provider_by_status_fun(){		
		$status = wc_clean($_POST['status']);
		global $wpdb;		
		if($status == 'active'){				
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 1" );	
		}
		if($status == 'inactive'){			
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 0" );	
		}
		if($status == 'custom'){			
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 0" );	
		}
		if($status == 'all'){
			$status = '';
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table" );	
		}
		$html = $this->get_provider_html($default_shippment_providers,$status);
		echo $html;exit;		
	}	
	
	/*
	* Get providers list html
	*/
	public function get_provider_html($default_shippment_providers,$status){
		$WC_Countries = new WC_Countries();
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
		$ast_base_directory = $upload_dir['basedir'] . '/ast-shipping-providers/';
		?>
		<div class="provider_list">
			<?php if($default_shippment_providers){ 
			if($status == 'custom'){
				?>					
				</br><a href="javaScript:void(0);" class="button-primary btn_ast2 btn_large add_custom_provider" id="add-custom"><span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add Custom Provider', 'woo-advanced-shipment-tracking' ); ?></a>	
			<?php } ?>
			<div class="provider_table_hc">
				<div class="shipping_provider_counter counter"></div>
				<div class="paging shipping_provider_paging"></div>
			</div>			
			<table class="wp-list-table widefat posts provder_table provder_table_desktop" id="shipping-provider-table">
				<thead>
					<tr>						
						<th colspan="2" style="width: 200px;"><?php _e( 'Shipping Providers', 'woo-advanced-shipment-tracking'); ?></th>
						<th style="width: 200px;"><?php _e( 'Display Name', 'woo-advanced-shipment-tracking'); ?><span class="woocommerce-help-tip tipTip" title="<?php _e( 'The custom name will display in the tracking info section on the customer order emails, my-account, shipment tracking page and shipment status emails.', 'woo-advanced-shipment-tracking' ); ?>"></span></th>
						<th style="width: 200px;"><?php _e( 'API Name', 'woo-advanced-shipment-tracking'); ?></th>
						<?php do_action('ast_shipping_provider_column_after_api_name'); ?>
						<th><?php _e( 'Country', 'woo-advanced-shipment-tracking'); ?></th>						
						<th><?php _e( 'Default', 'woo-advanced-shipment-tracking'); ?></th>
						<th><?php _e( 'TrackShip', 'woo-advanced-shipment-tracking'); ?></th>
						<th class="provider_actions_th" style="min-width: 110px;"><?php _e( 'Actions', 'woo-advanced-shipment-tracking'); ?></th>						
						<th style=""><?php _e( 'Active', 'woo-advanced-shipment-tracking'); ?></th>						
					</tr>
				</thead>
				<tbody>		
					<?php 					
					foreach($default_shippment_providers as $d_s_p){ ?>
						<tr>							
							<td>
								<?php  
								$custom_thumb_id = $d_s_p->custom_thumb_id;
								if( $d_s_p->shipping_default == 1 ){
									if($custom_thumb_id != 0){
										$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
										$provider_image = $image_attributes[0];
									} else if(!file_exists($ast_base_directory.''.sanitize_title($d_s_p->provider_name).'.png')){
										$provider_image = wc_advanced_shipment_tracking()->plugin_dir_url().'assets/shipment-provider-img/'.sanitize_title($d_s_p->provider_name).'.png?v='.wc_advanced_shipment_tracking()->version;
									} else{
										$provider_image = $ast_directory.''.sanitize_title($d_s_p->provider_name).'.png?v='.wc_advanced_shipment_tracking()->version;
									}
								?>
								<img class="provider-thumb" src="<?php echo $provider_image; ?>">
								<?php } else{ 
								
								$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
								
								if($custom_thumb_id != 0){ ?>
									<img class="provider-thumb" src="<?php echo $image_attributes[0]; ?>">
								<?php } else{ ?>
									<img class="provider-thumb" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/icon-default.png">
								<?php } ?>
								<?php } ?>																					
																
							</td>
							<td>
								<span class="provider_name"><?php echo $d_s_p->provider_name; ?></span>								
							</td>	
							<td><span class="provider_name"><?php echo $d_s_p->custom_provider_name; ?></span></td>
							<td><?php 
							if($this->isJSON($d_s_p->api_provider_name)){
								echo implode(",",json_decode($d_s_p->api_provider_name));
							} else{
								echo $d_s_p->api_provider_name;
							} ?></td>
							<?php do_action('ast_shipping_provider_column_content_after_api_name', $d_s_p->provider_name); ?>
							<td><span class="provider_country"><?php
									$search  = array('(US)', '(UK)');
									$replace = array('', '');
									if($d_s_p->shipping_country && $d_s_p->shipping_country != 'Global'){
										echo esc_html(str_replace($search, $replace, $WC_Countries->countries[$d_s_p->shipping_country]));
									} elseif($d_s_p->shipping_country && $d_s_p->shipping_country == 'Global'){
										echo esc_html('Global');
									}									
								?>
								</span>	
							</td>	
							<td><?php $default_provider = get_option("wc_ast_default_provider" );?>
								<label for="make_default_<?php echo $d_s_p->id; ?>" id="default_label_<?php echo $d_s_p->id; ?>" class="<?php if($d_s_p->display_in_order != 1) { echo 'disable_label'; } ?>">
									<input type="checkbox" id="make_default_<?php echo $d_s_p->id; ?>" name="make_provider_default" data-id="<?php echo $d_s_p->id; ?>" class="make_provider_default" value="<?php echo sanitize_title( $d_s_p->provider_name )?>" <?php if( $default_provider == sanitize_title( $d_s_p->provider_name ) )echo 'checked';?> <?php if($d_s_p->display_in_order != 1) { echo 'disabled'; } ?>>
								</label>
							</td>							
							<td>
								<?php if($d_s_p->trackship_supported == 1) { echo '<span class="woocommerce-help-tip tipTip dashicons dashicons-yes" title="'.__( 'TrackShip supported', 'woo-advanced-shipment-tracking').'"></span>'; } else{ echo '<span class="woocommerce-help-tip tipTip dashicons dashicons-no-alt" title="'.__( 'TrackShip not supported', 'woo-advanced-shipment-tracking').'"></span>'; }?>
							</td>								
							<td>							
							<?php if( $d_s_p->shipping_default == 0 ){ ?>									
									<span class="dashicons dashicons-trash remove provider_actions_btn" data-pid="<?php echo $d_s_p->id; ?>"></span>
								<?php } ?>
								<span class="dashicons dashicons-edit edit_provider provider_actions_btn" data-provider="<?php echo ($d_s_p->shipping_default == 1) ? 'default_provider' : 'custom_provider';?>" data-pid="<?php echo $d_s_p->id; ?>"></span>
								<a href="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" title="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" target="_blank"><span class="dashicons dashicons-external provider_actions_btn"></span></a>
							</td>	
							<td>
								<input class="tgl tgl-flat status_slide" id="list-switch-<?php echo $d_s_p->id; ?>" name="select_custom_provider[]" type="checkbox" <?php if($d_s_p->display_in_order == 1) { echo 'checked'; } ?> value="<?php echo $d_s_p->id; ?>"/>
								<label class="tgl-btn" for="list-switch-<?php echo $d_s_p->id; ?>"></label>
							</td>								
						</tr>
					<?php } ?>
				</tbody>				
			</table>
			<table class="wp-list-table widefat posts provder_table provder_table_mobile" id="shipping-provider-table">
				<thead>
					<tr>						
						<th><?php _e( 'Provider', 'woo-advanced-shipment-tracking'); ?></th>
						<th><?php _e( 'Default', 'woo-advanced-shipment-tracking'); ?></th>						
						<th><?php _e( 'Actions', 'woo-advanced-shipment-tracking'); ?></th>
					</tr>
				</thead>
				<tbody>		
					<?php 					
					foreach($default_shippment_providers as $d_s_p){ ?>
						<tr>							
							<td>
								<?php 
								?>
								<div class="row-1">
									<div class="left-div">
										<a href="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" title="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" target="_blank">
										<?php
											$custom_thumb_id = $d_s_p->custom_thumb_id;
											if( $d_s_p->shipping_default == 1 ){
												if($custom_thumb_id != 0){
													$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
													$provider_image = $image_attributes[0];
												} else if(!file_exists($ast_base_directory.''.sanitize_title($d_s_p->provider_name).'.png')){
													$provider_image = wc_advanced_shipment_tracking()->plugin_dir_url().'assets/shipment-provider-img/'.sanitize_title($d_s_p->provider_name).'.png?v='.wc_advanced_shipment_tracking()->version;
												} else{
													$provider_image = $ast_directory.''.sanitize_title($d_s_p->provider_name).'.png?v='.wc_advanced_shipment_tracking()->version;
												}
											?>
											<img class="provider-thumb" src="<?php echo $provider_image; ?>">
											<?php } else{ 											
											$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
											
											if($custom_thumb_id != 0){ ?>
												<img class="provider-thumb" src="<?php echo $image_attributes[0]; ?>">
											<?php } else{
											?>
												<img class="provider-thumb" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/icon-default.png">
											<?php } ?>
											<?php } ?>						
										</a>
									</div>
									<div class="right-div">
										<a href="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" title="<?php echo str_replace("%number%","",$d_s_p->provider_url ); ?>" target="_blank">
											<span class="provider_name"><?php echo $d_s_p->provider_name; ?></span>
										</a><br>
										<span class="provider_country"><?php
											$search  = array('(US)', '(UK)');
											$replace = array('', '');
											if($d_s_p->shipping_country && $d_s_p->shipping_country != 'Global'){
												echo str_replace($search, $replace, $WC_Countries->countries[$d_s_p->shipping_country]);
											} elseif($d_s_p->shipping_country && $d_s_p->shipping_country == 'Global'){
												echo 'Global';
											} ?>
										</span>
									</div>
								</div>
							</td>							
							<td><?php $default_provider = get_option("wc_ast_default_provider" );?>
								<label for="make_default_<?php echo $d_s_p->id; ?>" id="default_label_<?php echo $d_s_p->id; ?>" class="<?php if($d_s_p->display_in_order != 1) { echo 'disable_label'; } ?>">
									<input type="checkbox" id="make_default_<?php echo $d_s_p->id; ?>" name="make_provider_default" data-id="<?php echo $d_s_p->id; ?>" class="make_provider_default" value="<?php echo sanitize_title( $d_s_p->provider_name )?>" <?php if( $default_provider == sanitize_title( $d_s_p->provider_name ) )echo 'checked';?> <?php if($d_s_p->display_in_order != 1) { echo 'disabled'; } ?>>
								</label>
							</td>																					
							<td>							
								<?php if( $d_s_p->shipping_default == 0 ){ ?>
									<span class="dashicons dashicons-edit edit_provider provider_actions_btn" data-pid="<?php echo $d_s_p->id; ?>"></span>
									<span class="dashicons dashicons-trash remove provider_actions_btn" data-pid="<?php echo $d_s_p->id; ?>"></span>
								<?php } ?>								
								<span class="mdl-list__item-secondary-action">
									<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="mobile-list-switch-<?php echo $d_s_p->id; ?>">
										<input type="checkbox" name="select_custom_provider[]" id="mobile-list-switch-<?php echo $d_s_p->id; ?>" class="mdl-switch__input status_slide" value="<?php echo $d_s_p->id; ?>" <?php if($d_s_p->display_in_order == 1) { echo 'checked'; } ?> />
									</label>
								</span>
							</td>													
						</tr>
					<?php } ?>
				</tbody>				
			</table>
			<div class="provider_table_hc provider_table_hc_footer">
				<div class="shipping_provider_counter counter"></div>
				<div class="paging shipping_provider_paging"></div>
			</div>
			<?php } else{ 
				if($status == 'custom'){ ?>					
				<p class="provider_message"><?php echo sprintf(__("You did not create any %s shipping providers yet.", 'woo-advanced-shipment-tracking'), $status); ?></p>
				<a href="javaScript:void(0);" class="button-primary btn_ast2 btn_large add_custom_provider" id="add-custom"><span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add Custom Provider', 'woo-advanced-shipment-tracking' ); ?></a>	
			<?php } else{ ?>
				<p class="provider_message"><?php echo sprintf(__("You don't have any %s shipping providers.", 'woo-advanced-shipment-tracking'), $status); ?></p>
			<?php } }	?>		
		</div>	
		<?php 
	}
	
	/*
	* Check if valid json
	*/
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
			
	/*
	* Update shipment provider status
	*/
	function update_shipment_status_fun(){			
		global $wpdb;		
		$woo_shippment_table_name = $this->table;
		$success = $wpdb->update($woo_shippment_table_name, 
			array(
				"display_in_order" => wc_clean($_POST['checked']),
			),	
			array('id' => wc_clean($_POST['id']))
		);
		exit;	
	}
	
	/**
	* update default provider function 
	*/
	function update_default_provider_fun(){
		if($_POST['checked'] == 1){
			update_option("wc_ast_default_provider", wc_clean($_POST['default_provider']) );
		} else{
			update_option("wc_ast_default_provider", '' );
		}
		exit;
	}
	
	/**
	* Create slug from title
	*/
	public static function create_slug($text){
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		
		// trim
		$text = trim($text, '-');
		
		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);
		
		// lowercase
		$text = strtolower($text);
		
		$text = 'cp-'.$text;
		
		if (empty($text)) {
			return '';
		}
		
		return $text;
	}

	/**
	* Add custom shipping provider function 
	*/
	function add_custom_shipment_provider_fun(){
		
		global $wpdb;
		
		$woo_shippment_table_name = $this->table;
		$provider_slug = $this->create_slug(wc_clean($_POST['shipping_provider']));		
		if($provider_slug == ''){
			$provider_slug = sanitize_text_field($_POST['shipping_provider']);
		}
		
		$data_array = array(
			'shipping_country' => sanitize_text_field($_POST['shipping_country']),
			'provider_name' => sanitize_text_field($_POST['shipping_provider']),
			'custom_provider_name' => sanitize_text_field($_POST['shipping_display_name']),
			'ts_slug' => $provider_slug,
			'provider_url' => sanitize_text_field($_POST['tracking_url']),
			'custom_thumb_id' => sanitize_text_field($_POST['thumb_id']),			
			'display_in_order' => 1,
			'shipping_default' => 0,
		);
		
		$result = $wpdb->insert( $woo_shippment_table_name, $data_array );
		
		$status = 'custom';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 0" );		
		$html = $this->get_provider_html($default_shippment_providers,$status);
		echo $html;exit;		
	}
	
	/*
	* delet provide by ajax
	*/
	public function woocommerce_shipping_provider_delete(){				

		$provider_id = wc_clean($_POST['provider_id']);
		if ( ! empty( $provider_id ) ) {
			global $wpdb;
			$where = array(
				'id' => $provider_id,
				'shipping_default' => 0
			);
			$wpdb->delete( $this->table, $where );
		}
		$status = 'custom';
		
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 0" );
		$html = $this->get_provider_html($default_shippment_providers,$status);
		echo $html;exit;
	}
	
	/**
	* Get shipping provider details fun 
	*/
	public function get_provider_details_fun(){
		$id = wc_clean($_POST['provider_id']);
		global $wpdb;
		$shippment_provider = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table WHERE id=%d",$id ) );
		if($shippment_provider[0]->custom_thumb_id != 0){
			$image = wp_get_attachment_url($shippment_provider[0]->custom_thumb_id);	
		} else{
			$image = NULL;
		}
		
		$provider_name = $shippment_provider[0]->provider_name;			
		$custom_provider_name = $shippment_provider[0]->custom_provider_name;
		$api_provider_name = $shippment_provider[0]->api_provider_name;		
		
		echo json_encode( array('id' => $shippment_provider[0]->id,'provider_name' => $provider_name,'custom_provider_name' => $custom_provider_name,'api_provider_name' => $api_provider_name,'provider_url' => $shippment_provider[0]->provider_url,'shipping_country' => $shippment_provider[0]->shipping_country,'custom_thumb_id' => $shippment_provider[0]->custom_thumb_id,'image' => $image) );exit;			
	}
	
	/**
	* Update custom shipping provider and returen html of it
	*/
	public function update_custom_shipment_provider_fun(){
		
		global $wpdb;		
		
		//if(empty($_POST['api_provider_name'])){
		if (array_filter($_POST['api_provider_name']) == []) {
			$api_provider_name = NULL;
			//echo '</pre>';print_r($_POST['api_provider_name']);exit;	
		} else{
			$api_provider_name = wc_clean(json_encode($_POST['api_provider_name']));
		}	 	
		
		$provider_type = $_POST['provider_type'];
		if($provider_type == 'default_provider'){
			$data_array = array(				
				'custom_provider_name' => sanitize_text_field($_POST['shipping_display_name']),
				'api_provider_name' => $api_provider_name,				
				'custom_thumb_id' => sanitize_text_field($_POST['thumb_id']),				
			);				
		} else{
			$data_array = array(
				'shipping_country' => sanitize_text_field($_POST['shipping_country']),
				'provider_name' => sanitize_text_field($_POST['shipping_provider']),
				'custom_provider_name' => sanitize_text_field($_POST['shipping_display_name']),
				'ts_slug' => sanitize_title($_POST['shipping_provider']),
				'custom_thumb_id' => sanitize_text_field($_POST['thumb_id']),
				'provider_url' => sanitize_text_field($_POST['tracking_url'])		
			);	
		}
		
		$where_array = array(
			'id' => $_POST['provider_id'],			
		);
		$wpdb->update( $this->table, $data_array, $where_array );
		$status = 'active';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 1" );	
		$html = $this->get_provider_html($default_shippment_providers,$status);
		echo $html;exit;
	}

	/**
	* Reset default provider
	*/
	public function reset_default_provider_fun(){
		global $wpdb;		
				
		$data_array = array(				
			'custom_provider_name' => NULL,				
			'custom_thumb_id' => NULL,
			'api_provider_name' => NULL,			
		);	
		$where_array = array(
			'id' => $_POST['provider_id'],			
		);
		$wpdb->update( $this->table, $data_array, $where_array );
		$status = 'active';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 1" );	
		$html = $this->get_provider_html($default_shippment_providers,$status);
		echo $html;exit;
	}	
	
	/**
	* Update bulk status of providers to active
	*/
	public function update_provider_status_active_fun(){
		global $wpdb;
		$data_array = array(
			'display_in_order' => 1,			
		);
		$where_array = array(
			'display_in_order' => 0,			
		);
		$wpdb->update( $this->table, $data_array, $where_array);
		$status = 'active';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 1" );	
		$html = $this->get_provider_html($default_shippment_providers,$status);
		exit;
	}
	
	/**
	* Update bulk status of providers to inactive
	*/	
	public function update_provider_status_inactive_fun(){
		global $wpdb;
		$data_array = array(
			'display_in_order' => 0,			
		);
		$where_array = array(
			'display_in_order' => 1,			
		);
		$status = 'inactive';
		$wpdb->update( $this->table, $data_array, $where_array);
		update_option("wc_ast_default_provider", '' );
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 0" );	
		$html = $this->get_provider_html($default_shippment_providers,$status);
		exit;
	}		

	/**
	 * Add bulk filter for Shipping provider in orders list
	 *
	 * @since 2.4
	 */
	public function filter_orders_by_shipping_provider(){
		global $typenow, $wpdb;
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 1" );
		
		if ( 'shop_order' === $typenow ) { ?>
			<select name="_shop_order_shipping_provider" id="dropdown_shop_order_shipping_provider">
				<option value=""><?php _e( 'Filter by shipping provider', 'woo-advanced-shipment-tracking' ); ?></option>
				<?php foreach ( $default_shippment_providers as $provider ) : ?>
					<option value="<?php echo esc_attr( $provider->ts_slug ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_shipping_provider'] ) ? selected( $provider->ts_slug, $_GET['_shop_order_shipping_provider'], false ) : '' ); ?>>
						<?php printf( '%1$s', esc_html( $provider->provider_name ) ); ?>
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
	public function filter_orders_by_shipping_provider_query( $vars ){
		global $typenow;		
		if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_shipping_provider'] ) && $_GET['_shop_order_shipping_provider'] != '') {
			$vars['meta_key']   = '_wc_shipment_tracking_items';
			$vars['meta_value'] = $_GET['_shop_order_shipping_provider'];
			$vars['meta_compare'] = 'LIKE';						
		}

		return $vars;
	}			
	
	/**
	 * Process bulk filter action for shipment status orders
	 *
	 * @since 2.7.4
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_orders_by_tracking_number_query( $search_fields ){
		$search_fields[] = '_wc_shipment_tracking_items';
		return $search_fields;
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
	
	public function update_custom_order_status_email_display_fun(){
		
		$status = wc_clean($_POST['status']);
		
		$wc_ast_show_orders_actions = get_option('wc_ast_show_orders_actions');		
		$wc_ast_show_orders_actions[$status] = 1;
		update_option( 'wc_ast_show_orders_actions', $wc_ast_show_orders_actions );			
		
		$wc_ast_unclude_tracking_info = get_option('wc_ast_unclude_tracking_info');
		$wc_ast_unclude_tracking_info[$status] = 1;		
		update_option( 'wc_ast_unclude_tracking_info', $wc_ast_unclude_tracking_info );		
	}	
	
	/*
     * get tracking provider slug (ts_slug) from database
     * 
     * return provider slug
    */
	public function get_provider_slug_from_name( $tracking_provider_name ){
		
		global $wpdb;
		
		$tracking_provider = $wpdb->get_var( $wpdb->prepare( "SELECT ts_slug FROM $this->table WHERE api_provider_name = '%s'", $tracking_provider_name ) );		
		
		if(!$tracking_provider){			
			$query = "SELECT ts_slug FROM $this->table WHERE JSON_CONTAINS(api_provider_name, '[".'"'.$tracking_provider_name.'"'."]')";
			$tracking_provider = $wpdb->get_var( $query );			
		}		
		
		if(!$tracking_provider){
			$tracking_provider = $wpdb->get_var( $wpdb->prepare( "SELECT ts_slug FROM $this->table WHERE provider_name = '%s'", $tracking_provider_name ) );
		}		
		
		if(!$tracking_provider){
			$tracking_provider =  $tracking_provider_name ;
		}
		
		return $tracking_provider;
	}
}