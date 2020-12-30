<?php
/**
 * Foodstore Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @package FoodStore/Functions
 * @version 1.1.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Update admin fields with missing prefix
 *
 * @since 1.1.4
 * @return void
 */
function wfs_update_114_add_prefix() {

	// Update enable_pickup to _wfs_enable_pickup
	if( get_option( 'enable_pickup' ) ) {
		$option_value = get_option( 'enable_pickup', 'yes' );
		add_option( '_wfs_enable_pickup', $option_value );
		delete_option( 'enable_pickup' );
	}

	// Update enable_delivery to _wfs_enable_delivery
	if( get_option( 'enable_delivery' ) ) {
		$option_value = get_option( 'enable_delivery', '' );
		add_option( '_wfs_enable_delivery', $option_value );
		delete_option( 'enable_delivery' );
	}

	// Update pickup_time_interval to _wfs_pickup_time_interval
	if( get_option( 'pickup_time_interval' ) ) {
		$option_value = get_option( 'pickup_time_interval', 30 );
		add_option( '_wfs_pickup_time_interval', $option_value );
		delete_option( 'pickup_time_interval' );
	}

	// Update delivery_time_interval to _wfs_delivery_time_interval
	if( get_option( 'delivery_time_interval' ) ) {
		$option_value = get_option( 'delivery_time_interval', 30 );
		add_option( '_wfs_delivery_time_interval', $option_value );
		delete_option( 'delivery_time_interval' );
	}
}

/** 
 * Remove unused admin options and reassign them
 * to other similar option key
 *
 * @since 1.1.4
 * @return void
 */
function wfs_update_114_reassign_deprecated_options() {

	// Remove store time format of 12 hrs and 24 hours and reassign it to WordPress time_format
	if( get_option( '_wfs_store_time_format' ) ) {
		$option_value = get_option( '_wfs_store_time_format', '12hrs' );
		if( '24hrs' == $option_value ) {
			update_option( 'time_format', 'H:i' );
		}
		delete_option( '_wfs_store_time_format' );
	}

	// Reassign 3 columns options to 2 columns
	if( get_option( '_wfs_listing_column_count' ) ) {
		$option_value = get_option( '_wfs_listing_column_count' );
		if( $option_value == '3' ) {
			update_option( '_wfs_listing_column_count', '2' );
		} 
	}
}