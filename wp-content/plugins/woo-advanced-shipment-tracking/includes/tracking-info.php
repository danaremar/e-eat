<?php
/**
 * Adds a tracking number to an order.
 *
 * @param int         $order_id        		The order id of the order you want to
 *                                     		attach this tracking number to.
 * @param string      $tracking_number 		The tracking number.
 * @param string      $tracking_provider	The tracking provider name.
 * @param int         $date_shipped    		The timestamp of the shipped date.
 *                                     		This is optional, if not set it will
 *                                     		use current time.
 * @param int 		  $status_shipped		0=no,1=shipped,2=partial shipped(if partial shipped order status is enabled)
 */
 
function ast_insert_tracking_number($order_id, $tracking_number, $tracking_provider, $date_shipped = null, $status_shipped = 0){	
	$args = array(
		'tracking_provider'     => $tracking_provider,		
		'tracking_number'       => $tracking_number,
		'date_shipped'          => $date_shipped,
		'status_shipped'		=> $status_shipped,
	);	
	$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
	$ast->insert_tracking_item( $order_id, $args );	
}

/**
 * Adds a tracking number to an order.
 *
 * @param int         $order_id        		The order id of the order you want to
 *                                     		attach this tracking number to.
 * @param string      $tracking_number 		The tracking number.
 * @param string      $tracking_provider	The tracking provider slug.
 * @param int         $date_shipped    		The timestamp of the shipped date.
 *                                     		This is optional, if not set it will
 *                                     		use current time.
 * @param int 		  $status_shipped		0=no,1=shipped,2=partial shipped(if partial shipped order status is enabled)
 */
 
function ast_add_tracking_number($order_id, $tracking_number, $tracking_provider, $date_shipped = null, $status_shipped = 0){	
	$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
	$args = array(
		'tracking_provider'     => $tracking_provider,		
		'tracking_number'       => $tracking_number,
		'date_shipped'          => $date_shipped,
		'status_shipped'		=> $status_shipped,
	);	
	$ast->add_tracking_item( $order_id, $args );	
}

/**
 * Get a tracking information for an order.
 *
 * @param int         $order_id        		The order id of the order you want to
 *                                     		get tracking info. 
 */
if (!function_exists('ast_get_tracking_items')){
	function ast_get_tracking_items($order_id){
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$tracking_items = $ast->get_tracking_items( $order_id, true );	
		return $tracking_items;
	}
}
?>