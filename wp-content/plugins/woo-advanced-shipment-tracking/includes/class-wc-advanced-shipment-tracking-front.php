<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Front {

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
	 * @return WC_Advanced_Shipment_Tracking_Actions
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
		add_shortcode( 'wcast-track-order', array( $this, 'woo_track_order_function') );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles' ));		
		add_action( 'wp_ajax_nopriv_get_tracking_info', array( $this, 'get_tracking_info_fun') );
		add_action( 'wp_ajax_get_tracking_info', array( $this, 'get_tracking_info_fun') );
	}	
			
	/**
	 * Include front js and css
	*/
	public function front_styles(){		
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'front-js', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/front.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
		wp_localize_script( 'front-js', 'zorem_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		
		wp_register_style( 'front_style',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/front.css', array(), wc_advanced_shipment_tracking()->version );		
		
		$action = (isset($_REQUEST["action"])?$_REQUEST["action"]:"");
		
		if($action == 'preview_tracking_page'){
			wp_enqueue_style( 'front_style' );
			wp_enqueue_script( 'front-js' );	
		}
		
	}
	
	/**
	 * Return tracking details or tracking form for shortcode - [wcast-track-order]
	*/
	public function woo_track_order_function(){
		
		wp_enqueue_style( 'front_style' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'front-js' );	
		
		$wc_ast_api_key = get_option('wc_ast_api_key');	
		
		if(!$wc_ast_api_key){ ?>
			<p><a href="https://trackship.info/" target="blank">TrackShip</a> is not active.</p>
			<?php return;
		}
		
		if(isset($_GET['order_id']) &&  isset($_GET['order_key'])){
			
			$order_id = wc_clean($_GET['order_id']);
			
			$order = wc_get_order( $order_id );
			
			if(empty($order))return;
			
			$order_key = $order->get_order_key();
		
			if($order_key != $_GET['order_key'])return;
			
			if(empty($order))return;
			
			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				$tracking_items = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );			
			} else {				
				$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );			
			}
			
			$shipment_status = get_post_meta( $order_id, "shipment_status", true);			
			
			if(!$tracking_items)unset($order_id);			
		}
	
		if(!isset($order_id)){
			ob_start();		
			$this->track_form_template();
			$form = ob_get_clean();	
			return $form;
		} else{
			ob_start();												
			echo $this->display_tracking_page( $order_id, $tracking_items, $shipment_status );
			$form = ob_get_clean();	
			return $form;		
		}		
	}
	
	/**
	 * Ajax function for get tracking details
	*/
	public function get_tracking_info_fun(){				
		
		$wc_ast_api_key = get_option('wc_ast_api_key');	
		
		if(!$wc_ast_api_key){
			return;
		}
		
		$order_id = wc_clean($_POST['order_id']);		
		$email = sanitize_email($_POST['order_email']);
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $wast->get_formated_order_id($order_id);
		
		$order = wc_get_order( $order_id );
		
		if(empty($order)){
			ob_start();		
			$this->track_form_template();
			$form = ob_get_clean();
			echo json_encode( array('success' => 'false', 'message' => __( 'Order not found.', 'woo-advanced-shipment-tracking' ), 'html' => $form ));die();	
		}
		
		$order_id = $wast->get_formated_order_id($order_id);									
		$order_email = $order->get_billing_email();
		
		if(strtolower($order_email) != strtolower($email)){
			ob_start();		
			$this->track_form_template();
			$form = ob_get_clean();	
			echo json_encode( array('success' => 'false', 'message' => __( 'Order not found.', 'woo-advanced-shipment-tracking' ), 'html' => $form ));die();	
		}
		
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$tracking_items = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );			
		} else {			
			$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );			
		} 
		
		$shipment_status = get_post_meta( $order_id, "shipment_status", true);
		
		if(!$tracking_items){
			ob_start();		
			$this->track_form_template();
			$form = ob_get_clean();
			echo json_encode( array('success' => 'false', 'message' => __( 'Tracking details not found', 'woo-advanced-shipment-tracking' ), 'html' => $form ));die();	
		}
		ob_start();		
		$html = $this->display_tracking_page( $order_id, $tracking_items, $shipment_status );
		$html = ob_get_clean();
		echo json_encode( array('success' => 'true', 'message' => '', 'html' => $html ));die();							
	}
	
	/*
	* retuern Tracking form HTML
	*/
	public function track_form_template(){
		$local_template	= get_stylesheet_directory().'/woocommerce/tracking/tracking-form.php';
		if ( file_exists( $local_template ) && is_writable( $local_template )){	
			wc_get_template( 'tracking/tracking-form.php', array(), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
		} else{
			wc_get_template( 'tracking/tracking-form.php', array(), 'woocommerce-advanced-shipment-tracking/', wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' );	
		}		
	}
	
	/*
	* retuern Tracking page HTML
	*/
	public function display_tracking_page( $order_id, $tracking_items, $shipment_status ){
		
		wp_enqueue_style( 'front_style' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'front-js' );	
		
		global $wpdb;
				
		$border_color = get_option('wc_ast_select_border_color');
		$hide_tracking_events = get_option('wc_ast_hide_tracking_events');
		$tracking_page_layout = get_option('wc_ast_select_tracking_page_layout','t_layout_1');	?>
		
		<style>					
			<?php if($border_color){ ?>
				body .col.tracking-detail{
					border: 1px solid <?php echo $border_color; ?>;
				}
				body .col.tracking-detail .shipment-header{
					border-bottom: 1px solid <?php echo $border_color; ?>;
				}
				body .col.tracking-detail .trackship_branding{
					border-top: 1px solid <?php echo $border_color; ?>;
				}
			<?php }	 ?>
		</style>
		<?php
		
		$num = 1;
		$total_trackings = sizeof($tracking_items);			
		
		foreach($tracking_items as $key => $item){
			$tracking_number = $item['tracking_number'];
			$trackship_url = 'https://trackship.info';
			$tracking_provider = $item['tracking_provider'];
			$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table WHERE ts_slug = %s", $tracking_provider ) );
			$tracking_provider = $results->provider_name;
			$custom_provider_name = $results->custom_provider_name;
			$custom_thumb_id = $results->custom_thumb_id;
						
			/*** Update in 2.7.9
			* Date - 20/01/2020
			* Remove api call code after three month - get_tracking_info
			***/
			$tracker = new \stdClass();
			
			if( isset($shipment_status[$key]['pending_status']) ){
				$tracker->ep_status = $shipment_status[$key]['pending_status'];								
			} else if( isset($shipment_status[$key]['status']) ){
				$tracker->ep_status = $shipment_status[$key]['status'];
			}
			
			$tracker->est_delivery_date = isset( $shipment_status[$key]['est_delivery_date'] ) ? $shipment_status[$key]['est_delivery_date'] : '';
						
			if( isset($shipment_status[$key]['tracking_events']) || isset($shipment_status[$key]['pending_status'])){
								
				if( isset($shipment_status[$key]['tracking_events']) ){
					$tracker->tracking_detail = json_encode($shipment_status[$key]['tracking_events']);
				}
				
				if(isset($shipment_status[$key]['tracking_destination_events'])){
					$tracker->tracking_destination_events = json_encode($shipment_status[$key]['tracking_destination_events']);
				}
							
				$decoded_data = true;				
			}									
			
			$tracking_detail_org = '';	
			$trackind_detail_by_status_rev = '';
			
			if(isset($tracker->tracking_detail) && $tracker->tracking_detail != 'null'){						
				$tracking_detail_org = json_decode($tracker->tracking_detail);						
				$trackind_detail_by_status_rev = array_reverse($tracking_detail_org);	
			}
			
			$tracking_details_by_date = array();
			
			foreach((array)$trackind_detail_by_status_rev as $key => $details){
				if(isset($details->datetime)){		
					$date = date('Y-m-d', strtotime($details->datetime));
					$tracking_details_by_date[$date][] = $details;
				}
			}
			
			$tracking_destination_detail_org = '';	
			$trackind_destination_detail_by_status_rev = '';
			
			if(isset($tracker->tracking_destination_events) && $tracker->tracking_destination_events != 'null'){						
				$tracking_destination_detail_org = json_decode($tracker->tracking_destination_events);	
					
				$trackind_destination_detail_by_status_rev = array_reverse($tracking_destination_detail_org);	
			}
			
			$tracking_destination_details_by_date = array();
			
			foreach((array)$trackind_destination_detail_by_status_rev as $key => $details){
				if(isset($details->datetime)){		
					$date = date('Y-m-d', strtotime($details->datetime));
					$tracking_destination_details_by_date[$date][] = $details;
				}
			}	
			
			$order = wc_get_order( $order_id );			
			$order_url =  $order->get_view_order_url();
			
			$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$order_number = $ast->get_custom_order_number($order_id);						
			
			if( isset($tracker->ep_status) ){										
			
				if($tracking_page_layout == 't_layout_1'){ ?>
			
				<div class="tracking-detail col">			
					
					<div class="shipment-header">
						<?php if($total_trackings > 1 ){ ?>
							<p class="shipment_heading"><?php echo sprintf(__("Shipment : %s (out of %s)", 'woo-advanced-shipment-tracking'), $num , $total_trackings); ?></p>
							<span><?php _e( 'Order', 'woocommerce' ); ?> <a href="<?php echo $order_url; ?>" target="_blank"><strong>#<?php echo apply_filters( 'ast_order_number_filter', $order_number); ?></strong></a></span>
						<?php } else{ ?>
							<p class="shipment_heading"><?php _e( 'Shipment', 'woo-advanced-shipment-tracking' ); ?></p>
							<span><?php _e( 'Order', 'woocommerce' ); ?> <a href="<?php echo $order_url; ?>" target="_blank"><strong>#<?php echo apply_filters( 'ast_order_number_filter', $order_number); ?></strong></a></span>
						<?php } ?>
					</div> 
					<?php
					echo $this->tracking_page_header( $order_id, $tracking_provider, $tracking_number, $tracker, $item, $custom_provider_name, $custom_thumb_id );
					
					if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'INVALID_TRACKING_NUM' || $tracker->ep_status == 'carrier_unsupported' || $tracker->ep_status == 'invalid_user_key' || $tracker->ep_status == 'wrong_shipping_provider' || $tracker->ep_status == 'deleted' || $tracker->ep_status == 'pending'){
					} elseif(isset($tracker->ep_status)){
						echo $this->layout1_progress_bar($tracker);
					} 
					
					if( !empty($trackind_detail_by_status_rev) && $hide_tracking_events != 1  ){
						echo $this->layout1_tracking_details( $trackind_detail_by_status_rev, $tracking_details_by_date, $trackind_destination_detail_by_status_rev, $tracking_destination_details_by_date, $tracker , $order_id, $tracking_provider, $tracking_number );
					} 
					
					$remove_trackship_branding =  get_option('wc_ast_remove_trackship_branding');
					
					if($remove_trackship_branding != 1){ ?> 
						<div class="trackship_branding">
							<p>Shipment Tracking info by <a href="https://trackship.info/trackings/?number=<?php echo $tracking_number; ?>" title="TrackShip" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png"></a></p>
						</div>
					<?php } ?>					
	
				</div>
				<?php } else{ ?>
				<div class="tracking-detail tracking-layout-2 col">
					<?php if($total_trackings > 1 ){ ?>
						<p class="shipment_heading"><?php echo sprintf(__("Shipment - %s (out of %s)", 'woo-advanced-shipment-tracking'), $num , $total_trackings); ?></p>
					<?php } 			
					
					echo $tracking_header = $this->tracking_page_header( $order_id, $tracking_provider, $tracking_number, $tracker, $item, $custom_provider_name, $custom_thumb_id ); 			
					
					if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'INVALID_TRACKING_NUM' || $tracker->ep_status == 'carrier_unsupported' || $tracker->ep_status == 'invalid_user_key' || $tracker->ep_status == 'wrong_shipping_provider' || $tracker->ep_status == 'deleted' || $tracker->ep_status == 'pending'){
					} elseif(isset($tracker->ep_status)){ echo $this->layout2_progress_bar($tracker); }									
				
					if( !empty($trackind_detail_by_status_rev) && $hide_tracking_events != 1  ){				
						echo $this->layout1_tracking_details( $trackind_detail_by_status_rev, $tracking_details_by_date, $trackind_destination_detail_by_status_rev, $tracking_destination_details_by_date, $tracker, $order_id, $tracking_provider, $tracking_number );	
					} 
				
					$remove_trackship_branding =  get_option('wc_ast_remove_trackship_branding');
				
					if($remove_trackship_branding != 1){ ?> 
						<div class="trackship_branding">
							<p>Shipment Tracking info by <a href="https://trackship.info/trackings/?number=<?php echo $tracking_number; ?>" title="TrackShip" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png"></a></p>
						</div>
					<?php } ?>
			
				</div>	
			<?php } } else{ ?>
				<div class="tracking-detail col">
					<h1 class="shipment_status_heading text-secondary text-center"><?php _e( 'Tracking&nbsp;#&nbsp;'.$tracking_number, 'woo-advanced-shipment-tracking' ); ?></h1>
					<h3 class="text-center"><?php _e( 'Tracking details not found in TrackShip', 'woo-advanced-shipment-tracking' ); ?></h3>
				</div>
			<?php } 
			$num++;
		}	
	}
	
	/*
	* Tracking Page Header
	*/
	public function tracking_page_header( $order_id, $tracking_provider, $tracking_number, $tracker,$item, $custom_provider_name = null, $custom_thumb_id = 0 ){
				
		if(isset($tracker->est_delivery_date)){	
			$unixTimestamp = strtotime($tracker->est_delivery_date);				
			$day = date("l", $unixTimestamp);
		}
		
		$wc_ast_link_to_shipping_provider = get_option('wc_ast_link_to_shipping_provider');
		
		$tracking_number_url = '';
		
		if($wc_ast_link_to_shipping_provider == 1){
			$tracking_number_url = $this->get_tracking_number_url( $order_id, $tracking_provider, $tracking_number, $item );	
		}		
		
		$hide_tracking_provider_image = get_option('wc_ast_hide_tracking_provider_image');
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
		$ast_base_directory = $upload_dir['basedir'] . '/ast-shipping-providers/';
		
		if($custom_thumb_id != 0){
			$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array('60','60') );
			$src = $image_attributes[0];
		} else if(!file_exists($ast_base_directory.''.sanitize_title($tracking_provider).'.png')){
			$src = wc_advanced_shipment_tracking()->plugin_dir_url().'assets/shipment-provider-img/'.sanitize_title($tracking_provider).'.png?v='.wc_advanced_shipment_tracking()->version;
		} else{
			$src = $ast_directory.''.sanitize_title($tracking_provider).'.png?v='.wc_advanced_shipment_tracking()->version;
		}
		
		if($custom_provider_name != NULL){
			$provider_name = $custom_provider_name;	
		} else{
			$provider_name = $tracking_provider;	
		}		 				
		
		include 'views/front/tracking_page_header.php';	
	}		
	
	public function layout1_progress_bar($tracker){
		if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'INVALID_TRACKING_NUM' || $tracker->ep_status == 'carrier_unsupported' || $tracker->ep_status == 'invalid_user_key' || $tracker->ep_status == 'wrong_shipping_provider' || $tracker->ep_status == 'deleted' || $tracker->ep_status == 'pending' || $tracker->ep_status == 'unknown' || $tracker->ep_status == 'pre_transit' || $tracker->ep_status == 'failure'){
			$width = '0';				
		} else if($tracker->ep_status == 'in_transit' || $tracker->ep_status == 'on_hold'){
			$width = '33%';				
		} else if($tracker->ep_status == 'out_for_delivery'){
			$width = '67%';				
		} else if($tracker->ep_status == 'available_for_pickup'){
			$width = '67%';				
		} else if($tracker->ep_status == 'return_to_sender'){
			$width = '67%';				
		} else if($tracker->ep_status == 'delivered'){
			$width = '100%';				
		} ?>
		<div class="tracker-progress-bar tracking_layout_1">
			<div class="progress">
				<div class="progress-bar <?php echo $tracker->ep_status; ?>" style="width: <?php echo $width; ?>;"></div>
			</div>
		</div>
	<?php }
	
	public function layout2_progress_bar($tracker){
		if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'INVALID_TRACKING_NUM' || $tracker->ep_status == 'carrier_unsupported' || $tracker->ep_status == 'invalid_user_key' || $tracker->ep_status == 'wrong_shipping_provider' || $tracker->ep_status == 'deleted' || $tracker->ep_status == 'pending' || $tracker->ep_status == 'unknown' || $tracker->ep_status == 'pre_transit' || $tracker->ep_status == 'failure'){
			$width = '0';				
		} else if($tracker->ep_status == 'in_transit' || $tracker->ep_status == 'on_hold'){
			$width = '33%';				
		} else if($tracker->ep_status == 'out_for_delivery'){
			$width = '67%';				
		} else if($tracker->ep_status == 'available_for_pickup'){
			$width = '67%';				
		} else if($tracker->ep_status == 'return_to_sender'){
			$width = '67%';				
		} else if($tracker->ep_status == 'delivered'){
			$width = '100%';				
		} ?>
		<div class="tracker-progress-bar">
			<div class="progress">
				<div class="progress-bar <?php echo $tracker->ep_status; ?>" style="width: <?php echo $width; ?>;"></div>
			</div>
		</div>	
	<?php }
	
	public function layout1_tracking_details( $trackind_detail_by_status_rev, $tracking_details_by_date, $trackind_destination_detail_by_status_rev, $tracking_destination_details_by_date, $tracker, $order_id, $tracking_provider, $tracking_number ){  
		include 'views/front/layout1_tracking_details.php';		
	}		
	
	/**
	 * convert string to date
	*/
	public static function convertString ($date) 
    { 
        // convert date and time to seconds 
        $sec = strtotime($date); 
  
        // convert seconds into a specific format 
        $date = date("m/d/Y H:i", $sec); 
  
        // print final date and time 
        return $date; 
    }

	/*
	* Function for return tracking url from tracking number and shipping provider	
	*/
	public function get_tracking_number_url( $order_id, $tracking_provider, $tracking_number, $item ){
		
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$postcode = get_post_meta( $order_id, '_shipping_postcode', true );
		} else {
			$order    = new WC_Order( $order_id );
			$postcode = $order->get_shipping_postcode();
		}

		if ( empty( $postcode ) ) {
			$postcode = get_post_meta( $order_id, '_shipping_postcode', true );
		}
		
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		
		$link_format = '';
		
		foreach ( $ast->get_providers() as $provider => $format ) {		
			if (  $format['provider_name']  === $tracking_provider ) {
				$link_format = $format['provider_url'];				
				break;
			}			
		}
		
		if($link_format){
			$searchVal = array("%number%", str_replace(' ', '', "%2 $ s") );
			$tracking_number = str_replace(' ', '', $tracking_number);
			$replaceVal = array( $tracking_number, urlencode( $postcode ) );
			$link_format = str_replace($searchVal, $replaceVal, $link_format); 
			
			if(isset($item['tracking_product_code'])){
				$searchnumber2 = array("%number2%", str_replace(' ', '', "%2 $ s") );
				$tracking_product_code = str_replace(' ', '', $item['tracking_product_code']);					
				$link_format = str_replace($searchnumber2, $tracking_product_code, $link_format); 						
			}
			
			if($order->get_shipping_country() != null){
				$shipping_country = $order->get_shipping_country();	
			} else{
				$shipping_country = $order->get_billing_country();	
			}								
			
			if($shipping_country){												
				
				if($tracking_provider == 'JP Post' && $shipping_country != 'JP'){
					$local_en = '&locale=en';
					$link_format = $link_format.$local_en;
				}						
				
				if($tracking_provider == 'DHL eCommerce'){
					$link_format = str_replace('us-en', strtolower($shipping_country).'-en', $link_format); 	
				}
				
				if($tracking_provider == 'DHL Freight'){
					$link_format = str_replace('global-en', strtolower($shipping_country).'-en', $link_format);
				}
			}
			
			if($order->get_shipping_postcode() != null){
				$shipping_postal_code = $order->get_shipping_postcode();	
			} else{
				$shipping_postal_code = $order->get_billing_postcode();
			}	
			
			$shipping_country = str_replace(' ', '', $shipping_country);					
			$link_format = str_replace("%country_code%", $shipping_country, $link_format);
													
			if($tracking_provider == 'APC Overnight'){	
				$shipping_postal_code = str_replace(' ', '+', $shipping_postal_code);
			} else{
				$shipping_postal_code = str_replace(' ', '', $shipping_postal_code);
			}
			$link_format = str_replace("%postal_code%", $shipping_postal_code, $link_format);
		}
		return $link_format;
	}	
	
	/*
	* Tracking Page preview
	*/
	public static function preview_tracking_page(){
		$action = (isset($_REQUEST["action"])?$_REQUEST["action"]:"");
		if($action != 'preview_tracking_page')return;		
		
		wp_head();
		
		$primary_color = get_option('wc_ast_select_primary_color');	
		$border_color = get_option('wc_ast_select_border_color');
		$wc_ast_link_to_shipping_provider = get_option('wc_ast_link_to_shipping_provider');
		$hide_tracking_provider_image = get_option('wc_ast_hide_tracking_provider_image');
		$hide_tracking_events = get_option('wc_ast_hide_tracking_events');
		$tracking_page_layout = get_option('wc_ast_select_tracking_page_layout','t_layout_1');	
		$remove_trackship_branding =  get_option('wc_ast_remove_trackship_branding');
		
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
		$ast_base_directory = $upload_dir['basedir'] . '/ast-shipping-providers/';
		
		include 'views/front/preview_tracking_page.php';exit;
	}
}