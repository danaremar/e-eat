<section id="content_trackship_dashboard" class="inner_tab_section">
	<div class="tab_inner_container">		
		<?php
		$trackship = WC_Advanced_Shipment_Tracking_Trackship::get_instance();
		$admin = WC_Advanced_Shipment_Tracking_Admin::get_instance();		
		$completed_order_with_tracking = $trackship->completed_order_with_tracking();		
		$completed_order_with_zero_balance = $trackship->completed_order_with_zero_balance();							
		$completed_order_with_do_connection = $trackship->completed_order_with_do_connection();
		if($completed_order_with_tracking > 0 || $completed_order_with_zero_balance > 0 || $completed_order_with_do_connection > 0){
		$total_orders = $completed_order_with_tracking + $completed_order_with_zero_balance + $completed_order_with_do_connection;	
		?>
		<div class="trackship-notice">
			<p><?php echo sprintf(__('You have %s Shipped Orders from the last 30 days that you can bulk send to <a href="javascript:void(0);" class="tool_link">Get Shipment Status</a>', 'woo-advanced-shipment-tracking'),$total_orders ); ?></p>
		</div>			
		<?php } ?>				
		<form method="post" id="wc_ast_trackship_form" action="" enctype="multipart/form-data">
			<div class="outer_form_table border_0">
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php _e( 'Settings', 'woocommerce' ); ?></h3>
							</td>					
						</tr>
					</tbody>
				</table>		
				<?php $admin->get_html_ul( $trackship->get_trackship_general_data() ); ?>
				
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php _e( 'Automation', 'woo-advanced-shipment-tracking' ); ?></h3>
							</td>					
						</tr>
					</tbody>
				</table>		
				<?php $admin->get_html_ul( $trackship->get_trackship_automation_data() ); ?>
				
				<table class="form-table">
					<tbody>
						<tr valign="top">						
							<td class="button-column">
								<div class="submit">								
									<button name="save" class="button-primary woocommerce-save-button btn_green2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
									<div class="spinner"></div>								
									<?php wp_nonce_field( 'wc_ast_trackship_form', 'wc_ast_trackship_form_nonce' );?>
									<input type="hidden" name="action" value="wc_ast_trackship_form_update">
								</div>	
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>	
	</div>	
	<?php include 'trackship_sidebar.php'; ?>		
</section>