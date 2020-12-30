<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API shipment tracking controller.
 *
 * Handles requests to /orders/shipment-tracking endpoint.
 *
 * @since 1.5.0
 */

class WC_Advanced_Shipment_Tracking_REST_API_Controller extends WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-ast/v3';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'orders/(?P<order_id>[\d]+)/shipment-trackings';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'shop_order';

	/**
	 * @param $namespace
	 *
	 * @return WC_Shipment_Tracking_Order_REST_API_Controller
	 */
	public function set_namespace( $namespace ) {
		$this->namespace = $namespace;
		return $this;
	}
	
	/**
	 * Register the routes for trackings.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => array_merge( $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ), array(
					'tracking_number' => array(
						'required' => true,
					),
				) ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/providers', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_providers' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[a-fA-F0-9]{0,32})', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
		
		if(!class_exists('trackship_for_woocommerce')){			
			
			//disconnect_from_trackship
			register_rest_route( $this->namespace, '/disconnect_from_trackship', array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'disconnect_from_trackship_fun' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array_merge( $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ), array(
						'user_key' => array(
							'required' => true,
						),
					) ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			) );
			
			//tracking webhook
			register_rest_route( $this->namespace, '/tracking-webhook', array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'tracking_webhook' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			) );
			
			//check_wcast_installed
			register_rest_route( $this->namespace, '/check_wcast_installed', array(			
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'check_wcast_installed' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			) );
			
			// this is use for sendle
			register_rest_route( $this->namespace, '/check_wcast_installed_from_third_party_tool', array(			
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'check_wcast_installed_from_third_party_tool' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			) );
		}	
	}		
	
	/*
	* check_wcast_installed_from_third_party_tool
	*/
	public function check_wcast_installed_from_third_party_tool( $request ){		
		
		$data = array(
			'status' => 'installed'
		);
		return rest_ensure_response( $data );
	}
	
	/*
	* check_wcast_installed
	*/
	public function check_wcast_installed( $request ){
		$wc_ast_api_key = get_option('wc_ast_api_key');
		$wc_ast_api_enabled = get_option('wc_ast_api_enabled');		
		if(empty($wc_ast_api_key)){
			update_option('wc_ast_api_key',$request['user_key']);
		}
		
		if($wc_ast_api_enabled == ''){
			update_option('wc_ast_api_enabled',1);
		}
		
		if($request['trackers_balance']){
			update_option( 'trackers_balance', $request['trackers_balance'] );
		}			
		
		$trackship = new WC_Advanced_Shipment_Tracking_Trackship;
		$trackship->create_tracking_page();
		
		$data = array(
			'status' => 'installed'
		);
		return rest_ensure_response( $data );
	}
	
	public function tracking_webhook( $request ){
		$content = print_r($request, true);
		$logger = wc_get_logger();
		$context = array( 'source' => 'trackship_log' );
		$logger->error( "New tracking_webhook \n\n".$content."\n\n", $context );
		//error_log("New tracking_webhook \n\n".$content."\n\n", 3, ABSPATH . "trackship.log");
		
		//validation
		
		$user_key = $request['user_key'];
		$order_id = $request['order_id'];
		$tracking_number = $request['tracking_number'];
		$tracking_provider = $request['tracking_provider'];
		$tracking_event_status = $request['tracking_event_status'];
		$tracking_event_date = $request['tracking_event_date'];
		$tracking_est_delivery_date = $request['tracking_est_delivery_date'];
		$tracking_events = $request['tracking_events'];
		$tracking_destination_events = $request['tracking_destination_events'];
		$previous_status = '';
		
		$st = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$trackship = WC_Advanced_Shipment_Tracking_Trackship::get_instance();
		
		$tracking_items = $st->get_tracking_items( $order_id, true );
		
		foreach( ( array )$tracking_items as $key => $tracking_item ){
			if( trim($tracking_item['tracking_number']) != trim($tracking_number) )continue;
			
			$shipment_status = get_post_meta( $order_id, "shipment_status", true);						
			
			if( is_string($shipment_status) )$shipment_status = array();			
			
			if(isset($shipment_status[$key]['status'])){
				$previous_status = $shipment_status[$key]['status'];	
			}			
			
			unset($shipment_status[$key]['pending_status']);
			
			$shipment_status[$key]['status'] = $tracking_event_status;
			$shipment_status[$key]['tracking_events'] = json_decode($tracking_events);
			$shipment_status[$key]['tracking_destination_events'] = json_decode($tracking_destination_events);
			
			$shipment_status[$key]['status_date'] = $tracking_event_date;
			if($tracking_est_delivery_date){
				$shipment_status[$key]['est_delivery_date'] = date("Y-m-d", strtotime($tracking_est_delivery_date));
			}
						
			update_post_meta( $order_id, "shipment_status", $shipment_status);
			
			$trackship->trigger_tracking_email( $order_id, $previous_status, $tracking_event_status, $tracking_item, $shipment_status[$key] );
		}
		
		$trackship->check_tracking_delivered( $order_id );
		
		$data = array(
			'status' => 'success'
		);
		
		return rest_ensure_response( $data );
	}

	/**
	 * Check whether a given request has permission to read order shipment-trackings.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_post_permissions( $this->post_type, 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-shipment-tracking' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request has access create order shipment-tracking.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return boolean
	 */
	public function create_item_permissions_check( $request ) {
		
		if ( ! wc_rest_check_post_permissions( $this->post_type, 'create' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce-shipment-tracking' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request has access to read a order shipment-tracking.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! wc_rest_check_post_permissions( $this->post_type, 'read', (int) $request['order_id'] ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'woocommerce-shipment-tracking' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request has access delete a order shipment-tracking.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return boolean
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! wc_rest_check_post_permissions( $this->post_type, 'delete', (int) $request['order_id'] ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_delete', __( 'Sorry, you are not allowed to delete this resource.', 'woocommerce-shipment-tracking' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Checks if an order ID is a valid order.
	 *
	 * @param int $order_id
	 * @return bool
	 * @since 1.6.4
	 */
	public function is_valid_order_id( $order_id ) {
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$order = get_post( $order_id );
			if ( empty( $order->post_type ) || $this->post_type !== $order->post_type ) {
				return false;
			}
		} else {
			$order = wc_get_order( $order_id );
			// in 3.0 the order factor will return false if the order class
			// throws an exception or the class doesn't exist.
			if ( false === $order ) {
				return false;
			}
		}
		return true;
	}
	
	/*
	* 
	*/
	public function update_user_key($request){
		$add_key = update_option( 'wc_ast_api_key', $request['user_key'] );
		$wc_ast_api_enabled = update_option( 'wc_ast_api_enabled', 1 );
		$trackers_balance = update_option( 'trackers_balance', $request['trackers_balance'] );				
	}

	/*
	* disconnect store from TS
	*/
	public function disconnect_from_trackship_fun($request){
		$add_key = update_option( 'wc_ast_api_key', '' );
		$wc_ast_api_enabled = update_option( 'wc_ast_api_enabled', 0 );
		delete_option( 'wc_ast_api_enabled' );
		delete_option( 'trackers_balance' );
	}
	
	/**
	 * Get shipment-trackings from an order.
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function get_items( $request ) {
		$order_id = (int) $request['order_id'];
		
		if ( ! $this->is_valid_order_id( $order_id ) ) {
			return new WP_Error( 'woocommerce_rest_order_invalid_id', __( 'Invalid order ID.', 'woocommerce-shipment-tracking' ), array( 'status' => 404 ) );
		}

		$st             = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$tracking_items = $st->get_tracking_items( $order_id, true );

		$data = array();
		foreach ( $tracking_items as $tracking_item ) {
			$tracking_item['order_id'] = $order_id;

			$tracking_item = $this->prepare_item_for_response( $tracking_item, $request );
			$tracking_item = $this->prepare_response_for_collection( $tracking_item );
			$data[]        = $tracking_item;
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get shipment-tracking providers.
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function get_providers( $request ) {
		$st = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		return rest_ensure_response( $st->get_providers_for_app() );
	}

	/**
	 * Create a single order shipment-tracking.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['tracking_id'] ) ) {
			return new WP_Error( 'woocommerce_rest_shop_order_shipment_tracking_exists', __( 'Cannot create existing order shipment tracking.', 'woo-advanced-shipment-tracking' ), array( 'status' => 400 ) );
		}

		$order_id = (int) $request['order_id'];
		
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $ast->get_formated_order_id( $order_id );
		
		if ( ! $this->is_valid_order_id( $order_id ) ) {
			return new WP_Error( 'woocommerce_rest_order_invalid_id', __( 'Invalid order ID.', 'woo-advanced-shipment-tracking' ), array( 'status' => 404 ) );
		}
		
		if(preg_match('/[^a-z0-9- \b]+/i', $request['tracking_number'])){
			return new WP_Error( 'woocommerce_rest_order_invalid_id', __( 'Special character not allowd in tracking number', 'woo-advanced-shipment-tracking' ), array( 'status' => 404 ) );			
		}
				
		$ast_admin = WC_Advanced_Shipment_Tracking_Admin::get_instance();		

		$tracking_provider_name = (isset($request['custom_tracking_provider']) && !empty($request['custom_tracking_provider']))  ? $request['custom_tracking_provider'] : $request['tracking_provider'];	
		
		$replace_tracking = isset($request['replace_tracking']) ? $request['replace_tracking'] : 0;	
		
		if($replace_tracking == 1){
			$order = wc_get_order($order_id);
			
			if($order){	
				$tracking_items = $ast->get_tracking_items( $order_id );			
				
				if ( count( $tracking_items ) > 0 ) {
					foreach ( $tracking_items as $key => $item ) {
						unset( $tracking_items[ $key ] );													
					}
					$ast->save_tracking_items( $order_id, $tracking_items );
				}
			}
		}
		
		$tracking_provider = $ast_admin->get_provider_slug_from_name( $tracking_provider_name );
		
		$args = array(
			'tracking_provider'        => wc_clean( $tracking_provider ),		
			'custom_tracking_link'     => wc_clean( $request['custom_tracking_link'] ),
			'tracking_number'          => wc_clean( $request['tracking_number'] ),
			'date_shipped'             => wc_clean( $request['date_shipped'] ),
			'status_shipped'           => wc_clean( $request['status_shipped'] ),
			'source'				   => 'REST_API',
		);				
		
		$args = apply_filters( 'ast_api_create_item_arg', $args, $request );		
		
		$tracking_item             = $ast->add_tracking_item( $order_id, $args );		
		$tracking_item['order_id'] = $order_id;
		$formatted                 = $ast->get_formatted_tracking_item( $order_id, $tracking_item );
		$tracking_item             = array_merge( $tracking_item, $formatted );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $tracking_item, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, str_replace( '(?P<order_id>[\d]+)', $order_id, $this->rest_base ), $tracking_item['tracking_id'] ) ) );

		return $response;
	}

	/**
	 * Get a single order shipment-tracking.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$tracking_id = $request['id'];

		$order_id = (int) $request['order_id'];
		if ( ! $this->is_valid_order_id( $order_id ) ) {
			return new WP_Error( 'woocommerce_rest_order_invalid_id', __( 'Invalid order ID.', 'woocommerce-advanced-shipment-tracking' ), array( 'status' => 404 ) );
		}

		$st            = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$tracking_item = $st->get_tracking_item( $order_id, $tracking_id, true );

		if ( ! $tracking_item ) {
			return new WP_Error( 'woocommerce_rest_order_shipment_tracking_invalid_id', __( 'Invalid shipment tracking ID.', 'woocommerce-advanced-shipment-tracking' ), array( 'status' => 404 ) );
		}

		$tracking_item['order_id'] = $order_id;
		$tracking_item             = $this->prepare_item_for_response( $tracking_item, $request );
		$response                  = rest_ensure_response( $tracking_item );

		return $response;
	}

	/**
	 * Delete a single order shipment-tracking.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$tracking_id = $request['id'];

		$order_id = (int) $request['order_id'];
				
		if ( ! $this->is_valid_order_id( $order_id ) ) {
			return new WP_Error( 'woocommerce_rest_order_invalid_id', __( 'Invalid order ID.', 'woocommerce-advanced-shipment-tracking' ), array( 'status' => 404 ) );
		}

		$st            = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$tracking_item = $st->get_tracking_item( $order_id, $tracking_id, true );

		if ( ! $tracking_item ) {
			return new WP_Error( 'woocommerce_rest_order_shipment_tracking_invalid_id', __( 'Invalid shipment tracking ID.', 'woocommerce-advanced-shipment-tracking' ), array( 'status' => 404 ) );
		}

		$tracking_item['order_id'] = $order_id;
		$tracking_item             = $this->prepare_item_for_response( $tracking_item, $request );
		$response                  = rest_ensure_response( $tracking_item );

		$result = $st->delete_tracking_item( $order_id, $tracking_id );
		if ( ! $result ) {
			return new WP_Error( 'woocommerce_rest_cannot_delete_order_shipment_tracking', __( 'The shipment tracking cannot be deleted.', 'woocommerce-advanced-shipment-tracking' ), array( 'status' => 500 ) );
		}

		return $response;
	}

	/**
	 * Prepare a single order shipment-note output for response.
	 *
	 * @param array           $tracking_item Shipment tracking item
	 * @param WP_REST_Request $request       Request object
	 *
	 * @return WP_REST_Response $response Response data
	 */
	public function prepare_item_for_response( $tracking_item, $request ) {
		$date_shipped = date("Y-m-d");
		if(isset($tracking_item['date_shipped'])){
			$date_shipped = date( 'Y-m-d', $tracking_item['date_shipped'] );
		}
		$data = array(
			'tracking_id'       => $tracking_item['tracking_id'],
			'tracking_provider' => $tracking_item['formatted_tracking_provider'],
			'tracking_link'     => $tracking_item['formatted_tracking_link'],
			'tracking_number'   => $tracking_item['tracking_number'],
			'date_shipped'      => $date_shipped,
		);

		$order_id = $tracking_item['order_id'];

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $order_id, $tracking_item ) );

		/**
		 * Filter order shipment-tracking object returned from the REST API.
		 *
		 * @param WP_REST_Response $response      The response object.
		 * @param array            $tracking_item Order tracking item used to create response.
		 * @param WP_REST_Request  $request       Request object.
		 */
		return apply_filters( 'woocommerce_rest_prepare_order_shipment_tracking', $response, $tracking_item, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param int   $order_id          Order ID
	 * @param array $shipment_tracking Shipment tracking item
	 *
	 * @return array Links for the given order shipment-tracking.
	 */
	protected function prepare_links( $order_id, $tracking_item ) {
		$order_id = (int) $order_id;
		$base     = str_replace( '(?P<order_id>[\d]+)', $order_id, $this->rest_base );
		$links    = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s/%s', $this->namespace, $base, $tracking_item['tracking_id'] ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $base ) ),
			),
			'up' => array(
				'href' => rest_url( sprintf( '/%s/orders/%d', $this->namespace, $order_id ) ),
			),
		);
		return $links;
	}

	/**
	 * Get the Order Notes schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'tax',
			'type'       => 'shipment_tracking',
			'properties' => array(
				'tracking_id' => array(
					'description' => __( 'Unique identifier for shipment tracking.', 'woocommerce-shipment-tracking' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'tracking_provider' => array(
					'description' => __( 'Tracking provider name.', 'woocommerce-shipment-tracking' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => false,
				),
				'custom_tracking_provider' => array(
					'description' => __( 'Custom tracking provider name.', 'woocommerce-shipment-tracking' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'readonly'    => false,
				),
				'custom_tracking_link' => array(
					'description' => __( 'Custom tracking provider link.', 'woocommerce-shipment-tracking' ),
					'type'        => 'url',
					'context'     => array( 'edit' ),
					'readonly'    => false,
				),
				'tracking_number' => array(
					'description' => __( 'Tracking number.', 'woocommerce-shipment-tracking' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => false,
				),
				'date_shipped' => array(
					'description' => __( 'Date when package was shipped.', 'woocommerce-shipment-tracking' ),
					'type'        => 'date',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => false,
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}
}