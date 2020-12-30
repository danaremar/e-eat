<style>	
	html{
		background-color:#fff;
		margin-top:0px !important;
	}
	.col.tracking-detail{
		margin: 0 auto;
	}
	<?php 
	if($border_color){ ?>
		.col.tracking-detail{
			border: 1px solid <?php echo $border_color; ?>;
		}
		body .col.tracking-detail .shipment-header{
			border-bottom: 1px solid <?php echo $border_color; ?>;
		}
		body .col.tracking-detail .trackship_branding{
			border-top: 1px solid <?php echo $border_color; ?>;
		}
	<?php }	?>
</style>		

<div class="tracking-detail col">
	<div class="shipment-header">
		<p class="shipment_heading"><?php _e( 'Shipment', 'woo-advanced-shipment-tracking' ); ?></p>
		<span><?php _e( 'Order', 'woocommerce' ); ?> <strong>#14696 </strong></span>		
	</div>
   <div class="tracking-header">
      <div class="provider_image_div" style="<?php if($hide_tracking_provider_image == 1) { echo 'display:none'; };  ?>">
         <img class="provider_image" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/shipment-provider-img/usps.png?v=3.1.1">
      </div>
      <div class="tracking_number_div">
         <ul>
            <li>
               USPS: 
			   <?php 
			   if($wc_ast_link_to_shipping_provider == 1) { ?>
					<a href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=9410803699300126968507" target="blank"><strong>9410803699300126968507</strong></a>		
			   <?php } else{ ?>
					<strong>9410803699300126968507</strong>
			   <?php } ?>               
            </li>
         </ul>
      </div>
      <h1 class="shipment_status_heading out_for_delivery">
         Out For Delivery	
      </h1>
      <span class="tracking-number">
      Est. Delivery Date: <strong>
      Thursday, Oct 01</strong>				
      </span>	
   </div>
   <div class="tracker-progress-bar <?php if($tracking_page_layout == 't_layout_1'){ echo 'tracking_layout_1'; } ?>">
      <div class="progress">
         <div class="progress-bar out_for_delivery" style="width: 67%;"></div>
      </div>
   </div>
   <div class="tracking-details" style="<?php if($hide_tracking_events == 1){ echo 'display:none'; } ?>">
      <div class="shipment_progress_heading_div">
         <h4 class="h4-heading text-uppercase">Tracking Details</h4>
      </div>
      <div class="tracking_details_by_date">
        <ul class="timeline">
			<li>
				<strong>October 1, 2020 07:59</strong>
				<p>Out for Delivery, Expected Delivery by 8:00pm - EAST HARTFORD, CT - <span>EAST HARTFORD</span></p>
			</li>
			<li>
				<strong>October 1, 2020 07:48</strong>
				<p>Arrived at Post Office - HARTFORD, CT - <span>HARTFORD</span></p>
			</li> 
			<li>
				<strong>October 1, 2020 00:10</strong>
				<p>Arrived at USPS Regional Destination Facility - SPRINGFIELD MA NETWORK DISTRIBUTION CENTER,  - <span>SPRINGFIELD MA NETWORK DISTRIBUTION CENTER</span></p>
			</li>
			<li>
				<strong>September 30, 2020 00:00</strong>
				<p>In Transit to Next Facility<span></span></p>
			</li>   
			<li>
				<strong>September 29, 2020 13:12</strong>
				<p>USPS in possession of item - SHELDON, WI - <span>SHELDON</span></p>
			</li>
		</ul>
      </div>
   </div>
   <div class="trackship_branding" style="<?php if($remove_trackship_branding == 1){ echo 'display:none'; }?>">
      <p>Shipment Tracking info by <a href="https://trackship.info" title="TrackShip" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png"></a></p>
   </div>
</div>