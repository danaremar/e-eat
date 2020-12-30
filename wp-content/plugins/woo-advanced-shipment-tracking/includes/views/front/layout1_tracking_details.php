<div class="tracking-details" style="">	
	<?php if(!empty($tracking_details_by_date)){ ?>
	<div class="shipment_progress_heading_div">	               				
		<h4 class="h4-heading text-uppercase"><?php _e( 'Tracking Details', 'woo-advanced-shipment-tracking' ); ?></h4>					
	</div>	
	<?php if(!empty($tracking_destination_details_by_date)){ ?>
		<div class="tracking_destination_details_by_date">
			<h4 style=""><?php _e( 'Destination Details', 'woo-advanced-shipment-tracking' ); ?></h4>
			<ul class="timeline">	
			<?php 			
			foreach($tracking_destination_details_by_date as $date => $date_details){				
				foreach($date_details as $key => $value){
				?>
				<li>
					<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
					<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
				</li>					
			<?php } } ?>								
			</ul>	
		</div>
		<?php } ?>
		
		<div class="tracking_details_by_date">
			<?php if(!empty($tracking_destination_details_by_date)){ ?>
				<h4 class="" style=""><?php _e( 'Origin Details', 'woo-advanced-shipment-tracking' ); ?></h4>
			<?php } ?> 
			<ul class="timeline">	
			<?php 
			foreach($tracking_details_by_date as $date => $date_details){				
				foreach($date_details as $key => $value){
				?>
				<li>
					<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
					<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
				</li>						
			<?php }  } ?>
			</ul>	
		</div>		
	<?php } ?>
</div>