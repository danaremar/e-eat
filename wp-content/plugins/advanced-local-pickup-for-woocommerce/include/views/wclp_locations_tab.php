<?php 
$data = wc_local_pickup()->admin->get_data();
$location_id = get_option('location_defualt', min($data)->id);
$location = wc_local_pickup()->admin->get_data_byid($location_id);
?>
<section id="wclp_content3" class="wclp_tab_section">
    <div class="wclp_tab_inner_container">         	
		<div class="wclp_outer_form_table">
			<?php if ( 'locations' === $tab && 'edit' !== $section && 'add' !== $section ) : ?> 
			<table class="form-table location-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'Pickup Locations', 'advanced-local-pickup-for-woocommerce' ); ?></h3>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="pickup-location-list">
            	<form method="post" id="wclp_choose_default_location_form">
				<?php foreach( $data as $key => $location ) { ?>
                	<?php if(!class_exists( 'Advanced_local_pickup_PRO' )) { 
						if($location->id != $location_id) {
							continue;
						}
					}?>
                    <?php $store_name = isset($location) ? $location->store_name : 'Default Location'; ?>
                    <div class="single-location">
						<?php if(class_exists( 'Advanced_local_pickup_PRO' )) { ?>
                            <input type="hidden" id="<?php echo $location->id;?>" name="location_defualt" value="<?php echo $key;?>">
                            <span class="dashicons dashicons-menu location-sort"></span>
                        <?php } ?>
                        <a href="admin.php?page=local_pickup&tab=locations&section=edit&id=<?php echo $location->id;?>" class="link decoration"><span class="location-title"><strong><?php _e( stripslashes($store_name), 'advanced-local-pickup-for-woocommerce' ); ?></strong></span></a>
                        <?php if(class_exists( 'Advanced_local_pickup_PRO' )&& $location_id == $location->id) { ?><span class="default-label"><?php _e( 'Default', 'woocommerce' ); ?></span><?php } ?>
                        
                        <a href="admin.php?page=local_pickup&tab=locations&section=edit&id=<?php echo $location->id;?>" class="link location-edit <?php echo ( 'edit' === $section ) ? 'nav-tab-active' : ''; ?>"><?php _e( 'Edit', 'woocommerce' ); ?></a>
                    </div>
                <?php } ?>
                <?php wp_nonce_field( 'wclp_choose_default_location_form_action', 'wclp_choose_default_location_form_nonce_field' ); ?>
                <input type="hidden" name="action" value="wclp_choose_default_location_form_update">
            </form>
			</div>
			<?php 
				//ALP Pro Hook
				//do_action('wclp_add_location_button_hook', $section);
			?>
			<table class="form-table add-location-table">
				<tbody>
					<tr valign="top">						
						<td class="button-column">
							<div class="submit wclp-btn">
								<a <?php if(class_exists( 'Advanced_local_pickup_PRO' )) { ?>href="admin.php?page=local_pickup&tab=locations&section=edit&id=0"<?php } ?> class="button-primary location-add <?php echo ( 'add' === $section ) ? 'nav-tab-active' : ''; ?>">
									  <?php esc_html_e( 'Add Location ', 'advanced-local-pickup-for-woocommerce' ); ?><span class="dashicons dashicons-plus" style="vertical-align: middle;"></span>
								</a>
								<?php if(!class_exists( 'Advanced_local_pickup_PRO' )) { ?>
									<style type="text/css">
										a.button-primary.location-add {
											pointer-events: none;
											background: #e0e0e0;
											border: #e0e0e0;
										}
									</style>
									<span style="margin:10px;"><?php _e( 'Need additional pickup locations?', 'advanced-local-pickup-for-woocommerce' ); ?><a href="https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/?utm_source=wp-admin&utm_medium=ALPPRO&utm_campaign=add-ons" style="text-decoration: none;"><?php _e( ' Get to the Advanced Local Pickup Pro', 'advanced-local-pickup-for-woocommerce' ); ?></a></span>
								<?php } ?>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<?php elseif('locations' === $tab && 'edit' === $section ) : ?>
				<?php include 'wclp-edit-location-form.php';?>
			<?php endif; ?>
		</div>
    </div>
    <?php //include 'wclp_admin_sidebar.php';?>
</section>