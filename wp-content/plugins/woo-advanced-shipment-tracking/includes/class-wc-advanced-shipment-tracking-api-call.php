<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WC_Advanced_Shipment_Tracking_Api_Call {
	
	public function __construct() {
		
	}
	
	/*
	* check if string is json or not
	*/
	public function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	/*
	* get trackship shipment status and update in order meta
	*/
	public function get_trackship_apicall( $order_id ){
		
		$logger = wc_get_logger();
		$context = array( 'source' => 'wc_ast_trackship' );
		$array = array();
		$order = wc_get_order( $order_id );
		$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );
		
		$shipment_status = get_post_meta( $order_id, "shipment_status", true);

		if( $tracking_items ){
			foreach( ( array )$tracking_items as $key => $val ){				
				if(isset($shipment_status[$key]['status']) && $shipment_status[$key]['status'] == 'delivered')continue;
				$tracking_number = trim($val['tracking_number']);
				
				$tracking_provider = isset( $val['tracking_provider'] ) ? $val['tracking_provider'] : $val['custom_tracking_provider'];
				$tracking_provider = apply_filters('convert_provider_name_to_slug',$tracking_provider);
				
				if( isset($tracking_number) ){
					
					//do api call
					$response = $this->get_trackship_data( $order, $tracking_number, $tracking_provider );
										
					if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						
						$logger = wc_get_logger();
						$context = array( 'source' => 'Trackship_apicall_is_wp_error' );
						$logger->error( "Something went wrong: {$error_message} For Order id :" .$order->get_id(), $context );
						
						//error like 403 500 502 
						$timestamp = time() + 5*60;
						$args = array( $order->get_id() );
						$hook = 'wcast_retry_trackship_apicall';
						wp_schedule_single_event( $timestamp, $hook, $args );
						
						$shipment_status = get_post_meta( $order->get_id(), "shipment_status", true);
						if( is_string($shipment_status) )$shipment_status = array();
						$shipment_status[$key]['status'] = "Something went wrong: {$error_message}";
						$shipment_status[$key]['status_date'] = date("Y-m-d H:i:s");
						
						update_post_meta( $order->get_id(), "shipment_status", $shipment_status);
						
					} else {
						
						$code = $response['response']['code'];

						if( $code == 200 ){
							//update trackers_balance, status_msg
							if( !$this->isJson($response['body']) ){
								return;
							}
							$body = json_decode($response['body'], true);
							
							$shipment_status = get_post_meta( $order->get_id(), "shipment_status", true);
							
							if( is_string($shipment_status) )$shipment_status = array();
							
							$shipment_status[$key]['pending_status'] = $body['status_msg'];
														
							
							$shipment_status[$key]['status_date'] = date("Y-m-d H:i:s");
							$shipment_status[$key]['est_delivery_date'] = '';														
							
							update_post_meta( $order->get_id(), "shipment_status", $shipment_status);
							
							if(isset($body['trackers_balance'])){
								update_option('trackers_balance',$body['trackers_balance']);
							}														
						} else {
							//error like 400
							$body = json_decode($response['body'], true);															
							$shipment_status = get_post_meta( $order->get_id(), "shipment_status", true);
							if( is_string($shipment_status) )$shipment_status = array();
							$shipment_status[$key]['status'] = "Error message : ".$body['message'];
							$shipment_status[$key]['status_date'] = date("Y-m-d H:i:s");
							$shipment_status[$key]['est_delivery_date'] = '';
							update_post_meta( $order->get_id(), "shipment_status", $shipment_status);
							
							$logger = wc_get_logger();
							$context = array( 'source' => 'Trackship_apicall_error' );
							$logger->error( "Error code : ".$code. " For Order id :" .$order->get_id(), $context );
							$logger->error( "Body : ".$response['body'], $context );
						}						
					}					
				}
			}
		}
		return $array;
	}
	
	/*
	* get trackship shipment data
	*/
	public function get_trackship_data( $order, $tracking_number, $tracking_provider ){
		$user_key = get_option("wc_ast_api_key");
		$domain = get_home_url();
		$order_id = $order->get_id();
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$custom_order_number = $wast->get_custom_order_number($order_id);
		
		if(empty($custom_order_number)){
			$custom_order_number = $order_id;
		}
		
		if($order->get_shipping_country() != null){
			$shipping_country = $order->get_shipping_country();	
		} else{
			$shipping_country = $order->get_billing_country();	
		}
		
		if($order->get_shipping_postcode() != null){
			$shipping_postal_code = $order->get_shipping_postcode();	
		} else{
			$shipping_postal_code = $order->get_billing_postcode();
		}
		
		$url = 'https://trackship.info/wp-json/tracking/create';
		
		$args['body'] = array(
			'user_key' => $user_key,
			'order_id' => $order_id,
			'custom_order_id' => $custom_order_number,
			'domain' => $domain,
			'tracking_number' => $tracking_number,
			'tracking_provider' => $tracking_provider,
			'postal_code' => $shipping_postal_code,
			'destination_country' => $shipping_country,
		);

		$args['headers'] = array(
			'user_key' => $user_key
		);	
		$args['timeout'] = 10;
		$response = wp_remote_post( $url, $args );
		return $response;
	}
	
	/*
	* delete tracking number from trackship
	*/
	public function delete_tracking_number_from_trackship($order_id, $tracking_number, $tracking_provider){
		$user_key = get_option("wc_ast_api_key");
		$domain = get_site_url();		
		
		$url = 'https://trackship.info/wp-json/tracking/delete';
		
		$args['body'] = array(
			'user_key' => $user_key,
			'order_id' => $order_id,
			'domain' => $domain,
			'tracking_number' => $tracking_number,
			'tracking_provider' => $tracking_provider,
		);

		$args['headers'] = array(
			'user_key' => $user_key
		);	
		$args['timeout'] = 10;
		$response = wp_remote_post( $url, $args );		
		return $response;
	}
}
