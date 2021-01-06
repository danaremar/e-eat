jQuery(document).ready(function(){
	
	"use strict";
	
	jQuery('#wclp_default_single_country, #wclp_default_single_state, #wclp_default_country, .wclp_pickup_time_select').select2();
	
	jQuery(".tipTip").tipTip();	
	
	jQuery('#wclp_ready_pickup_status_label_color').wpColorPicker({
		change: function(e, ui) {
			var color = ui.color.toString();			
			jQuery('.order-status-table .order-label.wc-ready-pickup').css('background',color);
		}, 
	});
	
	jQuery('#wclp_pickup_status_label_color').wpColorPicker({
		change: function(e, ui) {
			var color = ui.color.toString();			
			jQuery('.order-status-table .order-label.wc-pickup').css('background',color);
		}, 
	});		
	
	jQuery('body').click( function(){	
		if ( jQuery('.ready_pickup_row  button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
			save_custom_order_status(); 
		}
	});
	
	jQuery('.ready_pickup_row button.button.wp-color-result').click( function(){	
		if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{save_custom_order_status();}
	});
	
	jQuery('body').click( function(){	
		if ( jQuery('.picked_up_row button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
			save_custom_order_status(); 
		}
	});
	
	jQuery('.picked_up_row  button.button.wp-color-result').click( function(){	
		if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{save_custom_order_status();}
	});
	
	if(jQuery('#wclp_store_name').val() === ''){
		jQuery(".address-special").addClass('active');
		jQuery(".address-special").next('.panel').addClass('active').slideDown("slow");
		jQuery(".address-special").css('cursor', 'default');
		jQuery(".address-special").find('span.wclp-btn').show();
		jQuery(".address-special").find('span.dashicons').removeClass('dashicons-arrow-right-alt2');
		jQuery(".address-special").find('label').css('color','#212121');
	}
	
});

jQuery(document).on("click", ".accordion", function(){
	"use strict";
	var location_name = jQuery('#wclp_store_name').val();
	if(location_name === ''){
		jQuery('#wclp_store_name').next(".alp_error_msg").show();
		jQuery('#wclp_store_name').css('border-color','red');
		jQuery('#wclp_store_name').css('display','block');
	}
	if(location_name !== ''){
		if (jQuery(this).next('.panel').hasClass('active')) {
			//
		} else {
			jQuery('#wclp_store_name').next(".alp_error_msg").hide();
			jQuery('#wclp_store_name').css('border-color','');
			jQuery(".accordion").css('border-color','');
			jQuery(".accordion").removeClass('active');
			jQuery(".accordion").next('.panel').removeClass('active').slideUp("slow");
			jQuery(".accordion").css('cursor', '');
			jQuery(".accordion").find('span.wclp-btn').hide();
			jQuery(".accordion").find('span.dashicons').addClass('dashicons-arrow-right-alt2');
			jQuery(".accordion").find('label').css('color','');
			jQuery(this).addClass('active');
			jQuery(this).next('.panel').addClass('active').slideDown("slow");
			jQuery(this).css('cursor', 'default');
			jQuery(this).find('span.wclp-btn').show();
			jQuery(this).find('span.dashicons').removeClass('dashicons-arrow-right-alt2');
			jQuery(this).find('label').css('color','#212121');
		}
	}
});

jQuery(document).on("change", "#wclp_pickup_status_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-pickup').css('color',font_color);
	save_custom_order_status();
});

jQuery(document).on("change", "#wclp_ready_pickup_status_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-ready-pickup').css('color',font_color);
	save_custom_order_status();
});

jQuery(document).on("click", "#wclp_status_pickup", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});


/*ajex call for general tab form save*/	
jQuery(document).on("click", "#wclp_setting_tab_form .wclp-save", function(){
	"use strict";
	jQuery(this).parent().find(".spinner").addClass("active");
	var form = jQuery('#wclp_setting_tab_form');
	jQuery.ajax({
		url: ajaxurl,//csv_workflow_update,		
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			if( response.success === "true" ){
				jQuery("#wclp_setting_tab_form .spinner").removeClass("active");
				var snackbarContainer = document.querySelector('#wclp-toast-example');
				var data = {message: 'Your Settings have been successfully saved.'};
				snackbarContainer.MaterialSnackbar.showSnackbar(data);
			} else {
				//show error on front
			}
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".send_email_label input[type=checkbox]", function(){	
	save_custom_order_status();
});

jQuery(document).on("click", "#wclp_osm_tab_form .order-status-table .order_status_toggle", function(){	
	save_custom_order_status();
});

jQuery(document).on("click", "#wclp_status_ready_pickup", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});

jQuery(document).on("click", "#wclp_status_picked_up", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});

function save_custom_order_status(){
	jQuery("#wclp_osm_tab_form .order-status-table").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var form = jQuery('#wclp_osm_tab_form');
	jQuery.ajax({
		url: ajaxurl,//csv_workflow_update,		
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			jQuery("#wclp_osm_tab_form .order-status-table").unblock();		
			if( response.success === "true" ){
				jQuery("#wclp_osm_tab_form .spinner").removeClass("active");
				var snackbarContainer = document.querySelector('#wclp-toast-example');
				var data = {message: 'Your Settings have been successfully saved.'};
				snackbarContainer.MaterialSnackbar.showSnackbar(data);
			} else {
				//show error on front
			}
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
}

/*ajex call for general tab form save*/	
jQuery(document).on("click", "#wclp_location_tab_form .btn_location_submit", function(){
	"use strict";
	
	jQuery(".alp_error_msg").hide();
	var validation = true;
	var days = [ 'saturday', 'friday', 'thursday', 'wednesday', 'tuesday', 'monday', 'sunday' ];		
	for ( var i = 0, l = days.length; i < l; i++ ) {		
		
		jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour]"]').css('border-color','#ddd');
		jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour_end]"]').css('border-color','#ddd');
		jQuery('#'+days[ i ]).parent().parent().parent().css('border-color','');
		
		if(jQuery('#'+days[ i ]).prop("checked") == true){
			var wclp_store_hour = jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour]"] option:selected').val();
			var wclp_store_hour_end = jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour_end]"] option:selected').val();
			
			if(wclp_store_hour == ''){
				jQuery('#'+days[ i ]).parent().parent().parent().css('border-color','red');
				jQuery(".location-setting .accordion.heading.business-hours").trigger("click");	
				jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour]"]').css('border-color','red');
				jQuery('.alp_error_msg').show();
				validation=false;
			}
			if(wclp_store_hour_end == ''){
				jQuery('#'+days[ i ]).parent().parent().parent().css('border-color','red');
				jQuery(".location-setting .accordion.heading.business-hours").trigger("click");	
				jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour_end]"]').css('border-color','red');
				jQuery('.alp_error_msg').show();
				validation=false;
			}
			if(wclp_store_hour != '' && wclp_store_hour_end != ''){
				var st = minFromMidnight(wclp_store_hour);
				var et = minFromMidnight(wclp_store_hour_end);
				if(st>=et){
					jQuery('#'+days[ i ]).parent().parent().parent().css('border-color','red');
					jQuery(".location-setting .accordion.heading.business-hours").trigger("click");	
					jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour]"]').css('border-color','red');
					jQuery('select[name="wclp_store_days['+days[ i ]+'][wclp_store_hour_end]"]').css('border-color','red');
					jQuery('.alp_error_msg').show();
					validation=false;
				}
			}
		}
	}
	 
	var location_name = jQuery('#wclp_store_name').val();
	if(location_name === ''){
		if(!jQuery('.address-special').hasClass('active')){
			jQuery('.address-special').trigger('click');
		}		
		jQuery('#wclp_store_name').css('border-color','red');
		jQuery('#wclp_store_name').css('display','block');
		jQuery('.alp_error_msg').show();
		validation=false;
	} else {
		jQuery('#wclp_store_name').css('border-color','');
	}

	if(validation === true){
		jQuery("#wclp_location_tab_form .spinner").addClass("active");
		var form = jQuery('#wclp_location_tab_form');
		jQuery.ajax({
			url: ajaxurl,
			data: form.serialize(),
			type: 'POST',
			dataType:"json",	
			success: function(response) {
				if( response.success === "fail" ){
					jQuery("#wclp_location_tab_form .spinner").removeClass("active");
					jQuery('#wclp_location_tab_form .spinner').after('<div class="alp_error_msg">'+response.msg+'</div>');
					jQuery('.alp_error_msg').show();
				}
				if( response.success === "true" ){
					jQuery('.alp_error_msg').remove();
					jQuery("#wclp_location_tab_form .spinner").removeClass("active");
					var snackbarContainer = document.querySelector('#wclp-toast-example');
					var data = {message: 'Your Settings have been successfully saved.'};
					snackbarContainer.MaterialSnackbar.showSnackbar(data);
					window.history.pushState("object or string", alp_object.admin_url, "admin.php?page=local_pickup&tab=locations&section=edit&id="+response.id);
					jQuery("#location_id").val(response.id);
					jQuery(".accordion.heading").removeClass('active');
					jQuery('.accordion').next('.panel').removeClass('active').slideUp("slow");
					jQuery('.accordion').css('cursor', '');
					jQuery('.accordion').find('span.wclp-btn').hide();
					jQuery('.accordion').find('span.dashicons').addClass('dashicons-arrow-right-alt2');
					jQuery('.accordion').find('label').css('color','');
					wclp_update_edit_location_form();
					//location.reload();
				} else {
					//show error on front
				}
			},
			error: function(response) {
				console.log(response);			
			}
		});
	}
	return false;
});

function minFromMidnight(tm){
	"use strict";
	if(tm){
		var ampm= tm.substr(-2);
		var clk = tm.substr(0, 5);
		var m  = parseInt(clk.match(/\d+$/)[0], 10);
		var h  = parseInt(clk.match(/^\d+/)[0], 10);
		h += (ampm.match(/pm/i))? 12: 0;
		return h*60+m;
	}
}

jQuery(document).on("click", ".wclp_tab_input", function(){
	"use strict";
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	jQuery('.zorem-layout__header-breadcrumbs .header-breadcrumbs-last').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=local_pickup&tab="+tab;
	window.history.pushState({path:url},'',url);	
});
jQuery(document).on("click", ".pickup_days_checkbox", function(){
	"use strict";
	if(jQuery(this).prop("checked") === true){
		jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset span.hours').addClass('hours-time');
		jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', false);
	} else{
		jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset span.hours').removeClass('hours-time');
		jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', 'disabled');
	}
});
jQuery(document).ready(function(){
	"use strict";
	var pickup_days_checkbox = jQuery('.pickup_days_checkbox');
	jQuery(pickup_days_checkbox).each(function(){		
		if(jQuery(this).prop("checked") === true){
			jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset span.hours').addClass('hours-time');
			jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', false);
		} else{
			jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset span.hours').removeClass('hours-time');
			jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', 'disabled');
		}
	});
});


/*ajex call for general tab form save*/	
jQuery(document).on("change", "#wclp_default_single_country", function(){
	"use strict";
	
	var country = jQuery(this).val();
	var data = {
		action: 'wclp_update_state_dropdown',
		country: country,
	};		
	
	jQuery.ajax({
		url: ajaxurl,
		data: data,
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			if(response.state !== 'empty'){
				jQuery('#wclp_default_single_state').empty().append(response.state);				
				jQuery("#wclp_default_single_state").closest('tr').show();
			} else{
				jQuery('#wclp_default_single_state').empty();
				jQuery("#wclp_default_single_state").closest('tr').hide();
			}			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

/*ajex call for general tab form save*/	
jQuery(document).on("change", "#wclp_default_time_format", function(){
	"use strict";
	jQuery(".location-setting .panel.business-hours").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: 0.6
		}	
    });	
	var hour_format = jQuery(this).val();
	var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
	};
	var id = getUrlParameter('id');
	var data = {
		action: 'wclp_update_work_hours_list',
		hour_format: hour_format,
		id: id,
	};		
	
	jQuery.ajax({
		url: ajaxurl,
		data: data,
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			if(response.pickup_hours_div){
				jQuery(".pickup_hours_div").replaceWith(response.pickup_hours_div);
				jQuery(".wclp_pickup_time_select").select2();
				var pickup_days_checkbox = jQuery('.pickup_days_checkbox');
				jQuery(pickup_days_checkbox).each(function(){		
					if(jQuery(this).prop("checked") === true){
						jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', false);
					} else{
						jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', 'disabled');
					}
				});
				jQuery(".location-setting .panel.business-hours").unblock();
			}
				
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

function wclp_update_edit_location_form(){
	"use strict";
	var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
	};
	var id = getUrlParameter('id');
	var data = {
		action: 'wclp_update_edit_location_form',
		id: id,
	};		
	
	jQuery.ajax({
		url: ajaxurl,
		data: data,
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			if(response.edit_location_form){
				jQuery(".pickup-location-setting").replaceWith(response.edit_location_form);
				jQuery(".wclp_pickup_time_select").select2();
				jQuery("#wclp_selected_products, #wclp_excluded_products, #wclp_selected_categories, #wclp_excluded_categories").select2();
			}
				
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
}

jQuery(document).on("click", ".wclp-apply", function(){
	"use strict";
	jQuery(".alp_error_msg").remove();
	var hour_format = jQuery("#wclp_default_time_format").val();
	var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
	};
	
	var validation = true;
	var hasClassMorning = jQuery(this).parent().find(".morning-time").hasClass("hide-select-box");
	var hasClassAfternoon = jQuery(this).parent().find(".afternoon-time").hasClass("hide-select-box");
	var wclp_store_hour = jQuery(this).parent().find(".start");
	var wclp_store_hour_end = jQuery(this).parent().find(".end");
	var wclp_store_hour2 = jQuery(this).parent().find(".start2");
	var wclp_store_hour_end2 = jQuery(this).parent().find(".end2");
	
	var days = [];
	var day = jQuery(this).val();
	days.push(day);
	jQuery("input[name=weekday-"+jQuery(this).val()+"]:checked").each(function(){
		if(jQuery(this).val() !== day){
			days.push(jQuery(this).val());
		}
	});
	

	var data = {
		action: 'wclp_apply_work_hours',
		hour_format: hour_format,
		id: getUrlParameter('id'),
		days: days,
		wclp_store_hour: wclp_store_hour.val(),
		wclp_store_hour_end: wclp_store_hour_end.val(),
		wclp_store_hour2: wclp_store_hour2.val(),
		wclp_store_hour_end2: wclp_store_hour_end2.val(),
	};
	if(wclp_store_hour.val() !== '' && wclp_store_hour_end.val() !== '' && hasClassMorning === false ){
		var st1 = minFromMidnight(wclp_store_hour.val());
		var et1 = minFromMidnight(wclp_store_hour_end.val());
		if(st1>=et1){
			jQuery(wclp_store_hour).next(".select2").find(".select2-selection--single").css('border-color','red');
			jQuery(wclp_store_hour_end).next(".select2").find(".select2-selection--single").css('border-color','red');
			jQuery(this).after('<div class="alp_error_msg">End time must be greater than start time');
			jQuery('.alp_error_msg').show();
			validation=false;
		}
	}
	
	if( wclp_store_hour_end.val() !== '' && wclp_store_hour2.val() !== '' && hasClassAfternoon === false ){
		var st = minFromMidnight(wclp_store_hour_end.val());
		var et = minFromMidnight(wclp_store_hour2.val());
		if( st>=et){
			jQuery(wclp_store_hour_end).next(".select2").find(".select2-selection--single").css('border-color','red');
			jQuery(wclp_store_hour2).next(".select2").find(".select2-selection--single").css('border-color','red');
			jQuery(this).after('<div class="alp_error_msg">Start split time must be greater than end time');
			jQuery('.alp_error_msg').show();
			validation=false;
		}
	}
	if(wclp_store_hour2.val() && wclp_store_hour_end2.val() && hasClassAfternoon === false){
		var st2 = minFromMidnight(wclp_store_hour2.val());
		var et2 = minFromMidnight(wclp_store_hour_end2.val());
		if(st2>=et2){
			jQuery(wclp_store_hour2).next(".select2").find(".select2-selection--single").css('border-color','red');
			jQuery(wclp_store_hour_end2).next(".select2").find(".select2-selection--single").css('border-color','red');
			jQuery(this).after('<div class="alp_error_msg">End time must be greater than start interval time');
			jQuery('.alp_error_msg').show();
			validation=false;
		}
	}
	
	if(validation === true){
		jQuery('.alp-hours-popup').hide();
		jQuery(".location-setting .panel.business-hours").block({
			message: null,
			overlayCSS: {
				background: "#fff",
				opacity: 0.6
			}	
		});	
		jQuery.ajax({
			url: ajaxurl,
			data: data,
			type: 'POST',
			dataType:"json",	
			success: function(response) {
				if(response.pickup_hours_div){
					jQuery(".pickup_hours_div").replaceWith(response.pickup_hours_div);
					jQuery(".wclp_pickup_time_select").select2();
					var pickup_days_checkbox = jQuery('.pickup_days_checkbox');
					jQuery(pickup_days_checkbox).each(function(){		
						if(jQuery(this).prop("checked") === true){
							jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', false);
							
						} else{
							jQuery(this).closest('.wplp_pickup_duration').find('.wclp_pickup_time_fieldset').prop('disabled', 'disabled');
						}
					});
					jQuery(".location-setting .panel.business-hours").unblock();
				}
					
			},
			error: function(response) {
				console.log(response);			
			}
		});
	}
	return false;
}); 

jQuery(document).on("click", ".hours-time", function(){	
	"use strict";
	jQuery(this).parent().find(".alp-hours-popup").show();
});
jQuery(document).on("click", ".alp-apply-multiple", function(){	
	"use strict";
	jQuery(this).parent().find(".hours-popup").hide();
	jQuery(this).hide();
	jQuery(this).parent().find(".alp-hours-popup").hide();
	jQuery(this).parent().find(".apply-days-popup").show();
});
jQuery(document).on("click", ".back-popup", function(){	
	"use strict";
	jQuery(this).parent().parent().find(".alp-hours-popup").show();
	jQuery(this).parent().parent().find(".apply-days-popup").hide();
	jQuery(this).parent().parent().find(".hours-popup").show();
	jQuery(".alp-apply-multiple").show();
});
jQuery(document).on("click", ".popupclose, .popup_close_icon", function(){
	"use strict";
	jQuery('.alp-hours-popup').hide();
});

jQuery(document).on("click", ".alp-hours-popup .dashicons-trash", function(){
	"use strict";
	jQuery(this).parent().find("select").val("");
	jQuery(this).parent().find("select").select2();
});
 