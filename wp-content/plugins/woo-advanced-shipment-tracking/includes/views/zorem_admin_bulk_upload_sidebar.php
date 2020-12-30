<?php
/**
 * html code for admin sidebar
 */
?>
<div class="zorem_admin_sidebar">
	<div class="zorem_admin_sidebar_inner">
		<div class="zorem-sidebar__section padding_0 import_tracking_sidebar">
			<img class="import_tracking_csv_banner" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/CSV-Import-Tracking-info.png">
		</div>
		<div class="zorem-sidebar__section">
			<ul class="bulk_upload_documentation_ul">
				<li><a class="" href="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>/assets/tracking.csv"><?php _e('Download sample csv', 'woo-advanced-shipment-tracking'); ?></a></li>
				<li><a target="blank" href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/bulk-import-shipment-tracking/"><?php _e('Documentation', 'woo-advanced-shipment-tracking'); ?></a></li>	
			</ul>
		</div>
		<?php $wc_ast_api_key = get_option('wc_ast_api_key'); 	
		
		if(!$wc_ast_api_key){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://trackship.info/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="_blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-sidebar-banner.png"></a>
		</div>
		<?php }		
		
		if(!class_exists('ast_woo_advanced_shipment_tracking_by_products')){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://www.zorem.com/shop/tracking-per-item-ast-add-on/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/tpi-icon.png"></a>
		</div>
		<?php } ?>
		
		<?php if(!class_exists('SMS_for_WooCommerce')){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://www.zorem.com/products/sms-for-woocommerce/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/sms-woo-icon.png"></a>			
		</div>
		<?php } ?>	
		
		<?php if(!class_exists('Advanced_Order_Status_Manager')){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://www.zorem.com/products/advanced-order-status-manager/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/AOSM-banner.png"></a>			
		</div>
		<?php } ?>
		
		<?php if(!class_exists('ZH_Product_Country_Restrictions')){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://www.zorem.com/products/country-based-restriction-pro/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/cbr-banner.png"></a>			
		</div>
		<?php } ?>

		<?php if(!class_exists('Woocommerce_Advanced_Sales_Report_Email')){ ?>
		<div class="zorem-sidebar__section padding_0">
			<a href="https://www.zorem.com/products/sales-report-email-for-woocommerce/?utm_source=wpadmin&utm_medium=sidebar&utm_campaign=upgrade" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/sre-banner.png"></a>			
		</div>
		<?php } ?>
		
	</div>
</div>