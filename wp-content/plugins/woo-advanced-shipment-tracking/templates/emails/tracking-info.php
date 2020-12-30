<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipment Tracking
 *
 * Shows tracking information in the HTML order email
 *
 * @author  WooThemes
 * @package WooCommerce Shipment Tracking/templates/email
 * @version 1.6.4
 */
if ( $tracking_items ) : 
	$wcast_customizer_settings = new wcast_initialise_customizer_settings();
	$table_header_font_size = '';
	$table_header_font_color = '';
	$table_content_font_size = '';
	$table_content_font_color = '';
	$email_border_color = '';
	$email_border_size = '';
	$shipment_tracking_header_text = '';
	$email_table_backgroud_color = '';
	$tracking_link_font_color = '';
	$tracking_link_bg_color = '';
	$table_content_line_height = '';
	$table_content_font_weight = '';
	$header_content_text_align = '';
	$table_padding = '12';	
	
	$tracking_info_settings = get_option('tracking_info_settings');
	$ast = new WC_Advanced_Shipment_Tracking_Actions;	
	
	$select_tracking_template = $ast->get_option_value_from_array('tracking_info_settings','select_tracking_template',$wcast_customizer_settings->defaults['select_tracking_template']);

	$show_provider_th = 1;	
	$colspan = 1;
	$display_thumbnail = $ast->get_option_value_from_array('tracking_info_settings','display_shipment_provider_image',$wcast_customizer_settings->defaults['display_shipment_provider_image']);

	$display_shipping_provider_name = $ast->get_option_value_from_array('tracking_info_settings','display_shipment_provider_name',$wcast_customizer_settings->defaults['display_shipment_provider_name']);
	
	$tracking_number_link = $ast->get_option_value_from_array('tracking_info_settings','tracking_number_link','');
		
	if($display_shipping_provider_name == 1 && $display_thumbnail == 1){
		$show_provider_th = 1;
		$colspan = 2;
	} else if($display_shipping_provider_name != 1 && $display_thumbnail == 1){
		$show_provider_th = 1;
		$colspan = 1;
	} else if($display_shipping_provider_name == 1 && $display_thumbnail != 1){
		$show_provider_th = 1;
		$colspan = 1;
	} else if($display_shipping_provider_name != 1 && $display_thumbnail != 1){
		$show_provider_th = 0;
		$colspan = 1;
	} else{
		$show_provider_th = 0;		
	} 		
	
	if(is_rtl()){
		$header_content_text_align = 'right';
	} else{
		$header_content_text_align = $ast->get_option_value_from_array('tracking_info_settings','header_content_text_align',$wcast_customizer_settings->defaults['header_content_text_align']);
	}
	
	$table_padding = 10;	
	
	$email_border_color = $ast->get_option_value_from_array('tracking_info_settings','table_border_color',$wcast_customizer_settings->defaults['table_border_color']);
	
	$email_border_size = $ast->get_option_value_from_array('tracking_info_settings','table_border_size',$wcast_customizer_settings->defaults['table_border_size']);
	
	$hide_trackig_header = $ast->get_option_value_from_array('tracking_info_settings','hide_trackig_header','');
	
	$shipment_tracking_header = $ast->get_option_value_from_array('tracking_info_settings','header_text_change','Tracking Information');
	
	$shipment_tracking_header_text = $ast->get_option_value_from_array('tracking_info_settings','additional_header_text','');
	
	$email_table_backgroud_color = $ast->get_option_value_from_array('tracking_info_settings','table_bg_color',$wcast_customizer_settings->defaults['table_bg_color']);
	
	$table_content_line_height = $ast->get_option_value_from_array('tracking_info_settings','table_content_line_height',$wcast_customizer_settings->defaults['table_content_line_height']);
	
	$table_content_font_weight = $ast->get_option_value_from_array('tracking_info_settings','table_content_font_weight',$wcast_customizer_settings->defaults['table_content_font_weight']);
	
	$table_header_bg_color = $ast->get_option_value_from_array('tracking_info_settings','table_header_bg_color',$wcast_customizer_settings->defaults['table_header_bg_color']);	
	
	$table_header_font_size = $ast->get_option_value_from_array('tracking_info_settings','table_header_font_size',$wcast_customizer_settings->defaults['table_header_font_size']);

	$table_header_font_weight = $ast->get_option_value_from_array('tracking_info_settings','table_header_font_weight',$wcast_customizer_settings->defaults['table_header_font_weight']);	
	
	$table_header_font_color = $ast->get_option_value_from_array('tracking_info_settings','table_header_font_color',$wcast_customizer_settings->defaults['table_header_font_color']);
	
	$table_content_font_size = $ast->get_option_value_from_array('tracking_info_settings','table_content_font_size',$wcast_customizer_settings->defaults['table_content_font_size']);		
		
	$table_content_font_color = $ast->get_option_value_from_array('tracking_info_settings','table_content_font_color',$wcast_customizer_settings->defaults['table_content_font_color']);
	
	$tracking_link_font_color = $ast->get_option_value_from_array('tracking_info_settings','tracking_link_font_color',$wcast_customizer_settings->defaults['tracking_link_font_color']);
	
	$tracking_link_bg_color = $ast->get_option_value_from_array('tracking_info_settings','tracking_link_bg_color',$wcast_customizer_settings->defaults['tracking_link_bg_color']);
	
	$th_column_style = "background:".$table_header_bg_color.";text-align: ".$header_content_text_align."; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;font-size:".$table_header_font_size."px;font-weight:".$table_header_font_weight."; color: ".$table_header_font_color." ; border: ".$email_border_size."px solid ".$email_border_color."; padding: ".$table_padding."px;";
	
	$td_column_style = "text-align: ".$header_content_text_align."; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size:".$table_content_font_size."px;font-weight:".$table_content_font_weight."; color: ".$table_content_font_color." ; border: ".$email_border_size."px solid ".$email_border_color."; padding: ".$table_padding."px;min-width: auto;";
	
	$tracking_link_style = "color: ".$tracking_link_font_color." ;background:".$tracking_link_bg_color.";padding: 10px;text-decoration: none;";
	
	$tracking_link_style2 = "color: ".$tracking_link_font_color.";padding: 10px;text-decoration: none;";
	
	$hide_table_header = $ast->get_option_value_from_array('tracking_info_settings','hide_table_header','');	
	
	$remove_date_from_tracking_info = $ast->get_option_value_from_array('tracking_info_settings','remove_date_from_tracking',$wcast_customizer_settings->defaults['remove_date_from_tracking']);
	
	$show_track_label = $ast->get_option_value_from_array('tracking_info_settings','show_track_label',$wcast_customizer_settings->defaults['show_track_label']);
	
	$provider_header_text = $ast->get_option_value_from_array('tracking_info_settings','provider_header_text',$wcast_customizer_settings->defaults['provider_header_text']);
		
	$tracking_number_header_text = $ast->get_option_value_from_array('tracking_info_settings','tracking_number_header_text',$wcast_customizer_settings->defaults['tracking_number_header_text']);
	
	$shipped_date_header_text = $ast->get_option_value_from_array('tracking_info_settings','shipped_date_header_text',$wcast_customizer_settings->defaults['shipped_date_header_text']);
	
	$track_header_text = $ast->get_option_value_from_array('tracking_info_settings','track_header_text',$wcast_customizer_settings->defaults['track_header_text']);
	
	$simple_layout_content = $ast->get_option_value_from_array('tracking_info_settings','simple_layout_content',$wcast_customizer_settings->defaults['simple_layout_content']);			
	
	$simple_provider_font_size = $ast->get_option_value_from_array('tracking_info_settings','simple_provider_font_size',$wcast_customizer_settings->defaults['simple_provider_font_size']);	
		
	$simple_provider_font_color = $ast->get_option_value_from_array('tracking_info_settings','simple_provider_font_color',$wcast_customizer_settings->defaults['simple_provider_font_color']);
	
	$show_provider_border = $ast->get_option_value_from_array('tracking_info_settings','show_provider_border',$wcast_customizer_settings->defaults['show_provider_border']);
	
	$provider_border_color = $ast->get_option_value_from_array('tracking_info_settings','provider_border_color',$wcast_customizer_settings->defaults['provider_border_color']);	
	
	if(isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview']){
		$preview = true;
	} else{
		$preview = false;
	}
	$text_align = is_rtl() ? 'right' : 'left'; 
	
	$shipment_status = get_post_meta( $order_id, "shipment_status", true);
	if($preview){
	?>
	<h2 class="header_text <?php if($hide_trackig_header){ echo 'hide'; } ?>" style="text-align:<?php echo $text_align; ?>;"><?php echo apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ); ?></h2>
	<?php } else{ ?>
		<h2 class="header_text" style="text-align:<?php echo $text_align; ?>;<?php if($hide_trackig_header){ echo 'display:none;'; } ?>"><?php echo apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ); ?></h2>
	<?php } ?>
	<p class="addition_header"><?php echo $shipment_tracking_header_text; ?></p>
	
	<?php if($select_tracking_template == 'simple_list'){ ?>
	<div class="tracking_info">
		<ul class="tracking_list">
			<?php foreach ( $tracking_items as $tracking_item ) {
			$date_shipped = date("Y-m-d");
			if(isset($tracking_item['date_shipped'])){
				$date_shipped = $tracking_item['date_shipped'];
			}	
			
			global $wpdb;
			
			$tracking_provider = isset( $tracking_item['tracking_provider'] ) ? $tracking_item['tracking_provider'] : $tracking_item['custom_tracking_provider'];
			
			$tracking_provider = apply_filters('convert_provider_name_to_slug',$tracking_provider);

			$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_shippment_provider WHERE ts_slug = %s", $tracking_provider ) );											
			
			$provider_name = apply_filters('get_ast_provider_name', $tracking_provider, $results);	
			
			$url = str_replace('%number%',$tracking_item['tracking_number'],$tracking_item['formatted_tracking_link']);	
			$simple_layout_content_updated = '';
			?>	
				<li class="tracking_list_li">
					<div class="tracking_list_div" style="font-size:<?php echo $simple_provider_font_size; ?>px;color:<?php echo $simple_provider_font_color; ?>;border-bottom:<?php echo $show_provider_border; ?>px solid <?php echo $provider_border_color; ?>">
						<?php 
						$formatted_tracking_provider = apply_filters( 'ast_provider_title', esc_html( $provider_name ));
						
						$simple_layout_content_updated = str_replace('{ship_date}',date_i18n( get_option( 'date_format' ), $date_shipped ),$simple_layout_content);
						
						$simple_layout_content_updated = str_replace('{shipping_provider}',$formatted_tracking_provider,$simple_layout_content_updated);
						
						$tracking_number_link = '<a target="_blank" href="'.esc_url( $url ).'">'.$tracking_item['tracking_number'].'</a>';
						
						$simple_layout_content_updated = str_replace('{tracking_number_link}',$tracking_number_link,$simple_layout_content_updated);
						
						echo $simple_layout_content_updated; ?>						
					</div>
					<?php do_action("ast_tracking_simple_list_email_body", $order_id,$tracking_item); ?>
				</li>
			<?php } ?>			
		</ul>
	</div>
	<?php } else{ ?>
	<table class="td tracking_table" cellspacing="0" cellpadding="6" style="width: 100%;border-collapse: collapse;background:<?php echo $email_table_backgroud_color; ?>" border="1">
		<?php if($preview){ ?>
		<thead class="<?php if($hide_table_header){ echo 'hide'; }?>">
			<tr>
				<?php if($show_provider_th){ ?>
					<th class="tracking-provider"  colspan="<?php echo $colspan; ?>" scope="col" class="td" style="<?php echo $th_column_style; ?>">
						<?php _e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?>
					</th>
				<?php } ?>
				<?php do_action("ast_tracking_email_header", $order_id, $th_column_style); ?>
				<th class="tracking-number" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>												
				<?php if($preview){ ?>
					<th class="date-shipped <?php if($remove_date_from_tracking_info == 1){ echo 'hide'; } ?>" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php } else{
						if($remove_date_from_tracking_info != 1){ ?>
							<th class="date-shipped" style="<?php echo $th_column_style; ?>"><span class="nobr"><?php _e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php }
					} ?>
				<?php 
				if(!$tracking_number_link){
				if($preview){ ?>
				<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><span class="track_label <?php if($show_track_label != 1){ echo 'hide'; } ?>"><?php _e( $track_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
				<?php } else{ ?>
					<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php if($show_track_label == 1){ _e( $track_header_text, 'woo-advanced-shipment-tracking' ); } ?></th>
				<?php } }
				if(isset($show_shipment_status) && $show_shipment_status){ ?>
					<th class="shipment-status" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( 'Shipment Status', 'woo-advanced-shipment-tracking' ); ?></th>
				<?php }	
				?>
			</tr>
		</thead>
		<?php } else{ ?>
		<thead style="<?php if($hide_table_header){ echo 'display:none'; }?>">
			<tr>
				<?php if($show_provider_th){ ?>
					<th class="tracking-provider" colspan="<?php echo $colspan; ?>"  scope="col" class="td" style="<?php echo $th_column_style; ?>">
						<?php esc_html_e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?>
					</th>
				<?php } ?>
				<?php do_action("ast_tracking_email_header", $order_id, $th_column_style); ?>
				<th class="tracking-number" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php esc_html_e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>				
				<?php if($preview){ ?>
					<th class="date-shipped <?php if($remove_date_from_tracking_info == 1){ echo 'hide'; } ?>" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
				<?php } else{
						if($remove_date_from_tracking_info != 1){ ?>
							<th class="date-shipped" style="<?php echo $th_column_style; ?>"><span class="nobr"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php }
					} ?>
				<?php 
				if(!$tracking_number_link){
				if($preview){ ?>
				<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><span class="track_label <?php if($show_track_label != 1){ echo 'hide'; } ?>"><?php _e( $track_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
				<?php } else{ ?>
					<th class="order-actions" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php if($show_track_label == 1){ _e( $track_header_text, 'woo-advanced-shipment-tracking' ); } ?></th>
				<?php } } 
				if(isset($show_shipment_status) && $show_shipment_status){ ?>
					<th class="shipment-status" scope="col" class="td" style="<?php echo $th_column_style; ?>"><?php _e( 'Shipment Status', 'woo-advanced-shipment-tracking' ); ?></th>
				<?php }
				?>
			</tr>
		</thead>	
		<?php } ?>

		<tbody style="line-height:<?php echo $table_content_line_height; ?>px;"><?php
		foreach ( $tracking_items as $key => $tracking_item ) {
				$date_shipped = date("Y-m-d");
				if(isset($tracking_item['date_shipped'])){
					$date_shipped = $tracking_item['date_shipped'];
				}
				
				global $wpdb;
				
				$tracking_provider = isset( $tracking_item['tracking_provider'] ) ? $tracking_item['tracking_provider'] : $tracking_item['custom_tracking_provider'];
				$tracking_provider = apply_filters('convert_provider_name_to_slug',$tracking_provider);

				$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_shippment_provider WHERE ts_slug = %s", $tracking_provider ) );											
				
				$provider_name = apply_filters('get_ast_provider_name', $tracking_provider, $results);
				?>
				<tr class="tracking" style="background-color:<?php echo $email_table_backgroud_color; ?>">
					<?php if($display_thumbnail == 1){ ?>
					<td class="tracking-provider" data-title="<?php _e( 'Provider', 'woo-advanced-shipment-tracking' ); ?>" style="<?php echo $td_column_style; ?>;width: 50px;">
						<img style="width: 50px;vertical-align: middle;" src="<?php echo apply_filters('get_shipping_provdider_src',$results); ?>">
					</td>
					<?php } ?>
					<?php if($display_shipping_provider_name == 1){ ?>
					<td class="tracking-provider" data-title="<?php _e( 'Provider Name', 'woo-advanced-shipment-tracking' ); ?>" style="<?php echo $td_column_style; ?>">
						<?php echo apply_filters( 'ast_provider_title', esc_html( $provider_name )); ?>
					</td>
					<?php } ?>

					<?php do_action("ast_tracking_email_body", $order_id,$tracking_item, $td_column_style); ?>

					<td class="tracking-number" data-title="<?php _e( 'Tracking Number', 'woo-advanced-shipment-tracking' ); ?>" style="<?php echo $td_column_style; ?>">
						<?php if($tracking_item['formatted_tracking_link'] && $tracking_number_link){ 
								$url = str_replace('%number%',$tracking_item['tracking_number'],$tracking_item['formatted_tracking_link']); ?>	
								<a href="<?php echo esc_url( $url ); ?>" style="<?php echo $tracking_link_style2; ?>" target="_blank"><?php echo esc_html( $tracking_item['tracking_number'] ); ?></a>
						<?php } else{
							echo esc_html( $tracking_item['tracking_number'] );
						} ?>						
					</td>
					<?php if($preview){ ?>
						<td class="date-shipped <?php if($remove_date_from_tracking_info == 1){ echo 'hide'; } ?>" data-title="<?php _e( 'Status', 'woocommerce' ); ?>" style="<?php echo $td_column_style; ?>">
							<time datetime="<?php echo date( 'Y-m-d', $date_shipped ); ?>" title="<?php echo date( 'Y-m-d', $date_shipped ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $date_shipped ); ?></time>
						</td>						
					<?php } else{ 
						if($remove_date_from_tracking_info != 1){ ?>
							<td class="date-shipped" style="<?php echo $td_column_style; ?>" data-title="<?php _e( 'Date', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
								<time datetime="<?php echo date( 'Y-m-d', $date_shipped ); ?>" title="<?php echo date( 'Y-m-d', $date_shipped ); ?>"><?php echo date_i18n( get_option( 'date_format' ), $date_shipped ); ?></time>
							</td>
						<?php } 
						} 
					if(!$tracking_number_link){	
					?>					
					<td class="order-actions" style="<?php echo $td_column_style; ?>">
							<?php if($tracking_item['formatted_tracking_link']){ ?>
								<?php $url = str_replace('%number%',$tracking_item['tracking_number'],$tracking_item['formatted_tracking_link']); ?><a href="<?php echo esc_url( $url ); ?>" style="<?php echo $tracking_link_style; ?>" target="_blank"><?php _e( 'Track', 'woo-advanced-shipment-tracking' ); ?></a>
							<?php } ?>
					</td>
					<?php }
					if(isset($show_shipment_status) && $show_shipment_status){ 
						$data = $shipment_status[$key];
						$status = $data["status"];
					?>
						<td class="shipment-status" style="<?php echo $td_column_style; ?>"><?php echo apply_filters("trackship_status_filter",$status)?></td>
					<?php }
					?>
				</tr><?php
		}
		?></tbody>
	</table><br/>
	<?php } ?>
	
	<style>
	ul.tracking_list{
		padding: 0;
		list-style: none;
	}
	ul.tracking_list .tracking_list_li{
		margin-bottom: 5px;
	}
	ul.tracking_list .tracking_list_li .product_list_ul{
		padding-left: 10px;
	}
	ul.tracking_list .tracking_list_li .tracking_list_div{
		border-bottom:1px solid #e0e0e0;
	} 
	</style>
<?php
endif;