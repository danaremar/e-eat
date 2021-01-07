<?php
/**
 * WCFM plugin views
 *
 * Plugin WC Booking Details Views
 *
 * @author 		WC Lovers
 * @package 	wcfmu/views
 * @version   2.2.1
 */
 
global $wp, $WCFM, $WCFMu, $thebooking, $wpdb;

if( !current_user_can( 'manage_bookings_settings' ) && !current_user_can( 'manage_bookings' ) ) {
	wcfm_restriction_message_show( "Bookings" );
	return;
}

if ( ! is_object( $thebooking ) ) {
	if( isset( $wp->query_vars['wcfm-bookings-details'] ) && !empty( $wp->query_vars['wcfm-bookings-details'] ) ) {
		$thebooking = get_wc_booking( $wp->query_vars['wcfm-bookings-details'] );
	}
}

$booking_id = $wp->query_vars['wcfm-bookings-details'];
if( $booking_id ) {
	$post = get_post($booking_id);
	$booking = new WC_Booking( $post->ID );
	$order             = $booking->get_order();
	$product_id        = $booking->get_product_id( 'edit' );
	$resource_id       = $booking->get_resource_id( 'edit' );
	$customer_id       = $booking->get_customer_id( 'edit' );
	$product           = $booking->get_product( $product_id );
	$resource          = new WC_Product_Booking_Resource( $resource_id );
	$customer          = $booking->get_customer();
	$statuses          = array_unique( array_merge( get_wc_booking_statuses( null, true ), get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
	$statuses          = apply_filters( 'wcfm_allowed_booking_status', $statuses );
} else {
	wcfm_restriction_message_show( "Invalid Booking" );
	return;
}

if( wcfm_is_vendor() ) {
	$is_booking_for_vendor = $WCFM->wcfm_vendor_support->wcfm_is_component_for_vendor( $booking_id, 'booking' );
	if( !$is_booking_for_vendor ) {
		if( apply_filters( 'wcfm_is_show_booking_restrict_message', true, $booking_id ) ) {
			wcfm_restriction_message_show( "Restricted Booking" );
		} else {
			echo apply_filters( 'wcfm_show_custom_booking_restrict_message', '', $booking_id );
		}
		return;
	}
}


do_action( 'before_wcfm_bookings_details' );
?>

<div class="collapse wcfm-collapse" id="wcfm_booking_details">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-calendar"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Booking Details', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e( 'Booking #', 'wc-frontend-manager' ); echo $booking_id; ?></h2>
			
			<?php
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post.php?post='.$booking_id.'&action=edit'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
				<?php
			}
			
			if( $wcfm_is_allow_booking_calendar = apply_filters( 'wcfm_is_allow_booking_calendar', true ) ) {
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_bookings_calendar_url().'" data-tip="'. __('Calendar View', 'wc-frontend-manager') .'"><span class="wcfmfa fa-calendar-alt"></span></a>';
				}
			}
			
			echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_bookings_url().'" data-tip="' . __( 'Bookings List', 'wc-frontend-manager' ) . '"><span class="wcfmfa fa-calendar"></span></a>';
			
			if( $wcfm_is_allow_manage_resource = apply_filters( 'wcfm_is_allow_manage_resource', true ) ) {
				if( WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_bookings_resources_url().'" data-tip="' . __( 'Manage Resources', 'wc-frontend-manager' ) . '"><span class="wcfmfa fa-briefcase"></span></a>';
				}
			}
			
			if( apply_filters( 'wcfm_add_new_product_sub_menu', true ) && apply_filters( 'wcfm_is_allow_create_bookable', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_edit_product_url().'" data-tip="' . __('Create Bookable', 'wc-frontend-manager') . '"><span class="wcfmfa fa-cube"></span></a>';
			}
			?>
			<div class="wcfm_clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <?php do_action( 'begin_wcfm_bookings_details' ); ?>
		
		<!-- collapsible -->
		<div class="page_collapsible bookings_details_general" id="wcfm_general_options">
			<?php _e('Overview', 'wc-frontend-manager'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="bookings_details_general_expander" class="wcfm-content">
	
				<p class="form-field form-field-wide">
					<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Booking Created:', 'wc-frontend-manager' ) ?></strong></span>
					<?php echo date_i18n( wc_date_format() . ' @ ' . wc_time_format(), $booking->get_date_created() ); ?>
				</p>
				
				<p class="form-field form-field-wide">
					<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Order Number:', 'wc-frontend-manager' ) ?></strong></span>
					<?php
					if ( $order ) {
						if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $order->get_order_number() ) ) {
							echo '<span class="booking-orderno"><a href="' . get_wcfm_view_order_url( $order->get_order_number(), $order ) . '">#' . $order->get_order_number() . '</a></span> &ndash; ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . ' (' . date_i18n( wc_date_format(), strtotime( $order->get_date_created() ) ) . ')';
						} else {
							echo '<span class="booking-orderno">#' . $order->get_order_number() . ' - ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span>';
						}
					} else {
						echo '-';
					}
					?>
				</p>
				
				<?php if ( $order && is_a( $order, 'WC_Order' ) ) { ?>
					<p class="form-field form-field-wide">
						<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Booking Cost:', 'wc-frontend-manager' ) ?></strong></span>
						<?php
						echo '<span class="booking-cost">' . $order->get_formatted_order_total() . '</span>';
						?>
					</p>
				<?php } ?>
				
				<?php do_action( 'wcfm_booking_overview_block', $booking, $product ); ?>
				
				<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() ) { ?>
					<?php if( apply_filters( 'wcfm_is_allow_booking_status_update', true ) ) { ?>
						<div id="wcfm_booking_status_update_wrapper" class="wcfm_booking_status_update_wrapper">
							<p class="form-field form-field-wide">
								<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Booking Status:', 'woocommerce-bookings' ); ?></strong></span>
								<select id="wcfm_booking_status" name="booking_status">
									<?php
										foreach ( $statuses as $key => $value ) {
											echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $post->post_status, false ) . '>' . esc_html__( $value, 'woocommerce-bookings' ) . '</option>';
										}
									?>
								</select>
								<button class="wcfm_modify_booking_status button" id="wcfm_modify_booking_status" data-bookingid="<?php echo $booking_id; ?>"><?php _e( 'Update', 'wc-frontend-manager' ); ?></button>
							</p>
							<div class="wcfm-message" tabindex="-1"></div>
							<?php if( $post->post_status == 'pending-confirmation' ) { ?>
								<div class="wcfm_clearfix"></div><br/>
								<p class="form-field form-field-wide wcfm_booking_confirmed_cancel_wrapper" style="text-align: center;">
								  <a id="wcfm_booking_confirmed_button" style="float: none;" class="wcfm_submit_button" href="#" data-bookingid="<?php echo $booking_id; ?>"><?php _e( 'Confirm', 'wc-frontend-manager' ); ?></a>
								  <a id="wcfm_booking_declined_button" style="float: none;" class="wcfm_submit_button" href="#" data-bookingid="<?php echo $booking_id; ?>"><?php _e( 'Decline', 'wc-frontend-manager' ); ?></a>
								  <div class="wcfm_clearfix"></div>
								</p>
							<?php } ?>
							<div class="wcfm_clearfix"></div>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<br />
		<!-- collapsible End -->
		
		<!-- collapsible -->
		<div class="page_collapsible bookings_details_booking" id="wcfm_booking_options">
			<?php _e('Booking', 'wc-frontend-manager'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="bookings_details_booking_expander" class="wcfm-content">
				
				<p class="form-field form-field-wide">
					<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Booked product:', 'woocommerce-bookings' ) ?></strong></span>
					<?php
					
					if ( $product ) {
						$product_post = get_post($product->get_ID());
						echo '<a class="wcfm_dashboard_item_title" href="' . get_permalink($product->get_ID()) . '" target="_blank">' . $product_post->post_title . '</a>';
					} else {
						echo '-';
					}
					?>
				</p>
				
				<?php if( $resource_id ) { ?>
					<p class="form-field form-field-wide">
						<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Resource:', 'wc-frontend-manager' ) ?></strong></span>
						<?php
							echo esc_html( $resource->post_title );
						?>
					</p>
				<?php } ?>
				
				<?php
				if ( $product && is_callable( array( $product, 'get_person_types' ) ) ) {
					$person_types  = $product ? $product->get_person_types() : array();
					$person_counts = $booking->get_person_counts();
					
					if ( count( $person_counts ) > 0 || count( $person_types ) > 0 ) {
				?>
						<p class="form-field form-field-wide">
							<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Person(s):', 'woocommerce-bookings' ) ?></strong></span>
							<?php 
							$pfirst = true;
							foreach ( $person_counts as $person_id => $person_count ) {
								$person_type = null;

								try {
									$person_type = new WC_Product_Booking_Person_Type( $person_id );
								} catch ( Exception $e ) {
								}

								if ( $person_type ) {
									if( !$pfirst ) echo ', ';
									echo $pfirst = false;
									echo $person_type->get_name() . ' (';
									echo $person_count;
									echo ')';
								}
							} 
							?>
						</p>
				<?php 
					} else {
					?>
					<p class="form-field form-field-wide">
						<span for="booked_product" class="wcfm-title wcfm_title"><strong><?php _e( 'Person(s):', 'woocommerce-bookings' ) ?></strong></span>
						<?php echo ! empty( $person_counts[0] ) ? $person_counts[0] : 0; ?>
					</p>
					<?php
					}
				}
				?>
				
				
				<?php if( apply_filters( 'wcfm_is_allow_booking_schedule_update', true ) ) { ?>
					<form id="wcfm_wcb_schedule_update_form">
						<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $booking_id; ?>" />
						<p class="form-field form-field-wide">
							<?php 
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_booking_schedule_update_fields', array( 'booking_start_date' => array(
																																																'id'          => 'booking_start_date',
																																																'type'        => 'text',
																																																'class'       => 'wcfm-text booking_date booking_start_date',
																																																'label'       => __( 'Start date:', 'woocommerce-bookings' ),
																																																'label_class' => 'wcfm-title wcfm_title booking_date',
																																																'value'       => date( 'Y-m-d', $booking->get_start( 'edit' ) ),
																																																) ), $booking )
																																															);
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_booking_schedule_update_fields', array( 'booking_start_time' => array(
																																																'id'          => 'booking_start_time',
																																																'type'        => 'text',
																																																'class'       => 'wcfm-text booking_start_time',
																																																'label'       => '@',
																																																'label_class' => 'wcfm-title wcfm_title booking_date booking_start_time_label',
																																																'value'       => date( 'H:i', $booking->get_start( 'edit' ) ),
																																																) ), $booking )
																																															);
							?>
						</p>
					
						<p class="form-field form-field-wide">
							<?php 
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_booking_schedule_update_fields', array( 'booking_end_date' => array(
																																																'id'          => 'booking_end_date',
																																																'type'        => 'text',
																																																'class'       => 'wcfm-text booking_date booking_end_date',
																																																'label'       => __( 'End date:', 'woocommerce-bookings' ),
																																																'label_class' => 'wcfm-title wcfm_title booking_date',
																																																'value'       => date( 'Y-m-d', $booking->get_end( 'edit' ) ),
																																																) ), $booking )
																																															);
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_booking_schedule_update_fields', array( 'booking_end_time' => array(
																																																'id'          => 'booking_end_time',
																																																'type'        => 'text',
																																																'class'       => 'wcfm-text booking_end_time',
																																																'label'       => '@',
																																																'label_class' => 'wcfm-title wcfm_title booking_date booking_end_time_label',
																																																'value'       => date( 'H:i', $booking->get_end( 'edit' ) ),
																																																) ), $booking )
																																															);
							?>
						</p>
						<p class="form-field form-field-wide">
							<?php 
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_booking_schedule_update_fields', array( '_booking_all_day' => array(
																																																'id'          => '_booking_all_day',
																																																'type'        => 'checkbox',
																																																'class'       => 'wcfm-checkbox booking_end_time',
																																																'label'       => __( 'All day booking:', 'woocommerce-bookings' ),
																																																'label_class' => 'wcfm-title wcfm_title checkbox_title booking_all_day_label',
																																																'value'       => 'YES',
																																																'dfvalue'     => $booking->get_all_day( 'edit' ) ? 'YES' : 'NO',
																																																) ), $booking )
																																															);
							?>
						</p>
						
						<div class="wcfm-clearfix"></div>
							<div class="wcfm-message" tabindex="-1"></div>
							<div class="wcfm-clearfix"></div>
							<?php if( WCFM_Dependencies::wcfmu_plugin_active_check() ) { ?>
								<div id="wcfm_messages_submit">
									<input type="submit" name="save-data" value="<?php _e( 'Update', 'wc-frontend-manager' ); ?>" id="wcfm_booking_schedule_button" class="wcfm_submit_button" />
								</div>
							<?php } ?>
						<div class="wcfm-clearfix"></div>
					</form>
				<?php } else { ?>
					<p class="form-field form-field-wide">
						<label for="booking_date"><?php _e( 'Start date:', 'woocommerce-bookings' ) ?></label>
						<?php echo apply_filters( 'wcfm_booking_start_date', date_i18n( wc_date_format() . ' ' . wc_time_format(), $booking->get_start( 'edit' ) ), $booking ); ?>
					</p>
					
					<p class="form-field form-field-wide">
						<label for="booking_date"><?php _e( 'End date:', 'woocommerce-bookings' ) ?></label>
						<?php echo apply_filters( 'wcfm_booking_end_date', date_i18n( wc_date_format() . ' ' . wc_time_format(), $booking->get_end( 'edit' ) ), $booking ); ?>
					</p>
					<p class="form-field form-field-wide">
						<label for="booking_date"><?php _e( 'All day booking:', 'woocommerce-bookings' ) ?></label>
						<?php echo $booking->get_all_day( 'edit' ) ? 'YES' : 'NO'; ?>
					</p>
				<?php } ?>
				
				<?php do_action( 'wcfm_booking_details_block', $booking, $product ); ?>
		 </div>
		</div>
		<div class="wcfm_clearfix"></div>
		<br />
		<!-- collapsible End -->
		
		<!-- collapsible -->
		<div class="page_collapsible bookings_details_customer" id="wcfm_customer_options">
			<?php _e('Customer', 'woocommerce-bookings'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="bookings_details_customer_expander" class="wcfm-content">
				<?php
				$order_id    = $post->post_parent;
				$has_data    = false;
		
				echo '<table class="booking-customer-details">';
				
				if ( $customer && $customer->name ) {
					echo '<tr>';
						echo '<th><span for="booked_product" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Name:', 'woocommerce-bookings' ) . '</strong></span></th>';
						echo '<td>';
						if( apply_filters( 'wcfm_is_allow_view_customer', true ) ) {
							printf( __( apply_filters( 'wcfm_wcb_customer_name_display',  '%s' . $customer->name . '%s', $customer ) ), '<a href="' . get_wcfm_customers_details_url($customer->user_id) . '" class="wcfm_dashboard_item_title">', '</a>' );
						} else {
							echo apply_filters( 'wcfm_wcb_customer_name_display',  $customer->name, $customer );
						}
						echo '</td>';
					echo '</tr>';
					
					if( apply_filters( 'wcfm_allow_view_customer_email', true ) && $customer->email ) {
						echo '<tr>';
							echo '<th><span for="booked_product" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'User Email:', 'woocommerce-bookings' ) . '</strong></span></th>';
							echo '<td>';
							echo '<a href="mailto:' . esc_attr( $customer->email ) . '">' . esc_html( $customer->email ) . '</a>';
							echo '</td>';
						echo '</tr>';
					}
			
					$has_data = true;
				}
		
				if ( $order_id && ( $order = wc_get_order( $order_id ) ) ) {
					if( apply_filters( 'wcfm_allow_customer_billing_details', true ) ) {
						echo '<tr>';
							echo '<th><span for="booked_product" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Address:', 'woocommerce-bookings' ) . '</strong></span></th>';
							echo '<td>';
							if ( $order->get_formatted_billing_address() ) {
								echo wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) );
							} else {
								echo __( 'No billing address set.', 'woocommerce-bookings' );
							}
							echo '</td>';
						echo '</tr>';
					}
					
					if( apply_filters( 'wcfm_allow_view_customer_email', true ) && $order->get_billing_email() ) {
						echo '<tr>';
							echo '<th><span for="booked_product" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Billing Email:', 'wc-frontend-manager' ) . '<strong></span></th>';
							echo '<td>';
							echo '<a href="mailto:' . esc_attr( $order->get_billing_email() ) . '">' . esc_html( $order->get_billing_email() ) . '</a>';
							echo '</td>';
						echo '</tr>';
						echo '<tr>';                                    
							echo '<th>' . __( 'Billing Phone:', 'wc-frontend-manager' ) . '</th>';
							echo '<td>';
							echo esc_html( $order->get_billing_phone() );
							echo '</td>';
						echo '</tr>';
					}
					
					if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $order_id ) ) {
						echo '<tr class="view">';
							echo '<th>&nbsp;</th>';
							echo '<td>';
							echo '<a class="button" target="_blank" href="' . get_wcfm_view_order_url( $order_id ) . '">' . __( 'View Order', 'wc-frontend-manager' ) . '</a>';
							echo '</td>';
						echo '</tr>';
					}
		
					$has_data = true;
				}
		
				if ( ! $has_data ) {
					echo '<tr>';
						echo '<td colspan="2">' . __( 'N/A', 'woocommerce-bookings' ) . '</td>';
					echo '</tr>';
				}
				do_action( 'wcfm_booking_details_customer_block' );
				echo '</table>';
				?>
			</div>
		</div>
	</div>
</div>