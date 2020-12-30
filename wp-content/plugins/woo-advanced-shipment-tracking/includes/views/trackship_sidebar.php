<?php
/**
 * html code for admin sidebar
 */
?>
<div class="zorem_admin_sidebar">
	<div class="zorem_admin_sidebar_inner">
		
		<?php $wc_ast_api_key = get_option('wc_ast_api_key'); ?>
		  	
		<div class="zorem-sidebar__section">
			<h3><?php _e( 'TrackShip Connection Status', 'woo-advanced-shipment-tracking' ); ?></h3>
			<div class="api_connected"><span class="dashicons dashicons-yes-alt"></span> <?php _e( 'Connected', 'woo-advanced-shipment-tracking' ); ?></div>
			<a href="https://trackship.info/my-account/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" class="button-primary btn_ts_sidebar" target="_blank" ><?php _e( 'Account Dashboard', 'woo-advanced-shipment-tracking' ); ?></a>
		</div>
		
		<div class="zorem-sidebar__section">
			<table class="sidebar_subscription_details">
				<tr>
					<td><?php _e( 'Trackers Balance', 'woo-advanced-shipment-tracking' ); ?></td>
					<td><strong><?php echo get_option('trackers_balance'); ?></strong></td>
				</tr>
				<tr>
					<td><?php _e( 'Subscription Plan', 'woo-advanced-shipment-tracking' ); ?></td>
					<td><strong><?php if(isset($plan_data->subscription_plan))echo $plan_data->subscription_plan; ?></strong></td>
				</tr>
			</table>			
			<a href="https://trackship.info/my-account/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" class="button-primary btn_ts_sidebar" target="_blank" ><?php _e( 'Upgrade Now', 'woo-advanced-shipment-tracking' ); ?></a>
		</div>	
		<?php if(!class_exists('SMS_for_WooCommerce')){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://www.zorem.com/products/sms-for-woocommerce/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/SMSWOO-sidebar-banner.png"></a>
		</div>
		<?php } ?>
	</div>
</div>