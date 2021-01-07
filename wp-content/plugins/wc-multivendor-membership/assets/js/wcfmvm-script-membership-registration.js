jQuery(document).ready(function($) {
	if( $(".country_select").length > 0 ) {
		$(".country_select").select2();
	}	
	
	if( $(".wcfm_multi_select").length > 0 ) {
		$(".wcfm_multi_select").select2({
			placeholder: wcfm_registration_params.choose_select2 + ' ...'
		});
	}
	
	// Email Verification
	if( $('.wcfm_email_verified_input').length > 0 ) {
		$('#user_email').on( 'blur', function() {
			sendEmailVerificationCode();
		});
		$('.wcfm_email_verified_button').on( 'click', function(e) {
			e.preventDefault();
			sendEmailVerificationCode();
			return false;
		});
	}
	
	function sendEmailVerificationCode() {
		
		$('#user_email').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
	  $is_valid = true;
	  if( $is_valid ) {
	  	$user_email = $('#user_email').val();
	  	if( !$user_email ) {
	  		$('#user_email').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
	  		$('#wcfm_membership_container .wcfm-message:not(.email_verification_message, .sms_verification_message)').html( '<span class="wcicon-status-cancelled"></span>' + $('#user_email').data('required_message') ).addClass('wcfm-error').slideDown();
	  		$is_valid = false;
	  	} else {
	  		$('#user_email').addClass('wcfm_validation_success').removeClass('wcfm_validation_failed');
	  	}
	  }
	  
		if( $is_valid ) {
			var data = {
				action         : 'wcfmvm_email_verification_code',
				user_email     : $('#user_email').val()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.status) {
						$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
						$('#wcfm_membership_registration_form .email_verification_message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
						$('#wcfm_membership_registration_form .email_verification_message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#user_email').unblock();
				}
			});
		} else {
			$('#user_email').unblock();
		}
	}
	
	// SMS Verification
	if( $('.wcfm_sms_verified_input').length > 0 ) {
		$('#user_phone').on( 'blur', function() {
			sendSMSVerificationCode();
		});
		$('.wcfm_sms_verified_button').on( 'click', function(e) {
			e.preventDefault();
			sendSMSVerificationCode();
			return false;
		});
	}
	
	function sendSMSVerificationCode() {
		
		$('#user_phone').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
	  $is_valid = true;
	  if( $is_valid ) {
	  	$user_phone = $('#user_phone').val();
	  	if( !$user_phone ) {
	  		$('#user_phone').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
	  		$('#wcfm_membership_container .wcfm-message:not(.email_verification_message, .sms_verification_message)').html( '<span class="wcicon-status-cancelled"></span>' + $('#user_phone').data('required_message') ).addClass('wcfm-error').slideDown();
	  		$is_valid = false;
	  	} else {
	  		$('#user_phone').addClass('wcfm_validation_success').removeClass('wcfm_validation_failed');
	  	}
	  }
	  
		if( $is_valid ) {
			var data = {
				action         : 'wcfmvm_sms_verification_code',
				user_phone     : $('#user_phone').val()
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					if($response_json.status) {
						$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
						$('#wcfm_membership_registration_form .sms_verification_message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown();
					} else {
						$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
						$('#wcfm_membership_registration_form .sms_verification_message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					$('#user_phone').unblock();
				}
			});
		} else {
			$('#user_phone').unblock();
		}
	}
	
	// Select wrapper fix
	function unwrapSelect() {
		$('#wcfm-main-contentainer').find('input[type="checkbox"]').each(function() {
			if ( $(this).parent().hasClass( "icheckbox_minimal" ) ) {
			  $(this).iCheck( 'destroy' );
			}
			if ( $(this).parent().is( "span" ) ) {
			  $(this).unwrap( "span" );
			}
			if ( $(this).parent().is( "label" ) ) {
			  $(this).unwrap( "label" );
			}
		});
		$('#wcfm-main-contentainer').find('select').each(function() {
			if ( $(this).parent().is( "span" ) ) {
			  $(this).unwrap( "span" );
			}
			if ( $(this).parent().is( "label" ) ) {
			  $(this).unwrap( "label" );
			}
			if ( $(this).parent().hasClass( "select-option" ) || $(this).parent().hasClass( "buddyboss-select-inner" ) || $(this).parent().hasClass( "buddyboss-select" ) ) {
				$(this).parent().find('.ti-angle-down').remove();
				$(this).parent().find('span').remove();
			  $(this).unwrap( "div" );
			}
		});
		setTimeout( function() {  unwrapSelect(); }, 500 );
	}
	
	// Store Name Validation
	function restrictNameInput() {
	  $('.wcfm_name_input').each(function() {
	  	$(this).on("contextmenu",function(){
				 return false;
			}); 
	    $(this).on('keydown', function(e) {
	    	//console.log(e.keyCode);
				if( !( ( e.keyCode > 95 && e.keyCode < 106 )
								|| ( e.keyCode > 47 && e.keyCode < 58 ) 
							  || ( e.keyCode > 64 && e.keyCode < 91 ) 
								|| e.keyCode == 8
								|| e.keyCode == 9
								|| e.keyCode == 32
								|| e.keyCode == 37
								|| e.keyCode == 39
								|| e.keyCode == 46
								|| e.keyCode == 189 ) ) {
									return false;
								}
			});
	  });
	  setTimeout( function() {  restrictNameInput(); }, 500 );
	}
	
	setTimeout( function() {
		$('#wcfm-main-contentainer').find('select').each(function() {
			if ( $(this).parent().is( "span" ) || $(this).parent().is( "label" ) ) {
			  $(this).css( 'padding', '5px' ).css( 'min-width', '15px' ).css( 'min-height', '35px' ).css( 'padding-top', '5px' ).css( 'padding-right', '5px' ); //.change();
			}
		});
		unwrapSelect();
		
		restrictNameInput();
	}, 500 );
	
	// Store Slug Verification
	if( $('.wcfm_store_slug_verified').length > 0 ) {
		$('#store_name').on( 'blur', function() {
			checkStoreSlug();
		});
		
		function checkStoreSlug() {
		
			$('#store_name').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			$is_valid = true;
			if( $is_valid ) {
				$store_name = $('#store_name').val();
				if( !$store_name ) {
					$('#store_name').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
					$('#wcfm_membership_container .wcfm-message:not(.email_verification_message, .sms_verification_message)').html( '<span class="wcicon-status-cancelled"></span>' + $('#store_name').data('required_message') ).addClass('wcfm-error').slideDown();
					$is_valid = false;
				} else {
					$('#store_name').addClass('wcfm_validation_success').removeClass('wcfm_validation_failed');
				}
			}
			
			if( $is_valid ) {
				var data = {
					action         : 'wcfmvm_store_slug_verification',
					store_name     : $('#store_name').val()
				}	
				$.post(wcfm_params.ajax_url, data, function(response) {
					if(response) {
						$response_json = $.parseJSON(response);
						$('.wcfm_store_slug_status').remove();
						$('.wcfm-message').html('').removeClass('wcfm-error').slideUp();
						if($response_json.status) {
							$('#store_name').addClass('wcfm_validation_success').removeClass('wcfm_validation_failed');
							$('.wcfm_store_slug').text( decodeURIComponent($response_json.store_slug) );
							$('.wcfm_store_slug_verified').append( '<span class="wcfm_store_slug_status"><span class="wcicon-status-completed"></span>&nbsp;'+$('.wcfm_store_slug_verified').data('avail')+'</span>' );
						} else {
							$('#store_name').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							$('.wcfm_store_slug').text( '['+wcfm_registration_params.your_store+']' );
							$('.wcfm_store_slug_verified').append( '<span class="wcfm_store_slug_status" style="color: red;"><span class="wcicon-status-cancelled"></span>&nbsp;'+$('.wcfm_store_slug_verified').data('unavail')+'</span>' );
						}
						$('#store_name').unblock();
					}
				});
			} else {
				$('#store_name').unblock();
			}
		}
	}
	
	function setStateBoxforCountry( countryBox ) {
		var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
				states = $.parseJSON( states_json ),
				country = countryBox.val();

		if ( states[ country ] ) {
			if ( $.isEmptyObject( states[ country ] ) ) {
				countryBox.parent().find('.wcfmvm_state_to_select').each(function() {
					$statebox = $(this);
					$statebox_id = $statebox.attr('id');
					$statebox_name = $statebox.attr('name');
					$statebox_val = $statebox.val();
					$statebox_dataname = $statebox.data('name');
					
					if( $statebox_val == null ) $statebox_val = '';
					
					if ( $statebox.is( 'select' ) ) {
						$statebox.replaceWith( '<input type="text" name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" value="'+$statebox_val+'" class="wcfm-text wcfmvm_state_to_select" />' );
					}
				});
			} else {
				input_selected_state = '';
				var options = '',
						state = states[ country ];

				countryBox.parent().find('.wcfmvm_state_to_select').each(function() {
					$statebox = $(this);
					$statebox_id = $statebox.attr('id');
					$statebox_name = $statebox.attr('name');
					$statebox_val = $statebox.val();
					$statebox_dataname = $statebox.data('name');
					
					if( $statebox_val == null ) $statebox_val = '';
					
					for ( var index in state ) {
						if ( state.hasOwnProperty( index ) ) {
							if ( $statebox_val ) {
								if ( $statebox_val == index ) {
									var selected_value = 'selected="selected"';
								} else {
									var selected_value = '';
								}
							}
							options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
						}
					}
					
					if ( $statebox.is( 'select' ) ) {
						$statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
					}
					if ( $statebox.is( 'input' ) ) {
						$statebox.replaceWith( '<select name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" class="wcfm-select wcfmvm_state_to_select"></select>' );
						$statebox = $('#'+$statebox_id);
						$statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
					}
					$statebox.val( $statebox_val );
				});
			}
		} else {
			countryBox.parent().find('.wcfmvm_state_to_select').each(function() {
				$statebox = $(this);
				$statebox_id = $statebox.attr('id');
				$statebox_name = $statebox.attr('name');
				$statebox_val = $statebox.val();
				$statebox_dataname = $statebox.data('name');
				
				if( $statebox_val == null ) $statebox_val = '';
				
				if ( $statebox.is( 'select' ) ) {
					$statebox.replaceWith( '<input type="text" name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" value="'+$statebox_val+'" class="wcfm-text wcfmvm_state_to_select" />' );
				}
			});
		}
	}
	
	$('.wcfmvm_country_to_select').each(function() {
	  $(this).change(function() {
	    setStateBoxforCountry( $(this) );
	  }).change();
	});
	
	$('#passoword').keyup(function() {
		if( wcfm_registration_params.is_strength_check ) {
			checkStrength($('#passoword').val());
		}
	});
	
	function checkStrength( password ) {
		var strength = 0
		if (password.length < 6) {
			$('#password_strength').removeClass();
			$('#password_strength').addClass('short')
			$('#password_strength').html(wcfm_registration_params.short);
			return 'short';
		}
		if (password.length > 7) strength += 1
		// If password contains both lower and uppercase characters, increase strength value.
		if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
		// If it has numbers and characters, increase strength value.
		if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
		// If it has one special character, increase strength value.
		if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// If it has two special characters, increase strength value.
		if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// Calculated strength value, we can return messages
		// If value is less than 2
		if (strength < 2) {
			$('#password_strength').removeClass()
			$('#password_strength').addClass('weak')
			$('#password_strength').html( wcfm_registration_params.weak );
			return 'weak';
		} else if (strength == 2) {
			$('#password_strength').removeClass()
			$('#password_strength').addClass('good')
			$('#password_strength').html( wcfm_registration_params.good );
			return 'good';
		} else {
			$('#password_strength').removeClass()
			$('#password_strength').addClass('strong')
			$('#password_strength').html( wcfm_registration_params.strong );
			return 'strong';
		}
	}
		
	// Membership Registration
	$wcfm_anr_loaded = false;
	if( jQuery('.anr_captcha_field').length > 0 ) {
		var wcfmvm_anr_onloadCallback = function() {
			var anr_obj = {
			'sitekey' : wcfm_registration_captcha_params.site_key,
			'size'    : wcfm_registration_captcha_params.size,
		};
		if ( 'invisible' == wcfm_registration_captcha_params.size ) {
			anr_obj.badge = wcfm_registration_captcha_params.badge;
	  } else {
			anr_obj.theme = wcfm_registration_captcha_params.theme;
		}
	
			var anr_captcha99;
			
			if ( 'invisible' == wcfm_registration_captcha_params.size ) {
				var anr_form99 = jQuery('#anr_captcha_field_99').closest('form')[0];
				anr_obj.callback = function(){ anr_form99.submit(); };
				anr_obj["expired-callback"] = function(){ grecaptcha.reset(anr_captcha99); };
				
				anr_form99.onsubmit = function(evt){
					evt.preventDefault();
					grecaptcha.execute(anr_captcha99);
				};
			}
			anr_captcha_99 = grecaptcha.render('anr_captcha_field_99', anr_obj );
		};
	
		setTimeout(function() {
			if (typeof grecaptcha != "undefined") {
				wcfmvm_anr_onloadCallback();
				$wcfm_anr_loaded = true;
			} else {
				setTimeout(function() {
					if (typeof grecaptcha != "undefined") {
						wcfmvm_anr_onloadCallback();
						$wcfm_anr_loaded = true;
					}
				}, 1000 );
			}
		}, 1000 );
	}
	
	$('#wcfm_membership_register_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
	  $('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
	  $wcfm_is_valid_form = true;
	  $( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_membership_registration_form') );
	  $is_valid = $wcfm_is_valid_form;
	  
	  if( $is_valid ) {
	  	$password = $('#passoword').val();
			if( $password && wcfm_registration_params.is_strength_check ) {
				$password_strength = checkStrength($password );
				if( ( $password_strength == 'short') || ( $password_strength == 'weak' ) ) {
					$('#passoword').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
					$('#wcfm_membership_container .wcfm-message:not(.email_verification_message, .sms_verification_message)').html( '<span class="wcicon-status-cancelled"></span>' + wcfm_registration_params.password_failed ).addClass('wcfm-error').slideDown();
					$is_valid = false;
				}
			}
		}
	  
	  if( $is_valid ) {
	  	$password = $('#passoword').val();
	  	$confirm_pwd = $('#confirm_pwd').val();
	  	if( $password != $confirm_pwd ) {
	  		$('#passoword').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
	  		$('#confirm_pwd').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
	  		$('#wcfm_membership_container .wcfm-message:not(.email_verification_message, .sms_verification_message)').html( '<span class="wcicon-status-cancelled"></span>' + $('#passoword').data('mismatch_message') ).addClass('wcfm-error').slideDown();
	  		$is_valid = false;
	  	}
	  }
	  
		$('#wcfm_membership_container').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		
		if( $is_valid ) {
			
			$form_data = new FormData( document.getElementById('wcfm_membership_registration_form') );
			$form_data.append( 'wcfm_membership_registration_form', $('#wcfm_membership_registration_form').serialize() ); 
			$form_data.append( 'action', 'wcfm_ajax_controller' ); 
			$form_data.append( 'controller', 'wcfm-memberships-registration' ); 
			
			$.ajax({
				type         : 'POST',
				url          : wcfm_params.ajax_url,
				data         : $form_data,
				contentType  : false,
				cache        : false,
				processData  :false,
				success: function(response) {
					if(response) {
						$response_json = $.parseJSON(response);
						if($response_json.status) {
							$('#wcfm_membership_registration_form .wcfm-message:not(.email_verification_message, .sms_verification_message)').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
								if( $response_json.redirect ) window.location = $response_json.redirect;	
							} );	
						} else {
							$('.wcfm-message').html('').removeClass('wcfm-success').slideUp();
							$('#wcfm_membership_registration_form .wcfm-message:not(.email_verification_message, .sms_verification_message)').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
						}
						
						if( jQuery('.anr_captcha_field').length > 0 ) {
							if (typeof grecaptcha != "undefined") {
								if( $wcfm_anr_loaded ) {
									grecaptcha.reset();
								} else {
									wcfmvm_anr_onloadCallback();
								}
							}
						}
					
						$('#wcfm_membership_container').unblock();
					}
				}
			});
		} else {
			if( jQuery('.anr_captcha_field').length > 0 ) {
				if (typeof grecaptcha != "undefined") {
					if( $wcfm_anr_loaded ) {
						grecaptcha.reset();
					} else {
						wcfmvm_anr_onloadCallback();
					}
				}
			}
			$('#wcfm_membership_container').unblock();
		}
	});
} );