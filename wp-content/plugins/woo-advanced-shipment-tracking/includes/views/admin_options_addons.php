<?php
/**
 * html code for tools tab
 */
$more_plugins = array(
	0 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'image' => 'smswoo-addons-icon.jpg',
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	1 => array(
		'title' => 'Country Based Restrictions Pro',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'image' => 'cbr-icon.png',
		'url' => 'https://www.zorem.com/products/country-based-restriction-pro/',
		'file' => 'country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php'
	),
	2 => array(
		'title' => 'Advanced Order Status Manager',
		'description' => 'The Advanced Order Status Manager allows store owners to manage the WooCommerce orders statuses, create, edit, and delete custom Custom Order Statuses and integrate them into the WooCommerce orders flow.',
		'image' => 'AOSM-addons-icon.jpg',
		'url' => 'https://www.zorem.com/products/advanced-order-status-manager/',
		'file' => 'advanced-order-status-manager/advanced-order-status-manager.php'
	),
	3 => array(
		'title' => 'Sales Report Email Pro',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.',
		'image' => 'sre-icon.png',
		'url' => 'https://www.zorem.com/products/sales-report-email-for-woocommerce/',
		'file' => 'sales-report-email-pro-addon/sales-report-email-pro-addon.php'
	),
); 

//$status = install_plugin_install_status( $plugin );
$pro_plugins = array(
	0 => array(
		'title' => 'Tracking Per Item Add-on',
		'description' => 'The Tracking per item is add-on for the Advanced Shipment Tracking for WooCommerce plugin that lets you attach tracking numbers to line items and to line item quantities.',
		'url' => 'https://www.zorem.com/products/tracking-per-item-ast-add-on/',
		'image' => 'tpi-icon.png',
	),
	1 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/',
		'image' => 'sms-woo-icon.png',
	),
	2 => array(
		'title' => 'Advanced Order Status Manager',
		'description' => 'The Advanced Order Status Manager allows store owners to manage the WooCommerce orders statuses, create, edit, and delete custom Custom Order Statuses and integrate them into the WooCommerce orders flow.',
		'url' => 'https://www.zorem.com/products/advanced-order-status-manager/',
		'image' => 'AOSM-banner.png',
	),
	3 => array(
		'title' => 'Country Based Restriction Pro',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'url' => 'https://www.zorem.com/products/country-based-restriction-pro/',
		'image' => 'cbr-banner.png',
	),
	4 => array(
		'title' => 'Sales Report Email Pro',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.',
		'url' => 'https://www.zorem.com/products/sales-report-email-for-woocommerce/',
		'image' => 'sre-banner.png',
	),		
);

$ast_addons = array(
	0 => array(
		'title' => 'Tracking Per Item Add-on',
		'description' => 'The Tracking per item is add-on for the Advanced Shipment Tracking for WooCommerce plugin that lets you attach tracking numbers to line items and to line item quantities.',
		'url' => 'https://www.zorem.com/products/tracking-per-item-ast-add-on/',
		'image' => 'tpi-addon-icon.jpg',
		'file' => 'ast-tracking-per-order-items/ast-tracking-per-order-items.php'
	),
	1 => array(
		'title' => 'WC Shipping Tracking Add-on',
		'description' => 'Add Advanced Shipment Tracking for WooCommerce Compatibility with WooCommerce Services plugin.',
		'url' => 'https://www.zorem.com/product/wc-shipping-tracking-add-on/',
		'image' => 'wc-addon-banner.jpg',
		'file' => 'ast-compatibility-with-wc-shipstation/ast-compatibility-with-wc-shipstation.php'
	),
	2 => array(
		'title' => 'ShipStation Tracking Add-on',
		'description' => 'Add Advanced Shipment Tracking for WooCommerce Compatibility with WooCommerce ShipStation Integration plugin.',
		'url' => 'https://www.zorem.com/product/shipstation-tracking-add-on/',
		'image' => 'shipstations-addon-banner.jpg',
		'file' => 'ast-compatibility-with-wc-shipstation/ast-compatibility-with-wc-shipstation.php'
	),	
	3 => array(
		'title' => 'ReadyToShip Tracking Add-on',
		'description' => 'This plugin extends the API to work with Advanced Shipment Tracking for WooCommerce(AST) module, allowing for tracking numbers to be added and retrieved via the API',
		'url' => 'https://www.zorem.com/product/readytoship-tracking-add-on/',
		'image' => 'readytoship-addon-banner.jpg',
		'file' => 'ready-to-ship-ast-Integration/ready-to-ship-ast-Integration.php'
	),	
	4 => array(
		'title' => 'PayPal Tracking Add-on',
		'description' => 'This add-on extends the Advanced shipment tracking plugin and will automatically send tracking numbers and associated information from WooCommerce to PayPal using the PayPal API.',
		'url' => 'https://www.zorem.com/product/paypal-tracking-add-on/',
		'image' => 'paypal-addon-banner.jpg',
		'file' => 'paypal-tracking-add-on-for-ast/paypal-tracking-add-on-for-ast.php'
	),		
);

 
$wc_ast_api_key = get_option('wc_ast_api_key'); 
?>
<section id="content6" class="tab_section">
	<div class="d_table addons_page_dtable" style="">

		<?php
		$show_addons_tab = apply_filters( 'ast_show_addons_tab', false );
		
		if ( class_exists( 'ast_woo_advanced_shipment_tracking_by_products' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'ast_compatibility_with_wc_shipstation' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'ast_compatibility_with_wc_services' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'ast_compatibility_with_readytoship' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'paypal_tracking_add_on' ) ) {
			$show_addons_tab = true;
		}
		
		if ( $show_addons_tab) { ?>	
		<input id="tab_addons" type="radio" name="inner_tabs" class="inner_tab_input" data-tab="addons" checked="">
		<label for="tab_addons" class="inner_tab_label"><?php _e( 'Add-ons', 'woo-advanced-shipment-tracking' ); ?></label>
		
		<input id="tab_license" type="radio" name="inner_tabs" class="inner_tab_input" data-tab="license">
		<label for="tab_license" class="inner_tab_label"><?php _e( 'License', 'woo-advanced-shipment-tracking' ); ?></label>
		<hr class="inner_tabs_hr">
		<?php } else{ ?>
		<label for="tab_addons" class="inner_tab_label single_tab_label"><?php _e( 'Add-ons', 'woo-advanced-shipment-tracking' ); ?></label>	
		<hr class="inner_tabs_hr">
		<?php } ?>					
		<section id="content_tab_addons" class="<?php if ( $show_addons_tab ) { ?>inner_tab_section<?php } ?>">				
			
			<div class="section-content trackship_addon_section">
				<div class="ts_row">
					<div class="ts_col_8">
						<div class="ts_col_inner">
							<div class="ts_addon_logo_section">
								<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png">
								<span class="dashicons dashicons-plus"></span>
								<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ast-logo.png">
							</div>
							<h1 class="ts_landing_header">Spend less time on managing and more on marketing</h1>
							<p class="ts_landing_description">Trackship is a Multi-Carrier Shipment Tracking API that seamlessly integrates into your WooCommerce store and auto-tracks your shipments, automates your orders workflow, reduces the time spent on customer service and lets you provide a superior post-purchase experience to your customers.</p>
							<a href="javascript:void(0);" target="_self" class="button-primary btn_ts_transparent btn_large open_ts_video"><span><?php _e('Watch Video', 'woo-advanced-shipment-tracking'); ?></span><span class="dashicons dashicons-video-alt3"></span></a>
							<?php if($wc_ast_api_key){ ?> 
								<a href="https://trackship.info/my-account/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" class="button-primary btn_green2 btn_large" target="_blank" ><?php _e( 'Account Dashboard', 'woo-advanced-shipment-tracking' ); ?></a>
							<?php } else{ ?>
								<a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=tspage" target="_blank" class="button-primary btn_green2 btn_large"><span><?php _e('Start your free trial', 'woo-advanced-shipment-tracking'); ?></span></a>	
							<?php } ?>							
						</div>
					</div>				
					<div class="ts_col_4">
						<div class="ts_col_inner ts_landing_banner">
							<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-header-banner.png">
						</div>
					</div>
				</div>
			</div>	
			
			<div class="plugins_section free_plugin_section">
				<?php foreach($ast_addons as $plugin){ ?>
					<div class="single_plugin">
						<div class="free_plugin_inner">
							<div class="plugin_image">
								<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/<?php echo $plugin['image']; ?>">
							</div>
							<div class="plugin_description">
								<h3 class="plugin_title"><?php echo $plugin['title']; ?></h3>
								<p><?php echo $plugin['description']; ?></p>
								<?php 
								if ( is_plugin_active( $plugin['file'] ) ) { ?>
									<button type="button" class="button button-disabled" disabled="disabled">Active</button>
								<?php } else{ ?>
									<a href="<?php echo $plugin['url']; ?>" class="install-now button" target="blank">INSTALL NOW</a>
								<?php } ?>								
							</div>
						</div>	
					</div>	
				<?php } ?>						
			</div>										
			
			<h2 class="addons_page_title">More WooCommerce plugins by zorem</h2>					
			
			<div class="plugins_section free_plugin_section">
				<?php foreach($more_plugins as $plugin){ ?>
					<div class="single_plugin">
						<div class="free_plugin_inner">
							<div class="plugin_image">
								<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/<?php echo $plugin['image']; ?>">
							</div>
							<div class="plugin_description">
								<h3 class="plugin_title"><?php echo $plugin['title']; ?></h3>
								<p><?php echo $plugin['description']; ?></p>
								<?php 
								if ( is_plugin_active( $plugin['file'] ) ) { ?>
									<button type="button" class="button button-disabled" disabled="disabled">Active</button>
								<?php } else{ ?>
									<a href="<?php echo $plugin['url']; ?>" class="install-now button" target="blank">INSTALL NOW</a>
								<?php } ?>								
							</div>
						</div>	
					</div>	
				<?php } ?>						
			</div>					
		</section>
		
		<section id="content_tab_license" class="inner_tab_section">				
			<?php do_action('ast_addon_license_form'); ?>	
		</section>						
	</div>
</section>