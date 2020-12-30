<div class="tracking-header">
	<?php echo do_action("trackship_tracking_header_before",$order_id, $tracker, $provider_name, $tracking_number);?>
	<div class="provider_image_div" style="<?php if($hide_tracking_provider_image == 1) { echo 'display:none'; };  ?>">
		<img class="provider_image" src="<?php echo $src; ?>">
	</div>
	<div class="tracking_number_div">
		<ul>			
			<li>
				<?php echo apply_filters( 'ast_provider_title', esc_html( $provider_name )); ?>:</span> 
				<?php if($wc_ast_link_to_shipping_provider == 1 && $tracking_number_url != ''){ ?>
					<a href="<?php echo $tracking_number_url; ?>" target="blank"><strong><?php echo $tracking_number; ?></strong></a>	
				<?php } else{ ?>
					<strong><?php echo $tracking_number; ?></strong>	
				<?php } ?>
			</li>
		</ul>
	</div>					
	<h1 class="shipment_status_heading <?php echo $tracker->ep_status; ?>">
		<?php echo apply_filters("trackship_status_filter",$tracker->ep_status);?>
	</h1>	
	<span class="tracking-number">
		<?php _e( 'Est. Delivery Date', 'woo-advanced-shipment-tracking' ); ?>: <strong>
		<?php 
		if($tracker->est_delivery_date){
			echo $day; ?>, <?php echo  date('M d', strtotime($tracker->est_delivery_date));
		} else{
			echo 'N/A';
		} ?></strong>				
	</span>	
	<?php
	if($tracker->ep_status == 'pending_trackship' || $tracker->ep_status == 'pending'){
		if($tracker->ep_status == 'pending'){
			$pending_message = __( 'Tracking information is not available, please try again in a few hour.', 'woo-advanced-shipment-tracking' );
		} else{
			$pending_message = __( 'Tracking information is not available, please try again in a few minutes.', 'woo-advanced-shipment-tracking' );
		}
	?>
		<p class="pending_message"><?php echo apply_filters( "trackship_pending_status_message", $pending_message, $tracker->ep_status );?></p>
	<?php } ?>
</div>