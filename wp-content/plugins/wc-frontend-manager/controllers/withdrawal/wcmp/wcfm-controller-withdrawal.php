<?php
/**
 * WCFM plugin controllers
 *
 * Plugin WC Marketplace Withdrawal Dashboard Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/wcmp/controllers
 * @version   2.5.2
 */

class WCFM_Withdrawal_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST, $WCMp;
		
		$length = wc_clean($_POST['length']);
		$offset = wc_clean($_POST['start']);
		
		$args = array(
                'post_type' => 'dc_commission',
                'post_status' => array('publish', 'private'),
                'posts_per_page' => $length,
                'offset'         => $offset,
                'meta_query' => array(
                	  array(
										'key' => '_paid_status',
										'value' => 'unpaid',
										'compare' => '='
									)
                )
             );
		
    $args = apply_filters( 'wcfm_withdrawal_args', $args );
		
		$wcfm_withdrawals_array = get_posts( $args );
		
		$filtered_withdrawal_count = count( $wcfm_withdrawals_array );
		
		$commission_threshold_time = isset($WCMp->vendor_caps->payment_cap['commission_threshold_time']) && !empty($WCMp->vendor_caps->payment_cap['commission_threshold_time']) ? $WCMp->vendor_caps->payment_cap['commission_threshold_time'] : 0;
		
		// Generate Withdrawals JSON
		$wcfm_withdrawals_json = '';
		$wcfm_withdrawals_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $filtered_withdrawal_count . ',
															"recordsFiltered": ' . $filtered_withdrawal_count . ',
															"data": ';
		if(!empty($wcfm_withdrawals_array)) {
			$index = 0;
			$wcfm_withdrawals_json_arr = array();
			foreach($wcfm_withdrawals_array as $wcfm_withdrawals_single) {
				$order_id = get_post_meta( $wcfm_withdrawals_single->ID, '_commission_order_id', true );
				
				if( apply_filters( 'wcfm_is_show_commission_restrict_check', false, $order_id, $wcfm_withdrawals_single ) ) continue;
				
				$order_obj = wc_get_order($order_id);
        $commission_create_date = get_the_date('U', $wcfm_withdrawals_single->ID);
        $current_date = date('U');
        $diff = intval(($current_date - $commission_create_date) / (3600 * 24));
        if ($diff < $commission_threshold_time) {
           continue;
        }
				
				// Status
				if ( is_commission_requested_for_withdrawals( $wcfm_withdrawals_single->ID ) ) {
					$wcfm_withdrawals_json_arr[$index][] =  '<input name="requested_commissions[]" value="' . $wcfm_withdrawals_single->ID . '" class="wcfm-checkbox requested_select_withdrawal text_tip" data-tip="' . __( 'Already Requested', '' ) . '" type="checkbox" onclick="return false;" >';
				} else {
					$wcfm_withdrawals_json_arr[$index][] =  '<input name="commissions[]" value="' . $wcfm_withdrawals_single->ID . '" class="wcfm-checkbox select_withdrawal" type="checkbox" >';
				}
				
				// Order ID
				$wcfm_withdrawals_json_arr[$index][] = apply_filters( 'wcfm_commission_order_label_display', '<span class="wcfm_dashboard_item_title"># ' . $order_id . '</span>', $order_id, $wcfm_withdrawals_single );
				
				// Commission ID
				$wcfm_withdrawals_json_arr[$index][] = '<span class="wcfm_dashboard_item_title withdrawal_order_ids"># ' . $wcfm_withdrawals_single->ID . '</span>'; 
				
				// My Earnings
				$vendor_share = get_wcmp_vendor_order_amount(array('vendor_id' => apply_filters( 'wcfm_current_vendor_id', get_current_user_id() ), 'order_id' => $order_obj->get_id()));
				if (!isset($vendor_share['total'])) {
						$vendor_share['total'] = 0;
				}
				$vendor_share['total'] = apply_filters( 'wcfm_commission_share_total', $vendor_share['total'], $order_id, $wcfm_withdrawals_single );
				$wcfm_withdrawals_json_arr[$index][] = wc_price( $vendor_share['total'] );  
				
				// Date
				$wcfm_withdrawals_json_arr[$index][] = apply_filters( 'wcfm_commission_date_display', date_i18n( 'Y-m-d H:i A', strtotime( $wcfm_withdrawals_single->post_date ) ), $order_id, $wcfm_withdrawals_single );
				
				$index++;
			}												
		}
		if( !empty($wcfm_withdrawals_json_arr) ) $wcfm_withdrawals_json .= json_encode($wcfm_withdrawals_json_arr);
		else $wcfm_withdrawals_json .= '[]';
		$wcfm_withdrawals_json .= '
													}';
													
		echo $wcfm_withdrawals_json;
	}
}