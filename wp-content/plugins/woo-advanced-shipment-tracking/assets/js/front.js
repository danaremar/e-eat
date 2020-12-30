jQuery(document).on("submit", ".order_track_form", function(){
	var form = jQuery(this);
	var error;
	var order_id = form.find("#order_id");
	var order_email = form.find("#order_email");
	
	if( order_id.val() === '' ){		
		showerror( order_id );error = true;
	} else{
		hideerror(order_id);
	}	
	if(order_email.val() == '' ){		
		showerror(order_email);error = true;
	} else {
		hideerror(order_email);
	}
	
	if(error == true){
		return false;
	}
	
	jQuery(".order_track_form").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	
	jQuery.ajax({
		url: zorem_ajax_object.ajax_url,		
		data: form.serialize(),
		type: 'POST',
		dataType: "json",
		success: function(response) {			
			if(response.success == 'true'){
				jQuery('.track-order-section').replaceWith(response.html);
			} else{				
				jQuery(".track_fail_msg").text(response.message);
				jQuery(".track_fail_msg").show();				
			}			
			jQuery(".order_track_form").unblock();	
		},
		error: function(jqXHR, exception) {			
			if(jqXHR.status == 302){				
				jQuery(".track_fail_msg").show();
				jQuery(".track_fail_msg").text('Tracking details not found.');
				jQuery(".order_track_form").unblock();	
			} else{				
				jQuery(".track_fail_msg").show();
				jQuery(".track_fail_msg").text('There are some issue with Trackship.');
				jQuery(".order_track_form").unblock();	
			}	
			
		}
	});
	return false;
});
jQuery(document).on("click", ".back_to_tracking_form", function(){
	jQuery('.tracking-detail').hide();
	jQuery('.track-order-section').show();
});
jQuery(document).on("click", ".view_table_rows", function(){
	jQuery(this).hide();
	jQuery(this).closest('.shipment_progress_div').find('.hide_table_rows').show();
	jQuery(this).closest('.shipment_progress_div').find('table.tracking-table tr:nth-child(n+3)').show();	
});
jQuery(document).on("click", ".hide_table_rows", function(){
	jQuery(this).hide();
	jQuery(this).closest('.shipment_progress_div').find('.view_table_rows').show();
	jQuery(this).closest('.shipment_progress_div').find('table.tracking-table tr:nth-child(n+3)').hide();	
});

jQuery(document).on("click", ".view_old_details", function(){
	jQuery(this).hide();
	jQuery(this).closest('.tracking-details').find('.hide_old_details').show();
	jQuery(this).closest('.tracking-details').find('.old-details').fadeIn();
});
jQuery(document).on("click", ".hide_old_details", function(){
	jQuery(this).hide();
	jQuery(this).closest('.tracking-details').find('.view_old_details').show();
	jQuery(this).closest('.tracking-details').find('.old-details').fadeOut();	
});

jQuery(document).on("click", ".view_destination_old_details", function(){
	jQuery(this).hide();
	jQuery(this).closest('.tracking-details').find('.hide_destination_old_details').show();
	jQuery(this).closest('.tracking-details').find('.old-destination-details').fadeIn();
});
jQuery(document).on("click", ".hide_destination_old_details", function(){
	jQuery(this).hide();
	jQuery(this).closest('.tracking-details').find('.view_destination_old_details').show();
	jQuery(this).closest('.tracking-details').find('.old-destination-details').fadeOut();	
});

function showerror(element){
	element.css("border-color","red");
}
function hideerror(element){
	element.css("border-color","");
}