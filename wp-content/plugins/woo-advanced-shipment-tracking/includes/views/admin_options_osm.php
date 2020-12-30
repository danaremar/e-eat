<form method="post" id="wc_ast_order_status_form" action="" enctype="multipart/form-data">
	<div class="custom_order_status_section">							
		<table class="form-table order-status-table">
			<tbody>							
				<tr valign="top" class="delivered_row <?php if(!get_option('wc_ast_status_delivered')){echo 'disable_row'; } ?>">
					<td class="forminp">
						<input type="hidden" name="wc_ast_status_delivered" value="0"/>
						<input class="tgl tgl-flat order_status_toggle" id="wc_ast_status_delivered" name="wc_ast_status_delivered" type="checkbox" <?php if(get_option('wc_ast_status_delivered')){echo 'checked'; } ?> value="1"/>
						<label class="tgl-btn" for="wc_ast_status_delivered"></label>		
					</td>
					<td class="forminp status-label-column">
						<span class="order-label wc-delivered">
							<?php 
							if(get_option('wc_ast_status_delivered')){
								_e( wc_get_order_status_name( 'delivered' ), 'woo-advanced-shipment-tracking' );	
							} else{
								_e( 'Delivered', 'woo-advanced-shipment-tracking' );
							} ?>
						</span>
					</td>								
					<td class="forminp">							
						<?php
						$wcast_enable_delivered_email = get_option('woocommerce_customer_delivered_order_settings');
						
						$delivered_checked = '';
						
						if(isset( $wcast_enable_delivered_email['enabled'] )){
							if( $wcast_enable_delivered_email['enabled'] == 'yes' || $wcast_enable_delivered_email['enabled'] == 1 ){
								$delivered_checked = 'checked';
							}
						}
						?>
						<fieldset>
							<input class="input-text regular-input color_input" type="text" name="wc_ast_status_label_color" id="wc_ast_status_label_color" style="" value="<?php echo get_option('wc_ast_status_label_color','#59c889')?>" placeholder="">
							<select class="select custom_order_color_select" id="wc_ast_status_label_font_color" name="wc_ast_status_label_font_color">	
								<option value="#fff" <?php if(get_option('wc_ast_status_label_font_color','#fff') == '#fff'){ echo 'selected'; }?>><?php _e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
								<option value="#000" <?php if(get_option('wc_ast_status_label_font_color','#fff') == '#000'){ echo 'selected'; }?>><?php _e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
							</select>
							<label class="send_email_label">
								<input type="hidden" name="wcast_enable_delivered_email" value="0"/>
								<input type="checkbox" name="wcast_enable_delivered_email" id="wcast_enable_delivered_email" <?php echo $delivered_checked; ?> value="1" class="enable_order_status_email_input"><?php _e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?>
							</label>
							<a class='settings_edit' href="<?php echo wcast_initialise_customizer_email::get_customizer_url( 'custom_order_status_email','delivered' ); ?>"><?php _e( 'edit email', 'woocommerce' ) ?></a>
						</fieldset>
					</td>
				</tr>
				<tr valign="top" class="partial_shipped_row <?php if(!get_option('wc_ast_status_partial_shipped')){echo 'disable_row'; } ?>">	
					<td class="forminp">
						<input type="hidden" name="wc_ast_status_partial_shipped" value="0"/>
						<input class="tgl tgl-flat order_status_toggle" id="wc_ast_status_partial_shipped" name="wc_ast_status_partial_shipped" type="checkbox" <?php if(get_option('wc_ast_status_partial_shipped')){echo 'checked'; } ?> value="1"/>
						<label class="tgl-btn" for="wc_ast_status_partial_shipped"></label>	
					</td>
					<td class="forminp status-label-column">
						<span class="order-label wc-partially-shipped">
							<?php 
							if(get_option('wc_ast_status_partial_shipped')){
								_e( wc_get_order_status_name( 'partial-shipped' ), 'woo-advanced-shipment-tracking' );	
							} else{
								_e( 'Partially Shipped', 'woo-advanced-shipment-tracking' );
							} ?>								
						</span>
					</td>												
					<td class="forminp">								
						<?php
						$wcast_enable_partial_shipped_email = get_option('woocommerce_customer_partial_shipped_order_settings');
						
						$partial_checked = '';	
						
						if(isset( $wcast_enable_partial_shipped_email['enabled'] )){
							if( $wcast_enable_partial_shipped_email['enabled'] == 'yes' || $wcast_enable_partial_shipped_email['enabled'] == 1 ){
								$partial_checked = 'checked';
							}
						}
						
						?>
						<fieldset>
							<input class="input-text regular-input color_input" type="text" name="wc_ast_status_partial_shipped_label_color" id="wc_ast_status_partial_shipped_label_color" style="" value="<?php echo get_option('wc_ast_status_partial_shipped_label_color','#1e73be')?>" placeholder="">
							<select class="select custom_order_color_select" id="wc_ast_status_partial_shipped_label_font_color" name="wc_ast_status_partial_shipped_label_font_color">									
								<option value="#fff" <?php if(get_option('wc_ast_status_partial_shipped_label_font_color','#fff') == '#fff'){ echo 'selected'; }?>><?php _e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
								<option value="#000" <?php if(get_option('wc_ast_status_partial_shipped_label_font_color','#fff') == '#000'){ echo 'selected'; }?>><?php _e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
							</select>
							<label class="send_email_label">
								<input type="hidden" name="wcast_enable_partial_shipped_email" value="0"/>
								<input type="checkbox" name="wcast_enable_partial_shipped_email" id="wcast_enable_partial_shipped_email"class="enable_order_status_email_input"  <?php echo $partial_checked; ?> value="1"><?php _e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?></label>
								<a class='settings_edit' href="<?php echo wcast_partial_shipped_customizer_email::get_customizer_url('custom_order_status_email','partially_shipped'); ?>"><?php _e( 'edit email', 'woocommerce' ) ?></a>
						</fieldset>
					</td>
				</tr>
				<tr valign="top" class="updated_tracking_row <?php if(!get_option('wc_ast_status_updated_tracking')){echo 'disable_row'; } ?>">		
					<td class="forminp">
						<input type="hidden" name="wc_ast_status_updated_tracking" value="0"/>
						<input class="tgl tgl-flat order_status_toggle" id="wc_ast_status_updated_tracking" name="wc_ast_status_updated_tracking" type="checkbox" <?php if(get_option('wc_ast_status_updated_tracking')){echo 'checked'; } ?> value="1"/>
						<label class="tgl-btn" for="wc_ast_status_updated_tracking"></label>	
					</td>
					<td class="forminp status-label-column">
						<span class="order-label wc-updated-tracking">
							<?php 
							if(get_option('wc_ast_status_updated_tracking')){
								_e( wc_get_order_status_name( 'updated-tracking' ), 'woo-advanced-shipment-tracking' );	
							} else{
								_e( 'Updated Tracking', 'woo-advanced-shipment-tracking' );
							} ?>								
						</span>
					</td>						
					<td class="forminp">							
						<?php
						$wcast_enable_updated_tracking_email = get_option('woocommerce_customer_updated_tracking_order_settings');
						
						$updated_tracking_checked = '';	
						
						if(isset( $wcast_enable_updated_tracking_email['enabled'] )){
							if( $wcast_enable_updated_tracking_email['enabled'] == 'yes' || $wcast_enable_updated_tracking_email['enabled'] == 1 ){
								$updated_tracking_checked = 'checked';
							}
						} ?>
						<fieldset>
							<input class="input-text regular-input color_input" type="text" name="wc_ast_status_updated_tracking_label_color" id="wc_ast_status_updated_tracking_label_color" style="" value="<?php echo get_option('wc_ast_status_updated_tracking_label_color','#23a2dd')?>" placeholder="">
							<select class="select custom_order_color_select" id="wc_ast_status_updated_tracking_label_font_color" name="wc_ast_status_updated_tracking_label_font_color">									
								<option value="#fff" <?php if(get_option('wc_ast_status_updated_tracking_label_font_color','#fff') == '#fff'){ echo 'selected'; }?>><?php _e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
								<option value="#000" <?php if(get_option('wc_ast_status_updated_tracking_label_font_color','#fff') == '#000'){ echo 'selected'; }?>><?php _e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
							</select>
							<label class="send_email_label">
								<input  type="hidden" name="wcast_enable_updated_tracking_email" value="0"/>
								<input type="checkbox" name="wcast_enable_updated_tracking_email" id="wcast_enable_updated_tracking_email" class="enable_order_status_email_input" <?php echo $updated_tracking_checked; ?> value="1"><?php _e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?>
							</label>
							<a class='settings_edit' href="<?php echo wcast_updated_tracking_customizer_email::get_customizer_url('custom_order_status_email','updated_tracking'); ?>"><?php _e( 'edit email', 'woocommerce' ) ?></a>
						</fieldset>
					</td>
				</tr>
				<?php do_action("ast_orders_status_column_end"); ?>	
			</tbody>
		</table>	
		<?php wp_nonce_field( 'wc_ast_order_status_form', 'wc_ast_order_status_form_nonce' );?>	
		<input type="hidden" name="action" value="wc_ast_custom_order_status_form_update">									
	</div>	
</form>			