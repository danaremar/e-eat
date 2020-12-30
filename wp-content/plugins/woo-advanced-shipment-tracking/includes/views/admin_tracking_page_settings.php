<section id="content_tracking_page" class="inner_tab_section">
	<div class="tab_inner_container">
		<form method="post" id="trackship_tracking_page_form" action="" enctype="multipart/form-data">
			<div class="outer_form_table border_0">
				<table class="form-table tracking_page_heading">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php _e( 'Tracking Page', 'woo-advanced-shipment-tracking' ); ?></h3>
								<label class="setting_ul_checkbox_label"><?php _e( 'Enable a tracking page on your store', 'woo-advanced-shipment-tracking' ); ?></label>
							</td>					
							<td>
								<span class="tracking_page_toggle checkbox_span">
									<input type="hidden" name="wc_ast_use_tracking_page" value="0"/>
									<input class="tgl tgl-flat" id="wc_ast_use_tracking_page" name="wc_ast_use_tracking_page" type="checkbox" <?php if(get_option('wc_ast_use_tracking_page') == 1){ echo 'checked'; } ?> value="1"/>
									<label class="tgl-btn" for="wc_ast_use_tracking_page"></label>	
								</span>							
							</td>
						</tr>
					</tbody>
				</table>	
				
				<table class="form-table tracking_page_design_table">
					<tbody>
						<tr>
							<th>
								<label><?php _e( 'Select Tracking Page', 'woo-advanced-shipment-tracking' ); ?></label>
							</th>
							<td>
								<?php $page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' ); ?>
								<select class="select select2" id="wc_ast_trackship_page_id" name="wc_ast_trackship_page_id">
									<?php
										foreach($page_list as $page_id => $page_name){ ?>
											<option <?php if(get_option('wc_ast_trackship_page_id') == $page_id){ echo 'selected'; }?> value="<?php echo $page_id; ?>"><?php echo $page_name; ?></option>
										<?php } ?>
										<option <?php if(get_option('wc_ast_trackship_page_id') == 'other'){ echo 'selected'; }?> value="other"><?php _e( 'Other', 'woo-advanced-shipment-tracking' ); ?></option>	
								</select>
								<fieldset style="<?php if(get_option('wc_ast_trackship_page_id') != 'other'){ echo 'display:none;'; }?>" class="trackship_other_page_fieldset">
									<input type="text" name="wc_ast_trackship_other_page" style="width: 100%;" value="<?php echo get_option('wc_ast_trackship_other_page'); ?>">
								</fieldset>
								<p class="tracking_page_desc"><?php _e( 'Note - If you select a different page than the Shipment Tracking page, add the [wcast-track-order] shortcode to the selected page content.', 'woo-advanced-shipment-tracking' ); ?> <a href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/integration/" target="blank"><?php _e( 'more info', 'woo-advanced-shipment-tracking' ); ?></a></p>	
							</td>
						</tr>
						<tr>
							<th>
								<label><?php _e( 'Tracking Widget Layout', 'woo-advanced-shipment-tracking' ); ?></label>
							</th>
							<td>
								<span class="select_t_layout_section">
									<input type="radio" name="wc_ast_select_tracking_page_layout" id="t_layout_1" value="t_layout_1" class="radio-img" <?php if(get_option('wc_ast_select_tracking_page_layout','t_layout_1') == 't_layout_1'){ echo 'checked'; } ?>/>
									<label for="t_layout_1">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/t_layout_1.jpg?version=<?php echo wc_advanced_shipment_tracking()->version?>"/>
									</label>
								</span>
								<span class="select_t_layout_section">
									<input type="radio" name="wc_ast_select_tracking_page_layout" id="t_layout_2" value="t_layout_2" <?php if(get_option('wc_ast_select_tracking_page_layout','t_layout_1') == 't_layout_2'){ echo 'checked'; } ?> class="radio-img" />
									<label for="t_layout_2">
										<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/t_layout_2.jpg?version=<?php echo wc_advanced_shipment_tracking()->version?>"/>
									</label>
								</span>	
							</td>
						</tr>	
						<tr>
							<th>
								<label><?php _e( 'Tracking Widget Options', 'woo-advanced-shipment-tracking' ); ?></label>
							</th>
							<td class="tracking_page_display_options_td">
								<label>
									<input type="hidden" name="wc_ast_link_to_shipping_provider" value="0"/>
									<input type="checkbox" name="wc_ast_link_to_shipping_provider" value="1" id="wc_ast_link_to_shipping_provider" <?php if(get_option('wc_ast_link_to_shipping_provider') == 1){ echo 'checked'; } ?>>
									<?php _e( 'Add a link to the Shipping provider page', 'woo-advanced-shipment-tracking' ); ?>
								</label>
								<label>
									<input type="hidden" name="wc_ast_hide_tracking_provider_image" value="0"/>
									<input type="checkbox" name="wc_ast_hide_tracking_provider_image" value="1" id="wc_ast_hide_tracking_provider_image" <?php if(get_option('wc_ast_hide_tracking_provider_image') == 1){ echo 'checked'; } ?>>
									<?php _e( 'Hide Shipping Provider Image', 'woo-advanced-shipment-tracking' ); ?>
								</label>
								<label>
									<input type="hidden" name="wc_ast_hide_tracking_events" value="0"/>
									<input type="checkbox" name="wc_ast_hide_tracking_events" value="1" id="wc_ast_hide_tracking_events" <?php if(get_option('wc_ast_hide_tracking_events') == 1){ echo 'checked'; } ?>>
									<?php _e( 'Hide tracking event details', 'woo-advanced-shipment-tracking' ); ?>
								</label>
								<label>
									<input type="hidden" name="wc_ast_remove_trackship_branding" value="0"/>
									<input type="checkbox" name="wc_ast_remove_trackship_branding" value="1" id="wc_ast_remove_trackship_branding" <?php if(get_option('wc_ast_remove_trackship_branding') == 1){ echo 'checked'; } ?>>
									<?php _e( 'Remove TrackShip branding', 'woo-advanced-shipment-tracking' ); ?>
								</label>	
							</td>
						</tr>
						<tr>
							<th>
								<label><?php _e( 'Tracking Widget Border Color', 'woo-advanced-shipment-tracking' ); ?></label>
							</th> 
							<td>
								<input class="input-text regular-input" type="text" name="wc_ast_select_border_color" id="wc_ast_select_border_color" style="" value="<?php echo get_option('wc_ast_select_border_color')?>" >	
							</td>																				
						</tr>	
					</tbody>
				</table>					
				<table class="form-table tracking_page_save_table">
					<tbody>	
						<tr valign="top">						
							<td class="" colspan="2">
								<button name="save" class="button-primary woocommerce-save-button btn_green2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
								<button name="save" class="button-primary btn_ts_outline btn_large tracking_page_preview" type="button"><?php _e( 'Preview', 'woo-advanced-shipment-tracking' ); ?></button>
								<div class="spinner"></div>								
								<?php wp_nonce_field( 'trackship_tracking_page_form', 'trackship_tracking_page_form_nonce' );?>								
								<input type="hidden" name="action" value="trackship_tracking_page_form_update">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="" class="popupwrapper tracking_page_preview_popup" style="display:none;">
				<div class="popup_header">
					<h3><?php _e( 'Tracking Widget Preview', 'woo-advanced-shipment-tracking' ); ?></h3>
					<span class="dashicons dashicons-no-alt popup_close_icon"></span>
				</div>
				<div class="popuprow">					
					<div class="popup_body">
						<iframe id="tracking_preview_iframe" class="tracking_preview_iframe" src="<?php echo get_home_url(); ?>?action=preview_tracking_page" class="tracking-preview-link"></iframe>	
					</div>
				</div>
				<div class="popupclose"></div>
			</div>
		</form>	
	</div>
	<?php include 'trackship_sidebar.php'; ?>	
</section>