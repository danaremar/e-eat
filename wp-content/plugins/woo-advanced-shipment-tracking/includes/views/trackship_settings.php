<div class="woocommerce trackship_admin_layout">
	<div class="trackship_admin_content" >
		<div class="trackship_nav_div">	
			<?php $wc_ast_api_key = get_option('wc_ast_api_key'); 
				if($wc_ast_api_key){
			?>
			<input id="tab_trackship_dashboard" type="radio" name="tabs" class="tab_input" data-label="<?php _e('Settings', 'woocommerce'); ?>" data-tab="trackship" checked>
			<label for="tab_trackship_dashboard" class="tab_label first_label"><?php _e('Settings', 'woocommerce'); ?></label>					
			<input id="tab_tracking_page" type="radio" name="tabs" class="tab_input" data-label="<?php _e('Tracking Page', 'woo-advanced-shipment-tracking'); ?>" data-tab="tracking-page" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'tracking-page'){ echo 'checked'; } ?>>
			<label for="tab_tracking_page" class="tab_label tracking_page_label"><?php _e('Tracking Page', 'woo-advanced-shipment-tracking'); ?></label>
			
			<input id="tab_status_notifications" type="radio" name="tabs" class="tab_input" data-label="<?php _e('Notifications', 'woo-advanced-shipment-tracking'); ?>" data-tab="notifications" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'notifications'){ echo 'checked'; } ?>>
			<label for="tab_status_notifications" class="tab_label"><?php _e('Notifications', 'woo-advanced-shipment-tracking'); ?></label>
			
			<input id="tab_tools" type="radio" name="tabs" class="tab_input" data-label="<?php _e('Tools', 'woo-advanced-shipment-tracking'); ?>" data-tab="tools" <?php if(isset($_GET['tab']) && $_GET['tab'] == 'tools'){ echo 'checked'; } ?>>
			<label for="tab_tools" class="tab_label"><?php _e('Tools', 'woo-advanced-shipment-tracking'); ?></label>			
			
			<?php } 
				if($wc_ast_api_key){								
					$url = 'https://my.trackship.info/wp-json/tracking/get_user_plan';								
					$args['body'] = array(
						'user_key' => $wc_ast_api_key,				
					);
					$response = wp_remote_post( $url, $args );
					if ( is_wp_error( $response ) ) {
						
					} else{
						$plan_data = json_decode($response['body']);					
					}					
			 
					require_once( 'admin_trackship_dashboard.php' );
					require_once( 'admin_tracking_page_settings.php' );
					require_once( 'admin_status_notifications.php' );
					require_once( 'admin_options_tools.php' );
				}
			?>			
		</div>                   					
   </div>				
</div> 