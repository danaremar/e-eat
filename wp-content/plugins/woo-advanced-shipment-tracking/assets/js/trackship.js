( function( $, data, wp, ajaxurl ) {				
	var $wc_ast_trackship_form = $("#wc_ast_trackship_form");	
		
	
	var trackship_js = {
		
		init: function() {						
							
			$("#wc_ast_trackship_form").on( 'click', '.woocommerce-save-button', this.save_wc_ast_trackship_form );			
			$("#trackship_tracking_page_form").on( 'click', '.woocommerce-save-button', this.save_trackship_tracking_page_form );
			$("#trackship_late_shipments_form").on( 'click', '.woocommerce-save-button', this.save_trackship_late_shipments_form );
			$(".tipTip").tipTip();

		},				
		
		save_wc_ast_trackship_form: function( event ) {
			event.preventDefault();
			
			$("#wc_ast_trackship_form").find(".spinner").addClass("active");
			//$wc_ast_settings_form.find(".success_msg").hide();
			var ajax_data = $("#wc_ast_trackship_form").serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$("#wc_ast_trackship_form").find(".spinner").removeClass("active");
				
				jQuery("#trackship_settings_snackbar").addClass('show_snackbar');	
				jQuery("#trackship_settings_snackbar").text(trackship_script.i18n.data_saved);			
				setTimeout(function(){ jQuery("#trackship_settings_snackbar").removeClass('show_snackbar'); }, 3000);										
			});
			
		},
		save_trackship_tracking_page_form: function( event ) {			
			event.preventDefault();
			
			$("#trackship_tracking_page_form").find(".spinner").addClass("active");			
			var ajax_data = $("#trackship_tracking_page_form").serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$("#trackship_tracking_page_form").find(".spinner").removeClass("active");
				
				jQuery("#trackship_settings_snackbar").addClass('show_snackbar');	
				jQuery("#trackship_settings_snackbar").text(trackship_script.i18n.data_saved);			
				setTimeout(function(){ jQuery("#trackship_settings_snackbar").removeClass('show_snackbar'); }, 3000);
				
				jQuery('.tracking_page_preview').prop("disabled", false);	
			});			
		},
		save_trackship_late_shipments_form: function( event ) {			
			event.preventDefault();
			
			$("#trackship_late_shipments_form").find(".spinner").addClass("active");			
			var ajax_data = $("#trackship_late_shipments_form").serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$("#trackship_late_shipments_form").find(".spinner").removeClass("active");
				
				jQuery("#trackship_settings_snackbar").addClass('show_snackbar');	
				jQuery("#trackship_settings_snackbar").text(trackship_script.i18n.data_saved);			
				setTimeout(function(){ jQuery("#trackship_settings_snackbar").removeClass('show_snackbar'); }, 3000);								
			});			
		},	
	};
	$(window).on('load',function () {
		trackship_js.init();	
	});	
})( jQuery, trackship_script, wp, ajaxurl );

jQuery( document ).ready(function() {	
	jQuery('#wc_ast_select_border_color').wpColorPicker({
		change: function(e, ui) {
			var color = ui.color.toString();		
			jQuery('#tracking_preview_iframe').contents().find('.col.tracking-detail').css('border','1px solid '+color);
			jQuery('.tracking_page_preview').prop("disabled", true);
		},
	});	
});

jQuery(document).on("change", "#wc_ast_use_tracking_page", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('.tracking_page_preview').show();
		jQuery('.tracking_page_design_table').show();
	} else{		
		jQuery('.tracking_page_preview').hide();
		jQuery('.tracking_page_design_table').hide();
	}
});

jQuery(document).on("change", ".select_t_layout_section .radio-img", function(){
	jQuery('.tracking_page_preview').prop("disabled", true);
});

jQuery(document).on("click", "#wc_ast_link_to_shipping_provider", function(){	
	jQuery('.tracking_page_preview').prop("disabled", true);
});

jQuery(document).on("click", "#wc_ast_hide_tracking_provider_image", function(){	
	jQuery('.tracking_page_preview').prop("disabled", true);
});

jQuery(document).on("click", "#wc_ast_hide_tracking_events", function(){
	jQuery('.tracking_page_preview').prop("disabled", true);
});

jQuery(document).on("click", "#wc_ast_remove_trackship_branding", function(){
	jQuery('.tracking_page_preview').prop("disabled", true);
});

jQuery(document).on("click", ".tracking_page_preview", function(){	
	jQuery("#wc_ast_trackship_form").find(".spinner").addClass("active");
	document.getElementById('tracking_preview_iframe').contentDocument.location.reload(true);
	
	jQuery('#tracking_preview_iframe').load(function(){
		jQuery("#wc_ast_trackship_form").find(".spinner").removeClass("active");
		jQuery('.tracking_page_preview_popup').show();	
		var iframe = document.getElementById("tracking_preview_iframe");
		iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';  		
	});	
});
jQuery(document).on("click", ".popupclose", function(){	
	jQuery('.tracking_page_preview_popup').hide();
});
jQuery(document).on("click", ".popup_close_icon", function(){	
	jQuery('.tracking_page_preview_popup').hide();	
});

jQuery( document ).ready(function() {	
	if(jQuery('#wc_ast_use_tracking_page').prop("checked") == true){
		jQuery('.tracking_page_preview').show();
		jQuery('.tracking_page_design_table').show();		
	} else{
		jQuery('.tracking_page_preview').hide();
		jQuery('.tracking_page_design_table').hide();		
	}	
});

jQuery(document).on("click", ".tab_input", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	jQuery('.zorem-layout__header-breadcrumbs .header-breadcrumbs-last').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=trackship-for-woocommerce&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).click(function(){
	var $trigger = jQuery(".trackship_dropdown");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".trackship-dropdown-content").hide();
    }   
});

jQuery(document).on("click", ".trackship-dropdown-menu", function(){	
	jQuery('.trackship-dropdown-content').show();
});

jQuery(document).on("click", ".trackship-dropdown-content li a", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	var section = jQuery(this).data('section');
	jQuery('.inner_tab_section').hide();
	jQuery('.trackship_nav_div').find("[data-tab='" + tab + "']").prop('checked', true);
	jQuery('#'+section).show();
	jQuery('.zorem-layout__header-breadcrumbs .header-breadcrumbs-last').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=trackship-for-woocommerce&tab="+tab;
	window.history.pushState({path:url},'',url);
	jQuery(".trackship-dropdown-content").hide();
});

jQuery(document).on("click", ".bulk_shipment_status_button", function(){
	jQuery("#content3").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery("#content3").unblock();
			jQuery( '.bulk_shipment_status_success' ).show();
			jQuery( '.bulk_shipment_status_button' ).attr("disabled", true)
			//window.location.href = response;			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".bulk_shipment_status_button_for_empty_balance", function(){
	jQuery("#content3").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_for_empty_balance_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery("#content3").unblock();
			jQuery( '.bulk_shipment_status_button_for_empty_balance' ).after( "<div class='bulk_shipment_status_success'>Tracking info sent to Trackship for all Orders.</div>" );
			jQuery( '.bulk_shipment_status_button_for_empty_balance' ).attr("disabled", true);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".bulk_shipment_status_button_for_connection_issue", function(){
	jQuery("#content3").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_for_do_connection_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery("#content3").unblock();
			jQuery( '.bulk_shipment_status_button_for_connection_issue' ).after( "<div class='bulk_shipment_status_success'>Tracking info sent to Trackship for all Orders.</div>" );
			jQuery( '.bulk_shipment_status_button_for_connection_issue' ).attr("disabled", true);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});
jQuery(document).on("click", ".open_ts_video", function(){
	jQuery('.ts_video_popup').show();	 
});
jQuery(document).on("click", ".ts_video_popup .popupclose", function(){
	jQuery('#ts_video').each(function(index) {
		jQuery(this).attr('src', jQuery(this).attr('src'));
		return false;
    });
	jQuery('.ts_video_popup').hide();
});

jQuery(document).on("click", ".tool_link", function(){
	jQuery('#tab_tools').trigger( "click" );
});

jQuery(document).on("change", "#wc_ast_trackship_page_id", function(){
	var wc_ast_trackship_page_id = jQuery(this).val();
	if(wc_ast_trackship_page_id == 'other'){
		jQuery('.trackship_other_page_fieldset').show();
	} else{
		jQuery('.trackship_other_page_fieldset').hide();
	}
});

jQuery(document).on("change", ".shipment_status_toggle input", function(){
	jQuery("#content5 ").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	
	var settings_data = jQuery(this).data("settings");
	
	if(jQuery(this).prop("checked") == true){
		var wcast_enable_status_email = 1;
		jQuery(this).closest('tr').addClass('enable');
		jQuery(this).closest('tr').removeClass('disable');
	} else{
		jQuery(this).closest('tr').addClass('disable');
		jQuery(this).closest('tr').removeClass('enable');
		if( settings_data == 'late_shipments_email_settings') jQuery('.late-shipments-email-content-table').hide();	
	}
	
	var id = jQuery(this).attr('id');
	
	var ajax_data = {
		action: 'update_shipment_status_email_status',
		id: id,
		wcast_enable_status_email: wcast_enable_status_email,
		settings_data: settings_data,		
	};
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery("#content5 ").unblock();						
		},
		error: function(response) {					
		}
	});
});

jQuery(document).on("click", ".late_shipments_a", function(){
	jQuery('.late-shipments-email-content-table').toggle();
});