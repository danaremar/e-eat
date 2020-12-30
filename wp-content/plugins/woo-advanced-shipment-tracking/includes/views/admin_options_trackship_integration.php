<?php
/**
 * html code for trackship tab
 */
wp_enqueue_script( 'trackship_script' );
?>
<section id="content3" class="tab_section">
	<div class="d_table" style="">
		<div class="tab_inner_container">
			<div class="section-content trackship_section">
						<div class="ts_row ts_header_row">
							<div class="ts_col_6">
								<div class="ts_col_inner">
									<h1 class="ts_landing_header">Your Post-Shipping &amp; Delivery Autopilot</h1>
									<p class="ts_landing_description">Trackship is a Multi-Carrier Shipment Tracking API that seamlessly integrates into your WooCommerce store and auto-tracks your shipments, automates your orders workflow, reduces the time spent on customer service and lets you provide a superior post-purchase experience to your customers.</p>
									<a href="javascript:void(0);" target="_self" class="button-primary btn_green2 btn_large open_ts_video"><span><?php _e('Watch Video', 'woo-advanced-shipment-tracking'); ?></span><span class="dashicons dashicons-video-alt3"></span></a>
									<a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=tspage" target="_blank" class="button-primary btn_green2 btn_large"><span><?php _e('Start your free trial', 'woo-advanced-shipment-tracking'); ?></span></a>
								</div>
							</div>
							<div class="ts_col_6">
								<div class="ts_col_inner ts_landing_banner">
									<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-header-banner.png">
								</div>
							</div>
						</div>
						<div class="ts_row ts_features_section">							
							<div class="ts_col_4">
								<div class="ts_col_inner">
									<div class="ts_con_box_img">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-100-supported-carriers.png">
									</div>
									<div class="ts_icon_box_text">
										<h3>Auto-Track Your Shipments</h3>
										<p>Our Tracking API auto-tracks shipments with 200+ shipping providers across the globe</p>
										<a href="https://trackship.info/features/auto-track-shipments/" target="blank">read more</a>
									</div>	
								</div>
							</div>
							<div class="ts_col_4">
								<div class="ts_col_inner">
									<div class="ts_con_box_img">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-woocommerce-integration.png">
									</div>
									<div class="ts_icon_box_text">
										<h3>Seamless Integration</h3>
										<p>Fully integrated into your store and can be easily managed on your WooCommerce admin</p>
										<a href="https://trackship.info/docs/setup-trackship-on-woocommerce/" target="blank">read more</a>
									</div>	
								</div>
							</div>
							<div class="ts_col_4">
								<div class="ts_col_inner">
									<div class="ts_con_box_img">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-home-better-customer-support.png">
									</div>
									<div class="ts_icon_box_text">
										<h3>Post-Shipping Automation</h3>
										<p>TrackShip proactively updates tracking & delivery changes and automates your orders workflow</p>
										<a href="https://trackship.info/features/post-shipping-automation/" target="blank">read more</a>
									</div>	
								</div>
							</div>
							<div class="ts_col_4">
								<div class="ts_col_inner">
									<div class="ts_con_box_img">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-home-email-notifications.png">
									</div>
									<div class="ts_icon_box_text">
										<h3>Keep Your Customers informed</h3>
										<p>Keep Your Customers informed with automated shipment & delivery notifications</p>
										<a href="https://trackship.info/features/shipment-status-notifications/" target="blank">read more</a>
									</div>	
								</div>
							</div>	
							<div class="ts_col_4">
								<div class="ts_col_inner">
									<div class="ts_con_box_img">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-home-tracking-page.png">
									</div>
									<div class="ts_icon_box_text">
										<h3>Tracking Page on Your Store</h3>
										<p>Engage your customers with a tracking page on your store with up-to-date shipment tracking info</p>
										<a href="https://trackship.info/features/tracking-page/" target="blank">read more</a>
									</div>	
								</div>
							</div>							
							<div class="ts_col_4">
								<div class="ts_col_inner">
									<div class="ts_con_box_img">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-home-tracking-delivery-analytics.png">
									</div>
									<div class="ts_icon_box_text">
										<h3>Shipping & Delivery Analytics</h3>
										<p>Analyse delivery performance, find out exception and get an overview of your shipments data</p>
										<a href="https://trackship.info/features/tracking-delivery-analytics/" target="blank">read more</a>
									</div>	
								</div>
							</div>		
						</div>						
					</div>
		</div>
	</div>
</section>
<div id="" class="popupwrapper ts_video_popup" style="display:none;">
	<div class="popuprow">
		<div class="videoWrapper">
		<iframe id="ts_video" src="https://www.youtube.com/embed/PhnqDorKN_c" frameborder="0"  allowfullscreen></iframe>
		</div>
	</div>
	<div class="popupclose"></div>
</div>