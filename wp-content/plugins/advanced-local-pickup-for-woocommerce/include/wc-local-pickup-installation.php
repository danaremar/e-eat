<?php
/**
 * ALP
 *
 * Class WC_Local_Pickup_admin
 * 
 * @author        WooThemes
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_Local_Pickup_install { 

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	* function callback for add not existing key in database.
	*
	*/
	function wclp_update_install_callback() {
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.1', '<') ){			
			$pickup_day_time = get_option('wclp_store_days');
			if(empty($pickup_day_time)){				
				$pickup_day_time_array = array(
					'monday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'tuesday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'wednesday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'thursday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'friday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),				
				);
				update_option( 'wclp_store_days', $pickup_day_time_array);			
			}			
			update_option( 'wclp_local_pickup', '1.1');	
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.2', '<') ){		
			$wclp_show_pickup_instraction = get_option('wclp_show_pickup_instruction');
			$opt = array(
				'display_in_processing_email' => get_option('wclp_show_address_email'),
				'display_in_order_received_page' => get_option('wclp_show_address_order_received'),
				'display_in_order_details_page' => get_option('wclp_show_address_order_my_account'),
			);
			update_option( 'wclp_show_pickup_instruction', wc_clean($opt));
			update_option( 'wclp_local_pickup', '1.2');	
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.3', '<') ){	
			$pickup_day_time = get_option('wclp_store_days');
			foreach($pickup_day_time as $day => $time){
				$pickup_day_time[$day]['wclp_store_hour'] = str_replace("am","",$pickup_day_time[$day]['wclp_store_hour']);
				$pickup_day_time[$day]['wclp_store_hour'] = str_replace("pm","",$pickup_day_time[$day]['wclp_store_hour']);
				$pickup_day_time[$day]['wclp_store_hour_end'] = str_replace("am","",$pickup_day_time[$day]['wclp_store_hour_end']);
				$pickup_day_time[$day]['wclp_store_hour_end'] = str_replace("pm","",$pickup_day_time[$day]['wclp_store_hour_end']);
			}
			
			$country_code = get_option( 'wclp_default_country', get_option('woocommerce_default_country') );		
			$split_country = explode( ":", $country_code );
			
			if(isset($split_country[0])){
				$store_country = $split_country[0];	
			} else{
				$store_country = '';
			}
			
			if(isset($split_country[1])){
				$store_state = $split_country[1];
			} else{
				$store_state   = '';
			}
			
			update_option( 'wclp_default_single_country', wc_clean($store_country));
			update_option( 'wclp_default_single_state', wc_clean($store_state));			
			update_option( 'wclp_store_days', wc_clean($pickup_day_time));
			update_option( 'wclp_local_pickup', '1.3');		
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.4', '<') ){			
			update_option( 'wclp_default_time_format', '24');	
			update_option( 'wclp_local_pickup', '1.4');	
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.5', '<') ){			
			$store_name = get_option('wclp_store_name', get_bloginfo( 'name' ));
			$store_address = get_option( 'wclp_store_address', get_option( 'woocommerce_store_address' ) );
			$store_address_2 = get_option( 'wclp_store_address_2', get_option( 'woocommerce_store_address_2' ) );
			$store_city = get_option( 'wclp_store_city', get_option( 'woocommerce_store_city' ) );
			$default_country = get_option('wclp_default_country', get_option( 'woocommerce_default_country' ) );
			$store_postcode = get_option('wclp_store_postcode', get_option( 'woocommerce_store_postcode' ) );
			$default_time_format = get_option('wclp_default_time_format', '12');
			$store_days = get_option('wclp_store_days', array());
			$store_instruction = get_option('wclp_store_instruction', '');
			
			// insert data in database.
			$data[1] = array(
				'store_name' => $store_name,
				'store_address' => $store_address ,
				'store_address_2' => $store_address_2,
				'store_city' => $store_city,
				'default_country' => $default_country ,
				'store_postcode' => $store_postcode,
				'store_phone' => '',
				'default_time_format' => $default_time_format,
				'store_days' => $store_days,
				'store_instruction' => $store_instruction,
			);
			update_option( 'wclp_pickup_locations', $data );
			
			$locations = get_option( 'wclp_pickup_locations' );
			$pickup_day_time = $locations[1]['store_days'];
			if(empty($pickup_day_time)){				
				$pickup_day_time_array = array(
					'monday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'tuesday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'wednesday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'thursday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),
					'friday' => array(
									'checked' => 1,
									'wclp_store_hour' => '09:00am',
									'wclp_store_hour_end' => '18:00pm',									
								),				
				);
				update_option( $pickup_day_time, (array)$pickup_day_time_array);
			}
			update_option( 'wclp_status_ready_pickup', '1');	
			update_option( 'wclp_status_picked_up', '1');	
			
			update_option('wclp_local_pickup', '1.5');		
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.6', '<') ){			
			
			//database functions
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
			
			$locations = get_option( 'wclp_pickup_locations' );
			
			// insert data in database.
			$data = array(
				'store_name' => $locations[1]['store_name'],
				'store_address' => $locations[1]['store_address'] ,
				'store_address_2' => $locations[1]['store_address_2'],
				'store_city' => $locations[1]['store_city'],
				'store_country' => $locations[1]['default_country'] ,
				'store_postcode' => $locations[1]['store_postcode'],
				'store_phone' => $locations[1]['store_phone'],
				'store_time_format' => $locations[1]['default_time_format'],
				'store_days' => serialize($locations[1]['store_days']),
				'store_instruction' => $locations[1]['store_instruction'],
			);
			
			$wpdb->insert( $this->table, $data );
			
			update_option('wclp_local_pickup', '1.6');		
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.7', '<') ){			
			
			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix."alp_pickup_location";
			
			$data = array(
				'store_display_address' => '1',
				'store_display_address_2' => '1',
				'store_display_city' => '1',
				'store_display_country' => '1',
				'store_display_postcode' => '1',
				'store_display_phone' => '1',
			);
			$tabledata = $wpdb->get_row( sprintf("SELECT * FROM %s LIMIT 1", $this->table) );
			foreach( (array)$data as $key1 => $val1  ){
				if(!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( "ALTER TABLE %s ADD $key1 text NOT NULL", $this->table) );
				}
			}
			
			update_option('wclp_local_pickup', '1.7');		
		}
		
		if(version_compare(get_option( 'wclp_local_pickup', '1.0' ),'1.8', '<') ){			
			
			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix."alp_pickup_location";
			
			$tabledata = $wpdb->get_row( sprintf("SELECT * FROM %s LIMIT 1", $this->table) );

			if(!isset($tabledata->position)) {
				$wpdb->query( sprintf( "ALTER TABLE %s ADD position text NOT NULL", $this->table) );
			}
			
			update_option('wclp_local_pickup', '1.8');		
		}
	
	}
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Local_Pickup_admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}
