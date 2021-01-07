jQuery(document).ready(function($) {
	// Order Status Update
	$('#wcfm_modify_order_status').click(function(event) {
		event.preventDefault();
		modifyWCFMOrderStatus();
		return false;
	});
		
	function modifyWCFMOrderStatus() {
		$('#orders_details_general_expander').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action       : 'wcfm_modify_order_status',
			order_status : $('#wcfm_order_status').val(),
			order_id     : $('#wcfm_modify_order_status').data('orderid')
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
				if($response_json.status) {
					wcfm_notification_sound.play();
					$('#wcfm_order_status_update_wrapper .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" );
				} else {
					wcfm_notification_sound.play();
					$('#wcfm_order_status_update_wrapper .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown( "slow" );
				}
				$('#orders_details_general_expander').unblock();
			}
		});
	}
	
	// Invoice Dummy
	$('.wcfm_pdf_invoice_dummy').each(function() {
		$(this).click(function(event) {
			event.preventDefault();
			alert( "Install WC Frontend Manager Ultimate and WooCommerce PDF Invoices & Packing Slips to avail this feature." );
			return false;
		});
	});
	
	// Invoice dummy - vendor
	$('.wcfm_pdf_invoice_vendor_dummy').each(function() {
		$(this).click(function(event) {
			event.preventDefault();
			alert( "Please contact your Store Admin to enable this feature for you." );
			return false;
		});
	});
	
} );