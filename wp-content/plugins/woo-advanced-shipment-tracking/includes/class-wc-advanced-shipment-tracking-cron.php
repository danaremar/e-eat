<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_Advanced_Shipment_Tracking_Cron {

	const CRON_HOOK = 'wc_ast_cron';

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

		// Add the count words cronjob
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {

			$cron_timing = get_option("wc_ast_api_cron_time", "wc_ast_1day");
			$send_time = get_option("wc_ast_api_run_time", "03:00");
			
			// Create a Date Time object when the cron should run for the first time
			$first_cron = new DateTime( date( 'Y-m-d' ) . $send_time.":00" , new DateTimeZone( wc_timezone_string() ) );
			
			$hr_min = explode(":",$send_time);
			if( $hr_min[0] <= date("H") ) $first_cron->modify( '+1 day' );			
			
			wp_schedule_event( $first_cron->format( 'U' ) + $first_cron->getOffset(), $cron_timing, self::CRON_HOOK );
		}
	}
}
