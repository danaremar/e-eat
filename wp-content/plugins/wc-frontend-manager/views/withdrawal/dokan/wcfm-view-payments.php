<?php
/**
 * WCFM plugin view
 *
 * WCFM Dokan Payments List View
 *
 * @author 		WC Lovers
 * @package 	wcfm/withdrawal/dokan/view
 * @version   3.3.0
 */
 
global $WCFM;

$wcfm_is_allow_payments = apply_filters( 'wcfm_is_allow_payments', true );
if( !$wcfm_is_allow_payments ) {
	wcfm_restriction_message_show( "Payments" );
	return;
}

$start_date = date_i18n( wc_date_format(), strtotime( date('01-m-Y') ) );
$end_date = date_i18n( wc_date_format(), strtotime( date('t-m-Y') ) );
?>
<div class="collapse wcfm-collapse" id="wcfm_payments_listing">
  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-credit-card"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Payments History', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
	  <div id="wcfm_page_load"></div>
	  
	  <div class="wcfm-container wcfm-top-element-container">
			<h2>
				<?php _e( 'Transactions for: ', 'wc-frontend-manager' ); ?> 
				<span class="trans_start_date"><?php echo $start_date; ?></span>
				<?php echo ' - '; ?>
				<span class="trans_end_date"><?php echo $end_date; ?>
			</h2>
			
			<?php
			if( $wcfm_is_allow_withdrawal = apply_filters( 'wcfm_is_allow_withdrawal', true ) ) {
				echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.wcfm_withdrawal_url().'" data-tip="'. __('Withdrawal Request', 'wc-frontend-manager') .'"><span class="wcfmfa fa-currency">' . get_woocommerce_currency_symbol() . '</span><span class="text">' . __('Withdrawal', 'wc-frontend-manager' ) . '</span></a>';
			}
			?>
			<div class="wcfm-clearfix"></div>
		</div>
	  <div class="wcfm-clearfix"></div><br />
	  
	  <div class="wcfm_payments_filter_wrap wcfm_filters_wrap">
			<select name="status_type" id="dropdown_status_type" style="width: 160px;">
				<option value=""><?php  _e( 'Show all ..', 'wc-frontend-manager' ); ?></option>
				<option value="1"><?php  _e( 'Approved', 'wc-frontend-manager' ); ?></option>
				<option value="0"><?php  _e( 'Processing', 'wc-frontend-manager' ); ?></option>
				<option value="2"><?php  _e( 'Cancelled', 'wc-frontend-manager' ); ?></option>
			</select>
			<input id="payment_start_date_filter" type="text" class="wcfm-text" name="payment_start_date_filter" placeholder="<?php echo apply_filters( 'wcfm_date_filter_format', wc_date_format() ); ?>" data-date_format="<?php echo str_replace( 'mmmm', 'mm', str_replace( 'yyyy', 'yy', strtolower( wcfm_wp_date_format_to_js( wc_date_format() ) ) ) ); ?>" value="<?php echo $start_date; ?>" style="width: 160px;" />
			<input id="payment_end_date_filter" type="text" class="wcfm-text" name="payment_end_date_filter" placeholder="<?php echo apply_filters( 'wcfm_date_filter_format', wc_date_format() ); ?>" data-date_format="<?php echo str_replace( 'mmmm', 'mm', str_replace( 'yyyy', 'yy', strtolower( wcfm_wp_date_format_to_js( wc_date_format() ) ) ) ); ?>" value="<?php echo $end_date; ?>" style="width: 160px;" />
		</div>
	  
	  <?php do_action( 'before_wcfm_payments' ); ?>
			
		<div class="wcfm-container">
			<div id="wcfm_payments_listing_expander" class="wcfm-content">
				<table id="wcfm-payments" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Amount', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Pay Mode', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
						  <th><span class="wcicon-status-processing text_tip" data-tip="<?php _e( 'Status', 'wc-frontend-manager' ); ?>"></span></th>
							<th><?php _e( 'Amount', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Pay Mode', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Note', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Date', 'wc-frontend-manager' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_payments' );
		?>
	</div>
</div>