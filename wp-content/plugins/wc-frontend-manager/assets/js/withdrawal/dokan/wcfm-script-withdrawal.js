jQuery(document).ready(function($) {
		
	// Request Withdrawals
	$('#wcfm_withdrawal_request_button').click(function(event) {
	  event.preventDefault();
	  
		$('#wcfm-content').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action                      : 'wcfm_ajax_controller',
			controller                  : 'wcfm-withdrawal-request',
			wcfm_withdrawal_manage_form : $('#wcfm_withdrawal_manage_form').serialize(),
			status                      : 'submit'
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
				wcfm_notification_sound.play();
				if($response_json.status) {
					$('#wcfm_withdrawal_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					if( $response_json.redirect ) {
						setTimeout(function() {
							window.location = 	$response_json.redirect;
						}, 2000);
					} else {
						$('#wcfm-content').unblock();
					}
				} else {
					$('#wcfm_withdrawal_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					wcfmMessageHide();
					$('#wcfm-content').unblock();
				}
			}
		});
	});
} );