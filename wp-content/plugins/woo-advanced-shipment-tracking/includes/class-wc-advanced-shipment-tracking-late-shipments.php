<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Late_Shipments {
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;	

	const CRON_HOOK = 'ast_late_shipments_cron_hook';	
	
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
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0
	 * @return  void
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
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		
		$wcast_enable_late_shipments_email = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_enable_late_shipments_admin_email','');
		
		$wc_ast_api_key = get_option('wc_ast_api_key');
		if(!$wcast_enable_late_shipments_email || !$wc_ast_api_key){
			return;	
		}
		
		//cron schedule added
		add_filter( 'cron_schedules', array( $this, 'late_shipments_cron_schedule') );				
		add_action( 'wp_ajax_send_late_shipments_email', array( $this, 'send_late_shipments_email') );
		add_action( 'wp_ajax_nopriv_send_late_shipments_email', array( $this, 'send_late_shipments_email') );
		
		//Send Late Shipments Email
		add_action( self::CRON_HOOK, array( $this, 'send_late_shipments_email' ) );				
		
		if (!wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() , 'ast_late_shipments_cron_events', self::CRON_HOOK );			
		}
	}
	
	/**
	 * Remove the Cron
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function remove_cron() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Setup the Cron
	 * @access public
	 * @since  1.0.0
	 */
	public function setup_cron() {

		$late_shipments_email_settings = get_option('late_shipments_email_settings');
		
		$wcast_late_shipments_trigger_alert = isset( $late_shipments_email_settings['wcast_late_shipments_trigger_alert'] ) ? $late_shipments_email_settings['wcast_late_shipments_trigger_alert'] : '';						
		
		if($wcast_late_shipments_trigger_alert == 'daily_digest_on'){
			
			$wcast_late_shipments_daily_digest_time = isset( $late_shipments_email_settings['wcast_late_shipments_daily_digest_time'] ) ? $late_shipments_email_settings['wcast_late_shipments_daily_digest_time'] : '';
			
			// Create a Date Time object when the cron should run for the first time
			$first_cron = new DateTime( date( 'Y-m-d' ) .' '. $wcast_late_shipments_daily_digest_time .':00', new DateTimeZone( wc_timezone_string() ) );	
			
			
			$first_cron->setTimeZone(new DateTimeZone("GMT"));
			
			$time = new DateTime( date( 'Y-m-d H:i:s' ), new DateTimeZone( wc_timezone_string() ) );
			
			if( $time->getTimestamp() >  $first_cron->getTimestamp() ) {
				$first_cron->modify( '+1 day' );
			}

			wp_schedule_event( $first_cron->format( 'U' ) + $first_cron->getOffset(), 'daily', self::CRON_HOOK );					
		
		} else{
			if (!wp_next_scheduled( self::CRON_HOOK ) ) {
				wp_schedule_event( time() , 'ast_late_shipments_cron_events', self::CRON_HOOK );			
			}
		}
	}
	
	/*
	* add schedule for late shipments check
	*
	* @since  1.0
	*
	* @return  array
	*/
	function late_shipments_cron_schedule( $schedules ){				
		
		$schedules[ 'ast_late_shipments_cron_events' ] = array(
			'interval' => 86400,
			'display'  => __( 'Every day' ),
		);
		return $schedules;
	}		
	
	/**
	 *
	 * Send Late Shipments Email
	 *
	 */
	public function send_late_shipments_email() {	
		
		$orders = new WP_Query(
			array(
				'post_type'      => 'shop_order',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'posts_per_page' => -1,
				'meta_key'          => 'shipment_status',
				'meta_compare' => 'EXISTS', // The comparison argument
				// Using the date_query to filter posts from last 90 days
				'date_query' => array(
					array(
						'after' => '-90 days'
					)
				)
			)
		);
		
		$wcast_late_shipments_settings = new wcast_late_shipments_customizer_email();
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		
		$wcast_late_shipments_days = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_late_shipments_days',$wcast_late_shipments_settings->defaults['wcast_late_shipments_days']);
		
		foreach ( $orders->posts as $order ) {	
			$order_object = new WC_Order( $order->ID );	
			$shipment_status = get_post_meta( $order_object->get_id(), "shipment_status", true);
			
			foreach($shipment_status as $key => $tracker){						
				$tracking_items = get_post_meta( $order_object->get_id(), '_wc_shipment_tracking_items', true );
							
				$shipment_length = $this->get_shipment_length($tracker);	
				
				if($tracker['status'] != 'available_for_pickup' && $tracker['status'] != 'delivered'){
					if($shipment_length >= $wcast_late_shipments_days){		
						$late_shipments = get_post_meta( $order_object->get_id(), 'late_shipments_email', true );
						if(isset($late_shipments[$tracking_items[$key]['tracking_number']])){
							if($late_shipments[$tracking_items[$key]['tracking_number']]['email_send'] != 1){
								$email_send = $this->late_shippment_email_trigger($order_object->get_id(), $order_object, $tracker, $tracking_items[$key]['tracking_number']);
								if($email_send){							
									$late_shipments_array[$tracking_items[$key]['tracking_number']] = array( 'email_send'    => '1' );
									update_post_meta( $order_object->get_id(), 'late_shipments_email', $late_shipments_array );	
								}	
							}
						} else{
							$email_send = $this->late_shippment_email_trigger($order_object->get_id(), $order_object, $tracker, $tracking_items[$key]['tracking_number']);
							if($email_send){							
								$late_shipments_array[$tracking_items[$key]['tracking_number']] = array( 'email_send'    => '1' );
								update_post_meta( $order_object->get_id(), 'late_shipments_email', $late_shipments_array );	
							}							
						}									
					}	
				}								
			}							
		}
		exit;
	}

	/*
	* get shiment lenth of tracker
	* return (int)days
	*/
	function get_shipment_length($ep_tracker){
		if( empty($ep_tracker['tracking_events'] ))return 0;
		if( count( $ep_tracker['tracking_events'] ) == 0 )return 0;		
		
		$first = reset($ep_tracker['tracking_events']);
		$first_date = $first->datetime;
		$last = ( isset( $ep_tracker['tracking_destination_events'] ) && count( $ep_tracker['tracking_destination_events'] ) > 0  ) ? end($ep_tracker['tracking_destination_events']) : end($ep_tracker['tracking_events']);
		$last_date = $last->datetime;
		
		$status = $ep_tracker['status'];
		if( $status != 'delivered' ){
			$last_date = date("Y-m-d H:i:s");
		}		
		
		$days = $this->get_num_of_days( $first_date, $last_date );		
		return $days;
	}
	
	/*
	*
	*/
	function get_num_of_days( $first_date, $last_date ){
		$date1 = strtotime($first_date);
		$date2 = strtotime($last_date);
		$diff = abs($date2 - $date1);
		return date( "d", $diff );
	}

	/**
	 * code for send shipment status email
	 */
	public function late_shippment_email_trigger($order_id, $order, $tracker, $tracking_number){			
					
		$wcast_late_shipments_settings = new wcast_late_shipments_customizer_email();
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		
		$email_subject = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_late_shipments_email_subject',$wcast_late_shipments_settings->defaults['wcast_late_shipments_email_subject']);						
		
		$subject = wc_trackship_email_manager()->email_subject($email_subject,$order_id,$order);		
		
		$email_to = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_late_shipments_email_to',$wcast_late_shipments_settings->defaults['wcast_late_shipments_email_to']);		
		
		$email_to = explode(",",$email_to);
				
		foreach($email_to as $email){												
			$email_heading = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_late_shipments_email_heading',$wcast_late_shipments_settings->defaults['wcast_late_shipments_email_heading']);			
			
			$email_content = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_late_shipments_email_content',$wcast_late_shipments_settings->defaults['wcast_late_shipments_email_content']);										
			
			$sent_to_admin = false;
			$plain_text = false;
			
			$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$tracking_items = $wast->get_tracking_items( $order_id, true );
				
			foreach($tracking_items as $key => $item){
				if($item['tracking_number'] != $tracking_number){
					unset($tracking_items[$key]);
				}
			}
				
			$recipient = wc_trackship_email_manager()->email_to($email,$order,$order_id);
			
			$email_content = wc_trackship_email_manager()->email_content($email_content,$order_id, $order);
			
			$email_content = $this->email_content($email_content,$order_id, $order, $tracker);
			
			$mailer = WC()->mailer();
			
			$email_heading = wc_trackship_email_manager()->email_heading($email_heading,$order_id,$order);
			
			ob_start();
			
			$local_template	= get_stylesheet_directory().'/woocommerce/emails/tracking-info.php';
			
			if ( file_exists( $local_template ) && is_writable( $local_template )){
				wc_get_template( 'emails/tracking-info.php', array( 'tracking_items' => $tracking_items, 'order_id'=> $order_id ), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
			} else{
				wc_get_template( 'emails/tracking-info.php', array( 
					'tracking_items' => $tracking_items,
					'order_id' => $order_id,						
				), 'woocommerce-advanced-shipment-tracking/', wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' );
			}
			$email_content .= ob_get_clean();									
							
			// create a new email
			$email = new WC_Email();
		
			// wrap the content with the email template and then add styles
			$email_content = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $email_content ) ) );
			$headers = "Content-Type: text/html\r\n";
			add_filter( 'wp_mail_from', array( wc_trackship_email_manager(), 'get_from_address' ) );
			add_filter( 'wp_mail_from_name', array( wc_trackship_email_manager(), 'get_from_name' ) );
			
			$email_send = wp_mail( $recipient, $subject, $email_content, $email->get_headers() );
			$logger = wc_get_logger();
			$context = array( 'source' => 'trackship_late_shipments_email_log' );
			$logger->error( "Order_Id: ".$order_id." Late Shipments" .$email_send, $context );
			return $email_send;
		}		
	}	
	/**
	 * code for format email content 
	 */
	public function email_content($email_content, $order_id, $order, $tracker){	
		$shipment_length = $this->get_shipment_length($tracker);	
		$email_content = str_replace( '{shipment_length}', $shipment_length, $email_content );		
		return $email_content;
	}
}
