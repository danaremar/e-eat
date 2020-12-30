/*
 * Customizer Scripts
 * Need to rewrite and clean up this file.
 */

jQuery(document).ready(function() {

    /**
     * Change description
     */
	jQuery(wclp_customizer.trigger_click).trigger( "click" );    
	jQuery('#customize-theme-controls #accordion-section-themes').hide();
	
	if(wclp_customizer.wclp_enable_ready_pickup_email == 'yes'){
		jQuery('#customize-control-wclp_ready_pickup_email_settings-wclp_enable_ready_pickup_status_email input').prop('disabled', true);
	}
	
	if(jQuery("#customize-control-woocommerce_customer_ready_pickup_order_settings-wclp_enable_ready_pickup_ga_tracking input").prop("checked") != true){	
		jQuery('#customize-control-woocommerce_customer_ready_pickup_order_settings-wclp_ready_pickup_analytics_link').hide();
	}
	
	if(wclp_customizer.wclp_enable_pickup_email == 'yes'){
		jQuery('#customize-control-wclp_pickup_email_settings-wclp_enable_pickup_status_email input').prop('disabled', true);
	}
	
	if(jQuery("#customize-control-woocommerce_customer_pickup_order_settings-wclp_enable_pickup_ga_tracking input").prop("checked") != true){	
		jQuery('#customize-control-woocommerce_customer_pickup_order_settings-wclp_pickup_analytics_link').hide();
	}
	
	if(jQuery('#customize-control-pickup_instruction_display_settings-hide_table_header input').prop("checked") == true){
		jQuery('#customize-control-pickup_instruction_display_settings-header_address_text').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_business_text').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_size').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_color').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_background_color').hide();
	} else{
		jQuery('#customize-control-pickup_instruction_display_settings-header_address_text').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_business_text').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_size').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_color').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_background_color').show();
	}
	
	if(jQuery('#customize-control-pickup_instruction_display_settings-hide_instruction_heading input').prop("checked") == true){
		jQuery('#customize-control-pickup_instruction_display_settings-location_box_heading').hide();
	} else{
		jQuery('#customize-control-pickup_instruction_display_settings-location_box_heading').show();
	}
	
	
});

jQuery(document).on("click", "#customize-control-pickup_instruction_display_settings-hide_table_header input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-pickup_instruction_display_settings-header_address_text').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_business_text').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_size').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_color').hide();
		jQuery('#customize-control-pickup_instruction_display_settings-header_background_color').hide();
	} else{
		jQuery('#customize-control-pickup_instruction_display_settings-header_address_text').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_business_text').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_size').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_font_color').show();
		jQuery('#customize-control-pickup_instruction_display_settings-header_background_color').show();
	}
});

jQuery(document).on("click", "#customize-control-pickup_instruction_display_settings-hide_instruction_heading input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-pickup_instruction_display_settings-location_box_heading').hide();
	} else{
		jQuery('#customize-control-pickup_instruction_display_settings-location_box_heading').show();	
	}
});

jQuery(document).on("change", "#customize-control-woocommerce_customer_ready_pickup_order_settings-wclp_enable_ready_pickup_ga_tracking input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-woocommerce_customer_ready_pickup_order_settings-wclp_ready_pickup_analytics_link').show();
	} else{
		jQuery('#customize-control-woocommerce_customer_ready_pickup_order_settings-wclp_ready_pickup_analytics_link').hide();
	}
});	
jQuery(document).on("change", "#_customize-input-customizer_ready_pickup_order_settings_enabled", function(){	
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-wclp_ready_pickup_email_settings-wclp_enable_ready_pickup_status_email input').prop('disabled', true);
	} else{
		jQuery('#customize-control-wclp_ready_pickup_email_settings-wclp_enable_ready_pickup_status_email input').removeAttr('disabled');
	}
});

jQuery(document).on("change", "#customize-control-woocommerce_customer_pickup_order_settings-wclp_enable_pickup_ga_tracking input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-woocommerce_customer_pickup_order_settings-wclp_pickup_analytics_link').show();
	} else{
		jQuery('#customize-control-woocommerce_customer_pickup_order_settings-wclp_pickup_analytics_link').hide();
	}
});	
jQuery(document).on("change", "#_customize-input-customizer_pickup_order_settings_enabled", function(){	
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-wclp_pickup_email_settings-wclp_enable_pickup_status_email input').prop('disabled', true);
	} else{
		jQuery('#customize-control-wclp_pickup_email_settings-wclp_enable_pickup_status_email input').removeAttr('disabled');
	}
});
    // Handle mobile button click
    function custom_size_mobile() {
    	// get email width.
    	var email_width = '684';
    	var ratio = email_width/304;
    	var framescale = 100/ratio;
    	var framescale = framescale/100;
    	jQuery('#customize-preview iframe').width(email_width+'px');
    	jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(' + framescale + ')',
				'-moz-transform'    : 'scale(' + framescale + ')',
				'-ms-transform'     : 'scale(' + framescale + ')',
				'-o-transform'      : 'scale(' + framescale + ')',
				'transform'         : 'scale(' + framescale + ')'
		});
    }
	jQuery('#customize-footer-actions .preview-mobile').click(function(e) {
		custom_size_mobile();
	});
		jQuery('#customize-footer-actions .preview-desktop').click(function(e) {
		jQuery('#customize-preview iframe').width('100%');
		jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(1)',
				'-moz-transform'    : 'scale(1)',
				'-ms-transform'     : 'scale(1)',
				'-o-transform'      : 'scale(1)',
				'transform'         : 'scale(1)'
		});
	});
	jQuery('#customize-footer-actions .preview-tablet').click(function(e) {
		jQuery('#customize-preview iframe').width('100%');
		jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(1)',
				'-moz-transform'    : 'scale(1)',
				'-ms-transform'     : 'scale(1)',
				'-o-transform'      : 'scale(1)',
				'transform'         : 'scale(1)'
		});
	});

(function ( api ) {
    api.section( 'wclp_customer_pickup_email', function( section ) {	
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wclp_customizer.pickup_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'wclp_customer_ready_pickup_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wclp_customizer.ready_pickup_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'wclp_pickup_instaruction', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wclp_customizer.pickup_instaruction_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );


jQuery(document).on("change", ".wclp_preview_order_select", function(){
	var wclp_preview_order_id = jQuery(this).val();
	var data = {
		action: 'update_email_preview_order',
		wclp_preview_order_id: wclp_preview_order_id,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: data,
		type: 'POST',
		success: function(response) {			
			jQuery(".wclp_preview_order_select option[value="+wclp_preview_order_id+"]").attr('selected', 'selected');			
		},
		error: function(response) {
			console.log(response);			
		}
	});	
});

jQuery(document).on("change", ".preview_order_select", function(){
	var wclp_preview_order_id = jQuery(this).val();
	var data = {
		action: 'update_email_preview_order',
		wclp_preview_order_id: wclp_preview_order_id,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: data,
		type: 'POST',
		success: function(response) {			
			jQuery(".preview_order_select option[value="+wclp_preview_order_id+"]").attr('selected', 'selected');			
		},
		error: function(response) {
			console.log(response);			
		}
	});	
});