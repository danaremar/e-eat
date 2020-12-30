<section id="content_status_notifications" class="inner_tab_section">
	<div class="tab_inner_container">
		<h3 class="border0_heading"><?php _e('Shipment Status Email Notifications', 'woo-advanced-shipment-tracking'); ?></h3>
		<div class="outer_form_table">			
			<?php 
				$ast = new WC_Advanced_Shipment_Tracking_Actions;	
				
				$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings'); 				
				
				$wcast_enable_intransit_email = $ast->get_option_value_from_array('wcast_intransit_email_settings','wcast_enable_intransit_email','');
				
				$wcast_enable_onhold_email = $ast->get_option_value_from_array('wcast_onhold_email_settings','wcast_enable_onhold_email','');
								
				$wcast_enable_outfordelivery_email = $ast->get_option_value_from_array('wcast_outfordelivery_email_settings','wcast_enable_outfordelivery_email','');
				
				$wcast_enable_failure_email = $ast->get_option_value_from_array('wcast_failure_email_settings','wcast_enable_failure_email','');
				
				$wcast_enable_delivered_status_email = $ast->get_option_value_from_array('wcast_delivered_email_settings','wcast_enable_delivered_status_email','');
				
				$wcast_enable_returntosender_email = $ast->get_option_value_from_array('wcast_returntosender_email_settings','wcast_enable_returntosender_email','');
								
				$wcast_enable_availableforpickup_email = $ast->get_option_value_from_array('wcast_availableforpickup_email_settings','wcast_enable_availableforpickup_email','');
				
				$wcast_enable_late_shipments_admin_email = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_enable_late_shipments_admin_email','');
			?>		
			<table class="form-table shipment-status-email-table">
				<tbody>
					<tr class="<?php if($wcast_enable_intransit_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_intransit_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_intransit_email" name="wcast_enable_intransit_email" data-settings="wcast_intransit_email_settings" type="checkbox" <?php if($wcast_enable_intransit_email == 1) { echo 'checked'; } ?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_intransit_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_intransit_customizer_email::get_customizer_url('custom_shipment_status_email','in_transit','notifications') ?>" class="shipment-status-label in-transit woocommerce-help-tip tipTip" title="<?php _e('The shipment was accepted by the shipping provider and its on the way.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('In Transit', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
						<td class="forminp">
							<a class="edit_customizer_a" href="<?php echo wcast_intransit_customizer_email::get_customizer_url('custom_shipment_status_email','in_transit','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>
					<tr class="<?php if($wcast_enable_onhold_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_onhold_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_onhold_email" name="wcast_enable_onhold_email" data-settings="wcast_onhold_email_settings" type="checkbox" <?php if($wcast_enable_onhold_email == 1) { echo 'checked'; } ?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_onhold_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_onhold_customizer_email::get_customizer_url('custom_shipment_status_email','on_hold','notifications') ?>" class="shipment-status-label on-hold woocommerce-help-tip tipTip" title="<?php _e('The shipment is On Hold.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('On Hold', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
						<td class="forminp">
							<a class="edit_customizer_a" href="<?php echo wcast_onhold_customizer_email::get_customizer_url('custom_shipment_status_email','on_hold','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>
					<tr class="<?php if($wcast_enable_returntosender_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_returntosender_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_returntosender_email" name="wcast_enable_returntosender_email" data-settings="wcast_returntosender_email_settings" type="checkbox" <?php if($wcast_enable_returntosender_email == 1) { echo 'checked'; } ?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_returntosender_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_returntosender_customizer_email::get_customizer_url('custom_shipment_status_email','return_to_sender','notifications') ?>" class="shipment-status-label return-to-sender woocommerce-help-tip tipTip" title="<?php _e('Shipment is returned to sender.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('Return To Sender', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
						<td class="forminp">
							<a class="edit_customizer_a" href="<?php echo wcast_returntosender_customizer_email::get_customizer_url('custom_shipment_status_email','return_to_sender','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>
					<tr class="<?php if($wcast_enable_availableforpickup_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_availableforpickup_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_availableforpickup_email" name="wcast_enable_availableforpickup_email" data-settings="wcast_availableforpickup_email_settings" type="checkbox" <?php if($wcast_enable_availableforpickup_email == 1) { echo 'checked'; } ?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_availableforpickup_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_availableforpickup_customizer_email::get_customizer_url('custom_shipment_status_email','available_for_pickup','notifications') ?>" class="shipment-status-label available-for-pickup woocommerce-help-tip tipTip" title="<?php _e('The shipment is ready to by picked up.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('Available For Pickup', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
						<td class="forminp">
							<a class="edit_customizer_a" href="<?php echo wcast_availableforpickup_customizer_email::get_customizer_url('custom_shipment_status_email','available_for_pickup','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>
					<tr class="<?php if($wcast_enable_outfordelivery_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_outfordelivery_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_outfordelivery_email" name="wcast_enable_outfordelivery_email" data-settings="wcast_outfordelivery_email_settings" type="checkbox" <?php if($wcast_enable_outfordelivery_email == 1) { echo 'checked'; } ?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_outfordelivery_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_outfordelivery_customizer_email::get_customizer_url('custom_shipment_status_email','out_for_delivery','notifications') ?>" class="shipment-status-label out-for-delivery woocommerce-help-tip tipTip" title="<?php _e('Carrier is about to deliver the shipment.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('Out For delivery', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
						<td class="forminp">
							<a class="edit_customizer_a" href="<?php echo wcast_outfordelivery_customizer_email::get_customizer_url('custom_shipment_status_email','out_for_delivery','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>
					<tr class="<?php if($wcast_enable_delivered_status_email == 1 && $wcast_enable_delivered_email['enabled'] != 'yes'){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_delivered_status_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_delivered_status_email" name="wcast_enable_delivered_status_email" data-settings="wcast_delivered_email_settings" type="checkbox" <?php if($wcast_enable_delivered_status_email == 1 && $wcast_enable_delivered_email['enabled'] != 'yes') { echo 'checked'; } ?> <?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ echo 'disabled'; }?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_delivered_status_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_delivered_customizer_email::get_customizer_url('custom_shipment_status_email','delivered','notifications') ?>" class="shipment-status-label delivered-status woocommerce-help-tip tipTip" title="<?php _e('The shipment was delivered successfully.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('Delivered', 'woo-advanced-shipment-tracking'); ?></a>								
						</td>
						<td class="forminp">
							<?php if($wcast_enable_delivered_email['enabled'] === 'yes' && get_option('wc_ast_status_delivered') == 1){ ?>
							<p class="delivered_message"><?php _e("You already have delivered order status email enabled, to enable this email you'll need to disable the delivered order status email in settings.", 'woo-advanced-shipment-tracking'); ?></p>
							<?php } ?>
							<a class="edit_customizer_a" href="<?php echo wcast_delivered_customizer_email::get_customizer_url('custom_shipment_status_email','delivered','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>
					<tr class="<?php if($wcast_enable_failure_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
						<td class="forminp">
							<span class="shipment_status_toggle">								
								<input type="hidden" name="wcast_enable_failure_email" value="0"/>
								<input class="tgl tgl-flat" id="wcast_enable_failure_email" name="wcast_enable_failure_email" data-settings="wcast_failure_email_settings" type="checkbox" <?php if($wcast_enable_failure_email == 1) { echo 'checked'; } ?> value="yes"/>
								<label class="tgl-btn" for="wcast_enable_failure_email"></label>	
							</span>
						</td>
						<td class="forminp status-label-column">
							<a href="<?php echo wcast_failure_customizer_email::get_customizer_url('custom_shipment_status_email','failure','notifications') ?>" class="shipment-status-label failed-attempt woocommerce-help-tip tipTip" title="<?php _e('Carrier attempted to deliver the package but failed.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('Failed Attempt', 'woo-advanced-shipment-tracking'); ?></a>
						</td>
						<td class="forminp">
							<a class="edit_customizer_a" href="<?php echo wcast_failure_customizer_email::get_customizer_url('custom_shipment_status_email','failure','notifications') ?>"><?php _e('edit email', 'woocommerce'); ?></a>
						</td>
					</tr>					
				</tbody>
			</table>				
		</div>
		
		<?php do_action( 'after_shipment_status_email_notifications' ); ?>
		<h3 class="border0_heading"><?php _e('Admin Notifications', 'woo-advanced-shipment-tracking'); ?></h3>
		<form method="post" id="trackship_late_shipments_form" action="" enctype="multipart/form-data">
			<div class="outer_form_table">
				<table class="form-table shipment-status-email-table">
					<tbody>
						<tr class="<?php if($wcast_enable_late_shipments_admin_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
							<td class="forminp">
								<span class="shipment_status_toggle">								
									<input type="hidden" name="wcast_enable_late_shipments_admin_email" value="0"/>
									<input class="tgl tgl-flat" id="wcast_enable_late_shipments_admin_email" name="wcast_enable_late_shipments_admin_email" data-settings="late_shipments_email_settings" type="checkbox" <?php if($wcast_enable_late_shipments_admin_email == 1) { echo 'checked'; } ?> value="1"/>
									<label class="tgl-btn" for="wcast_enable_late_shipments_admin_email"></label>	
								</span>
							</td>
							<td class="forminp status-label-column">
								<a href="javascript:void(0);" class="shipment-status-label late-shipments woocommerce-help-tip tipTip" title="<?php _e('If a shipment reached the number of days that you define, and the shipment is not "delivered" or "Returned to Sender" than email will trigger.', 'woo-advanced-shipment-tracking'); ?>"><?php _e('Late Shipments', 'woo-advanced-shipment-tracking'); ?></a>
							</td>
							<td class="forminp">
								<a class="edit_customizer_a late_shipments_a" href="javascript:void(0);"><?php _e('edit email', 'woocommerce'); ?></a>
							</td>
						</tr>
					</tbody>
				</table>
			<?php 
			$late_shipments_email_settings = get_option('late_shipments_email_settings');
			$wcast_late_shipments_days = isset( $late_shipments_email_settings['wcast_late_shipments_days'] ) ? $late_shipments_email_settings['wcast_late_shipments_days'] : '';
			$wcast_late_shipments_email_to = isset( $late_shipments_email_settings['wcast_late_shipments_email_to'] ) ? $late_shipments_email_settings['wcast_late_shipments_email_to'] : '';			
			$wcast_late_shipments_email_subject = isset( $late_shipments_email_settings['wcast_late_shipments_email_subject'] ) ? $late_shipments_email_settings['wcast_late_shipments_email_subject'] : '';			
			$wcast_late_shipments_email_content = isset( $late_shipments_email_settings['wcast_late_shipments_email_content'] ) ? $late_shipments_email_settings['wcast_late_shipments_email_content'] : '';
			$wcast_late_shipments_trigger_alert = isset( $late_shipments_email_settings['wcast_late_shipments_trigger_alert'] ) ? $late_shipments_email_settings['wcast_late_shipments_trigger_alert'] : '';			
			$wcast_late_shipments_daily_digest_time = isset( $late_shipments_email_settings['wcast_late_shipments_daily_digest_time'] ) ? $late_shipments_email_settings['wcast_late_shipments_daily_digest_time'] : ''; ?>
			
			
				<table class="form-table late-shipments-email-content-table hide_table">
					<tr class="">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('Late Shipment Days', 'woo-advanced-shipment-tracking'); ?></label>	
						</th>	
						<td class="forminp">
							<fieldset>
								<input class="input-text" type="number" name="wcast_late_shipments_days" id="wcast_late_shipments_days" min="1" value="<?php echo $wcast_late_shipments_days; ?>">
							</fieldset>
						</td>
					</tr>
					<tr class="">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('Recipient(s)', 'woocommerce'); ?></label>	
						</th>	
						<td class="forminp">
							<fieldset>
								<input class="input-text regular-input " type="text" name="wcast_late_shipments_email_to" id="wcast_late_shipments_email_to" placeholder="<?php _e('E.g. {admin_email}, admin@example.org', 'woo-advanced-shipment-tracking'); ?>" value="<?php echo $wcast_late_shipments_email_to; ?>">
							</fieldset>
						</td>
					</tr>
					<tr class="">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('Subject', 'woocommerce'); ?></label>	
						</th>	
						<td class="forminp">
							<fieldset>
								<input class="input-text regular-input " type="text" name="wcast_late_shipments_email_subject" id="wcast_late_shipments_email_subject" placeholder="<?php _e('Late shipment for order #{order_number}', 'woo-advanced-shipment-tracking'); ?>" value="<?php echo $wcast_late_shipments_email_subject; ?>">
							</fieldset>
						</td>
					</tr>
					<tr class="">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('Email content', 'woo-advanced-shipment-tracking'); ?></label>	
						</th>	
						<td class="forminp">
							<fieldset>
								<textarea name="wcast_late_shipments_email_content" id="wcast_late_shipments_email_content" placeholder="<?php _e('This order was shipped {shipment_length} days ago, the shipment status is {shipment_status} and its est. delivery date is {est_delivery_date}.', 'woo-advanced-shipment-tracking'); ?>"><?php echo $wcast_late_shipments_email_content; ?></textarea>
							</fieldset>						
							<span><?php _e('Available variables:', 'woo-advanced-shipment-tracking'); ?> {site_title} {admin_email} {customer_first_name} {customer_last_name} {customer_company_name} {customer_username} {order_number} {shipment_length} {shipment_status} {est_delivery_date}</span>
						</td>
					</tr>
					<?php 
					$send_time_array = array();										
					for ( $hour = 0; $hour < 24; $hour++ ) {
						for ( $min = 0; $min < 60; $min = $min + 30 ) {
							$this_time = date( 'H:i', strtotime( "$hour:$min" ) );
							$send_time_array[ $this_time ] = $this_time;
						}	
					} ?>
					<tr class="">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('Trigger Alert', 'woo-advanced-shipment-tracking'); ?></label>	
						</th>	
						<td class="forminp">
							<label class="" for="trigger_alert_as_it_happens">												
								<input type="radio" id="trigger_alert_as_it_happens" name="wcast_late_shipments_trigger_alert" value="as_it_happens" <?php if($wcast_late_shipments_trigger_alert == 'as_it_happens')echo 'checked'; ?>>
								<span class=""><?php _e('As it Happens', 'woo-advanced-shipment-tracking'); ?></span>	
							</label>
							<label class="" for="trigger_alert_daily_digest_on">												
								<input type="radio" id="trigger_alert_daily_digest_on" name="wcast_late_shipments_trigger_alert" value="daily_digest_on" <?php if($wcast_late_shipments_trigger_alert == 'daily_digest_on')echo 'checked'; ?>>
								<span class=""><?php _e('Daily Digest on', 'woo-advanced-shipment-tracking'); ?></span>								
							</label>
							<select class="select daily_digest_time" name="wcast_late_shipments_daily_digest_time"> 
								<?php foreach((array)$send_time_array as $key1 => $val1 ){ ?>
									<option <?php if($wcast_late_shipments_daily_digest_time == $key1)echo 'selected'; ?> value="<?php echo $key1?>" ><?php echo $val1; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<button name="save" class="button-primary woocommerce-save-button btn_green2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
							<div class="spinner"></div>								
							<?php wp_nonce_field( 'ts_late_shipments_email_form', 'ts_late_shipments_email_form_nonce' );?>
							<input type="hidden" name="action" value="ts_late_shipments_email_form_update">
						</td>
					</tr>
				</table>			
			</div>	
		</form>	
	</div>		
	<?php include 'trackship_sidebar.php'; ?>	
</section>