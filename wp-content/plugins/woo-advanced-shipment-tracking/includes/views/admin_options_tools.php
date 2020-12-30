<?php
/**
 * html code for tools tab
 */
?>
<section id="content_tools" class="inner_tab_section">
	<div class="tab_inner_container">		
		<div class="d_table" style="">			
			<div class="outer_form_table get_shipment_status_tool border_0">				
				<div class="trackship-notice bulk_shipment_status_success" style="display:none;">
					<p><?php echo __('Tracking info sent to Trackship for all Orders.', 'woo-advanced-shipment-tracking'); ?></p>
				</div>	
				<h3 class="ts_outer_section_heading"><?php _e( 'Tools', 'woo-advanced-shipment-tracking' ); ?></h3>
				<p class="get_shipment_status_tool_description"><?php _e( 'You can send all your orders from the last 30 days to get shipment status from TrackShip:', 'woo-advanced-shipment-tracking' ); ?></p>
				<?php 
				$trackship = WC_Advanced_Shipment_Tracking_Trackship::get_instance();
				$admin = WC_Advanced_Shipment_Tracking_Admin::get_instance();
				$admin->get_html( $trackship->get_trackship_bulk_actions_data() ); ?>							
			</div>			
		</div>		
	</div>
	<?php include 'trackship_sidebar.php'; ?>	
</section>