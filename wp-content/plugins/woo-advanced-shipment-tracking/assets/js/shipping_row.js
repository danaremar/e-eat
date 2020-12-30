( function( $, data, wp, ajaxurl ) {	
		
	var $wc_ast_settings_form = $("#wc_ast_settings_form");
	var $wc_ast_trackship_form = $("#wc_ast_trackship_form");
	var $wc_ast_addons_form = $("#wc_ast_addons_form");
		
	
	var wc_table_rate_rows = {
		
		init: function() {						
			
			$wc_ast_settings_form.on( 'click', '.woocommerce-save-button', this.save_wc_ast_settings_form );			
			
			$(".tipTip").tipTip();

		},

		save_wc_ast_settings_form: function( event ) {
			event.preventDefault();			
			$wc_ast_settings_form.find(".spinner").addClass("active");
			var ajax_data = $wc_ast_settings_form.serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {				
				$wc_ast_settings_form.find(".spinner").removeClass("active");
				jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
				jQuery("#ast_settings_snackbar").text(shipment_tracking_table_rows.i18n.data_saved);			
				setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);
			});
			
		},				
	};
	
	$(window).on('load',function () {
		wc_table_rate_rows.init();	
	});	
})( jQuery, shipment_tracking_table_rows, wp, ajaxurl );


jQuery(document).on("change", ".wc_ast_default_provider", function(){
	jQuery(".d_s_select_section ").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	var default_provider = jQuery('.wc_ast_default_provider').val();
	var ajax_data = {
		action: 'update_default_provider',
		default_provider: default_provider,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".d_s_select_section ").unblock();
			jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
			jQuery("#ast_settings_snackbar").text(shipment_tracking_table_rows.i18n.data_saved);			
			setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);			
		},
		error: function(response) {					
		}
	});
});
	var file_frame;
	jQuery('.upload_image_button').on('click', function(product) {
		product.preventDefault();
		var image_id = jQuery(this).siblings(".image_id");
		var image_path = jQuery(this).siblings(".image_path");
		
		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}
	
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Upload Media',
			button: {
				text: 'Add',
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
	
		// When a file is selected, run a callback.
		file_frame.on('select', function(){     
			attachment = file_frame.state().get('selection').first().toJSON();       
			var id = attachment.id;        
			var url = attachment.url;     
			image_path.val(url);
			image_id.val(id);
		});
		// Finally, open the modal
		file_frame.open();
	});

jQuery(document).on("submit", "#wc_ast_upload_csv_form", function(){
	jQuery('.csv_upload_status li').remove();	
	jQuery('.bulk_upload_status_tr').hide();
	jQuery('.progress_title').hide();	
	var form = jQuery('#wc_ast_upload_csv_form');	
	var error;
	var trcking_csv_file = form.find("#trcking_csv_file");
	var replace_tracking_info = jQuery("#replace_tracking_info").prop("checked");
	var date_format_for_csv_import = jQuery('input[name="date_format_for_csv_import"]:checked').val();
	
	if(replace_tracking_info == true){
		replace_tracking_info = 1;	
	} else{
		replace_tracking_info = 0;
	}		
	
	var ext = jQuery('#trcking_csv_file').val().split('.').pop().toLowerCase();	
	
	if( trcking_csv_file.val() === '' ){		
		showerror( trcking_csv_file );
		error = true;
	} else{
		if(ext != 'csv'){
			alert(shipment_tracking_table_rows.i18n.upload_only_csv_file);	
			showerror( trcking_csv_file );
			error = true;
		} else{
			hideerror(trcking_csv_file);
		}
	}
	
	if(error == true){
		return false;
	}
	

             var regex = /([a-zA-Z0-9\s_\\.\-\(\):])+(.csv|.txt)$/;
             if (regex.test(jQuery("#trcking_csv_file").val().toLowerCase())) {
                 if (typeof (FileReader) != "undefined") {
                     var reader = new FileReader();
                     reader.onload = function (e) {
                         var trackings = new Array();
                         var rows = e.target.result.split("\n");						 
						 if(rows.length <= 1){
							 alert('There are some issue with CSV file.');
							 return false;
						 }		
                         for (var i = 1; i < rows.length; i++) {
                             var cells = rows[i].split(",");
                             if (cells.length > 1) {
                                 var tracking = {};
                                 tracking.order_id = cells[0];								 
                                 tracking.tracking_provider = cells[1];
                                 tracking.tracking_number = cells[2];
								 tracking.date_shipped = cells[3];
								 tracking.status_shipped = cells[4];
								 if(cells[5]){
									tracking.sku = cells[5]; 
								 }
								 if(cells[6]){
									tracking.qty = cells[6]; 
								 }
								 if(tracking.order_id){
									trackings.push(tracking);	
								 }						
                             }
                         }  				
 				
				var csv_length = trackings.length;
				
				jQuery("#wc_ast_upload_csv_form")[0].reset();												
				
				jQuery(".progress-moved .progress-bar2").css('width',0+'%');
				
				
				jQuery(".progress_step1").removeClass("active");
				jQuery(".progress_step1").addClass("done");
				jQuery(".progress_step2").addClass("active");
				jQuery(".upload_csv_div").hide();
				jQuery(".bulk_upload_status_div").show();
				
				var run_data = 0; 
                
				var tracking_import = jQuery(trackings).each(function(index, element) {
					var sku = '';
					var qty = '';
					var order_id = trackings[index]['order_id'];
					var tracking_provider = trackings[index]['tracking_provider'];
					var tracking_number = trackings[index]['tracking_number'];
					var date_shipped = trackings[index]['date_shipped'];
					var status_shipped = trackings[index]['status_shipped'];
					var success_class = 0;
					var error_class = 0;
					var error_message = '';
					var success_message = '';
					if(trackings[index]['sku']){
						var sku = trackings[index]['sku'];	
					}					
					if(trackings[index]['qty']){
						var qty = trackings[index]['qty'];
					}						
					
					var data = {
							action: 'wc_ast_upload_csv_form_update',
							order_id: order_id,
							date_format_for_csv_import: date_format_for_csv_import,
							tracking_provider: tracking_provider,
							tracking_number: tracking_number,
							date_shipped: date_shipped,
							status_shipped: status_shipped,
							sku: sku,
							qty: qty,
							replace_tracking_info: replace_tracking_info,
							trackings: trackings,	
						};
				
					var option = {
				
						url: ajaxurl,
						data: data,
						type: 'POST',
						success:function(data){								
							jQuery('.progress_number').html((index+1)+'/'+csv_length);
							
							jQuery('.csv_upload_status').append(data);
							var progress = (index+1)*100/csv_length;
							jQuery('.bulk_upload_status_tr').show();
							jQuery('.progress_title').show();	
							
							jQuery(".progress-moved .progress-bar2").css('width',progress+'%');
							
							var shipping_provider_error_class = 0;
							var tracking_number_error_class = 0;
							var empty_date_shipped_error_class = 0;
							var invalid_date_shipped_error_class = 0;
							var invalid_order_id_error_class = 0;
							var invalid_tracking_data_error_class = 0;
							
							if(progress == 100){
								jQuery( ".csv_upload_status li" ).each(function( index ) {
									if( this.className == 'shipping_provider_error' || this.className == 'tracking_number_error' || this.className == 'empty_date_shipped_error' || this.className == 'invalid_date_shipped_error' || this.className == 'invalid_order_id_error' || this.className == 'invalid_tracking_data_error' ){
										error_class++;
									}
									if(this.className == 'success'){										
										success_class++;
									}
									if( this.className == 'shipping_provider_error' )shipping_provider_error_class++;
									if( this.className == 'tracking_number_error' )tracking_number_error_class++;
									if( this.className == 'empty_date_shipped_error' )empty_date_shipped_error_class++;		
									if( this.className == 'invalid_date_shipped_error' )invalid_date_shipped_error_class++;
									if( this.className == 'invalid_order_id_error' )invalid_order_id_error_class++;
									if( this.className == 'invalid_tracking_data_error' )invalid_tracking_data_error_class++;
								});									
								
								jQuery('.progress_title').hide();
								jQuery(".progress_step2").removeClass("active");
								jQuery(".progress_step2").addClass("done");								
								jQuery(".progress_step3").addClass("active");
								jQuery(".bulk_upload_status_div").addClass("csv_import_done");
								jQuery(".bulk_upload_status_action ").show();
								
								if(error_class > 0){
									error_message = error_class+' tracking numbers import failed';
									jQuery(".bulk_upload_status_overview_td.csv_fail_msg").show();									
									jQuery(".bulk_upload_status_overview_td.csv_fail_msg span").html(error_message);
								} else{
									jQuery(".bulk_upload_status_overview_td.csv_fail_msg").hide();	
								}
								
								if(success_class > 0){
									jQuery(".bulk_upload_status_overview_td.csv_success_msg").show();								
									success_message = success_class+' tracking numbers imported successfully';
									jQuery(".bulk_upload_status_overview_td.csv_success_msg span").html(success_message);
								} else{
									jQuery(".bulk_upload_status_overview_td.csv_success_msg").hide();	
								}

								if(invalid_order_id_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+invalid_order_id_error_class+' tracking numbers import failed due to invalid order id</li>');	
								}
								if(shipping_provider_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+shipping_provider_error_class+' tracking numbers import failed due to invalid shipping provider</li>');	
								}
								if(tracking_number_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+tracking_number_error_class+' tracking numbers import failed due to empty tracking number</li>');	
								}
								if(empty_date_shipped_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+empty_date_shipped_error_class+' tracking numbers import failed due to empty date shipped</li>');	
								}
								if(invalid_date_shipped_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+invalid_date_shipped_error_class+' tracking numbers import failed due to invalid date shipped</li>');	
								}
								if(invalid_tracking_data_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+invalid_tracking_data_error_class+' tracking numbers import failed due to invalid tracking data</li>');	
								}	
																
								jQuery(".bulk_upload_status_heading_tr h2").html("Import Completed!");								
																
								jQuery(".bulk_upload_status_heading_tr p").hide();								
								jQuery(".csv_upload_status").hide();	
								jQuery('.bulk_upload_status_tr').hide();
							}												
						},
				
					};
				
					jQuery.ajaxQueue.addRequest(option);
				
					jQuery.ajaxQueue.run();					
					run_data++;					
				});											
                
				}				
                     reader.readAsText(jQuery("#trcking_csv_file")[0].files[0]);
			
			
                 } else {
                     alert(shipment_tracking_table_rows.i18n.browser_not_html);
                 }
             } else {
                 alert(shipment_tracking_table_rows.i18n.upload_valid_csv_file);
             }
	return false;
});

jQuery(document).on("click", ".view_csv_error_details", function(){
	jQuery('.bulk_upload_status_detail_error_tr').toggle();
	var tr_visible = jQuery('.bulk_upload_status_detail_error_tr').is(":visible");
	if(tr_visible == true){
		jQuery('.view_csv_error_details').text('hide details');
	} else{
		jQuery('.view_csv_error_details').text('view details');
	}
});
	
jQuery(document).on("click", ".csv_upload_again", function(){
	jQuery('.csv_upload_status li').remove();	
	jQuery('.csv_upload_status').show();	
	jQuery('.bulk_upload_status_tr').hide();
	jQuery('.bulk_upload_status_overview_td').hide();	
	jQuery('.progress_title').hide();
	jQuery(".bulk_upload_status_heading_tr h2").html('Importing'+'<span class="spinner is-active"></span>');
	jQuery(".bulk_upload_status_heading_tr p").show();
	jQuery(".progress_step2").removeClass("active");
	jQuery(".progress_step2").removeClass("done");								
	jQuery(".progress_step3").removeClass("done");								
	jQuery(".progress_step3").removeClass("active");
	jQuery(".progress_step1").removeClass("done");
	jQuery(".progress_step1").addClass("active");
	jQuery(".bulk_upload_status_div ").removeClass("csv_import_done");
	jQuery(".bulk_upload_status_action ").hide();
	jQuery('.bulk_upload_status_div').hide();
	jQuery('.upload_csv_div').show();
	jQuery('.bulk_upload_status_detail_error_tr').hide();
	jQuery('.csv_error_details_ul li').remove();
}); 

jQuery(document).on("change", "#wcast_enable_late_shipments_admin_email", function(){	
	if(jQuery(this).prop("checked") == true){
		var wcast_enable_late_shipments_email = 1;
	}
	var id = jQuery(this).attr('id');
	var settings_data = jQuery(this).data("settings");
	var ajax_data = {
		action: 'update_enable_late_shipments_email',
		id: id,
		wcast_enable_late_shipments_email: wcast_enable_late_shipments_email,		
		settings_data: settings_data,
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {				
			jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
			jQuery("#ast_settings_snackbar").text(shipment_tracking_table_rows.i18n.data_saved);			
			setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);						
		},
		error: function(response) {					
		}
	});
});


jQuery(document).on("click", ".status_filter a", function(){
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	jQuery('.status_filter a').removeClass('active');
	jQuery('#search_provider').removeAttr('value');
	jQuery(this).addClass('active');
	var status = jQuery(this).data('status');
	var ajax_data = {
		action: 'filter_shipiing_provider_by_status',
		status: status,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);	
			jQuery("#content1 ").unblock();						
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      					
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});
			jQuery(".woocommerce-help-tip").tipTip();				
		},
		error: function(response) {					
		}
	});
});

jQuery(document).on("click", ".status_slide", function(){
	var id = jQuery(this).val();
	if(jQuery(this).prop("checked") == true){
       var checked = 1;
	   jQuery(this).closest('.provider').addClass('active_provider');
	   jQuery('#make_default_'+id).prop('disabled', false);
	   jQuery('#default_label_'+id).removeClass('disable_label');
    } else{
		var checked = 0;
		jQuery(this).closest('.provider').removeClass('active_provider');
		jQuery('#make_default_'+id).prop('disabled', true);
		jQuery('#make_default_'+id).prop('checked', false);
		jQuery('#default_label_'+id).addClass('disable_label');
	}
	

	var error;	
	var ajax_data = {
		action: 'update_shipment_status',
		id: id,
		checked: checked,	 
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {						
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("change", ".make_provider_default", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	if(jQuery(this).prop("checked") == true){
	   jQuery('.make_provider_default').removeAttr('checked');
       var checked = 1;	   
	   jQuery(this).prop('checked',true);	   
    } else{
		var checked = 0;		
	}
	var id = jQuery(this).data('id');
	
	var error;	
	var default_provider = jQuery(this).val();
	var ajax_data = {
		action: 'update_default_provider',
		default_provider: default_provider,	
		id: id,
		checked: checked,			
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery("#content1 ").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on( "input", "#search_provider", function(){	
	jQuery('.status_filter a').removeClass('active');
	jQuery("[data-status=all]").addClass('active');	
	
	var ajax_data = {
		action: 'filter_shipiing_provider_by_status',
		status: 'all',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);								
			var provider_found = false;	
			var searchvalue = jQuery("#search_provider").val().toLowerCase().replace(/\s+/g, '');
			
			jQuery('.provider_list .provder_table tbody tr').each(function() {
				var provider = jQuery(this).find('.provider_name').text().toLowerCase().replace(/\s+/g, '');		
				var country = jQuery(this).find('.provider_country').text().toLowerCase().replace(/\s+/g, '');
				
				var hasprovider = provider.indexOf(searchvalue)!==-1;
				var hascountry= country.indexOf(searchvalue)!==-1;
				
				if (hasprovider || hascountry) {						
					jQuery(this).show();					
					provider_found = true;	
				} else {					
					jQuery(this).remove();
				}
			});	
			
			if(provider_found == false){
				jQuery(".provider_list").append('<h3 class="not_found_label">No Shipping Providers Found.</h3>');
			} else{
				jQuery(".not_found_label").remove();
			}
			
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {					
		}
	});	
});

jQuery(document).on("click", ".add_custom_provider", function(){	
	jQuery('.add_provider_popup').show();
	jQuery('.custom_provider_instruction').show();
});
jQuery(document).on("click", ".popupclose", function(){
	jQuery('.add_provider_popup').hide();
	jQuery('.edit_provider_popup').hide();
	jQuery('.sync_provider_popup').hide();
	jQuery('.how_to_video_popup').hide();
	jQuery('.ts_video_popup').hide();
	jQuery('.import_tracking_video_popup').hide();
});
jQuery(document).on("click", ".popup_close_icon", function(){
	jQuery('.add_provider_popup').hide();
	jQuery('.edit_provider_popup').hide();
	jQuery('.sync_provider_popup').hide();	
});
jQuery(document).on("click", ".popupclose_btn", function(){
	jQuery('.add_provider_popup').hide();
	jQuery('.edit_provider_popup').hide();
	jQuery('.sync_provider_popup').hide();
	jQuery('.how_to_video_popup').hide();
	jQuery('.ts_video_popup').hide();
	jQuery('.import_tracking_video_popup').hide();
});
jQuery(document).on("click", ".close_synch_popup", function(){		
	jQuery('.sync_provider_popup').hide();
	jQuery(".sync_message").show();
	jQuery(".reset_db_fieldset").show();
	jQuery(".synch_result").hide();
	jQuery(".reset_db_message").hide();
	jQuery(".view_synch_details").remove();
	jQuery(".updated_details").remove();	
	
	jQuery(".sync_providers_btn").show();
	jQuery(".close_synch_popup").hide();
});
 jQuery(document).on("submit", "#add_provider_form", function(){
	
	var form = jQuery('#add_provider_form');
	var error;
	var shipping_provider = jQuery(".add_provider_popup .shipping_provider");
	var shipping_country = jQuery(".add_provider_popup .shipping_country");
	var thumb_url = jQuery(".add_provider_popup .thumb_url");
	var tracking_url = jQuery(".add_provider_popup .tracking_url");	
	
	if( shipping_provider.val() === '' ){				
		
		showerror(shipping_provider);
		error = true;
	} else{		
		hideerror(shipping_provider);
	}	
	
	if( shipping_country.val() === '' ){				
		showerror(shipping_country);
		error = true;
	} else{		
		hideerror(shipping_country);
	}	
	
	
	if(error == true){
		return false;
	}	
	
	jQuery(".add_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	jQuery('#search_provider').removeAttr('value');
	
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();									
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=custom]").addClass('active');	
			jQuery('.add_provider_popup').hide();			
			jQuery(".add_provider_popup").unblock();
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".remove", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( shipment_tracking_table_rows.i18n.delete_provider );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
	var id = jQuery(this).data('pid');
	
	var error;	
	var default_provider = jQuery(this).val();
	var ajax_data = {
		action: 'woocommerce_shipping_provider_delete',		
		provider_id: id,
	};
	
	jQuery('#search_provider').removeAttr('value');
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=custom]").addClass('active');				
			jQuery("#content1").unblock();	
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on( "click", ".add_more_api_provider", function(){	
	jQuery(this).closest('.api_provider_name_container').append('<div class="api_provider_new"><input type="text" name="api_provider_name[]" class="api_provider_name" value="" placeholder="API Name"><span class="dashicons dashicons-remove remove_more_api_provider"></span></div>');
});

jQuery(document).on("click",".remove_more_api_provider", function(e){ //user click on remove text links
    e.preventDefault(); 
	jQuery(this).parent('.api_provider_new').remove(); 				
});

jQuery(document).on("click", ".edit_provider", function(){		
	var id = jQuery(this).data('pid');
	var provider = jQuery(this).data('provider');
	var ajax_data = {
		action: 'get_provider_details',		
		provider_id: id,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		dataType: "json",
		success: function(response) {
			
			var provider_name = response.provider_name;
			var custom_provider_name = response.custom_provider_name;
			var provider_url = response.provider_url;
			var shipping_country = response.shipping_country;
			var custom_thumb_id = response.custom_thumb_id;									
			var image = response.image;
			var api_length = 0;
			
			if(provider == 'custom_provider'){				
				jQuery('.edit_provider_popup .shipping_provider').val(provider_name);
				jQuery('.edit_provider_popup .shipping_display_name').val(custom_provider_name);
				jQuery('.edit_provider_popup .api_provider_name').val(api_provider_name);
				jQuery('.edit_provider_popup .tracking_url').val(provider_url);
				jQuery('.edit_provider_popup .thumb_url').val(image);
				jQuery('.edit_provider_popup .thumb_id').val(custom_thumb_id);
				jQuery('.edit_provider_popup #provider_id').val(id);
				jQuery(".edit_provider_popup .shipping_country").val(shipping_country);
				jQuery('.edit_provider_popup #provider_type').val(provider);
				jQuery('.edit_provider_popup .tracking_url').show();
				jQuery(".edit_provider_popup .shipping_country").show();
				jQuery(".edit_provider_popup .shipping_provider").show();
				jQuery('.edit_provider_popup').show();	
				jQuery('.edit_provider_msg').hide();
				jQuery('.api_provider_name_container').hide();
				jQuery('.reset_default_provider').hide();
				jQuery('.custom_provider_instruction').show();				
			} else{				
				jQuery('.edit_provider_popup .shipping_provider').val(provider_name);
				jQuery('.edit_provider_popup .shipping_display_name').val(custom_provider_name);								
				jQuery('.api_provider_new').remove(); 
				
				if(response.api_provider_name == null){
					jQuery('.edit_provider_popup .api_provider_name').val(response.api_provider_name);
				} else if( IsValidJSONString(response.api_provider_name) ){
					var api_provider_name = jQuery.parseJSON( response.api_provider_name );
					var api_length = api_provider_name.length;
					
					if( api_length > 1){
						jQuery( api_provider_name ).each(function( index, value ){							
							if( index  == 0){
								jQuery('.edit_provider_popup .api_provider_name').val(value);
							} else{
								jQuery('.api_provider_name_container').append('<div class="api_provider_new"><input type="text" name="api_provider_name[]" class="api_provider_name" value="'+value+'" placeholder="API Name"><span class="dashicons dashicons-remove remove_more_api_provider"></span></div>');
							}						
						});		
					} else{
						jQuery('.edit_provider_popup .api_provider_name').val(api_provider_name);	
					}
				} else{
					jQuery('.edit_provider_popup .api_provider_name').val(response.api_provider_name);	
				}
				
				jQuery('.edit_provider_popup .thumb_url').val(image);
				jQuery('.edit_provider_popup .thumb_id').val(custom_thumb_id);
				jQuery('.edit_provider_popup #provider_id').val(id);
				jQuery('.edit_provider_popup #provider_type').val(provider);
				jQuery('.edit_provider_popup .tracking_url').hide();
				jQuery(".edit_provider_popup .shipping_country").hide();
				jQuery(".edit_provider_popup .shipping_provider").hide();
				jQuery('.edit_provider_popup').show();	
				jQuery('.edit_provider_msg').show();
				jQuery('.reset_default_provider').show();
				jQuery('.api_provider_name_container').show();
				jQuery('.custom_provider_instruction').hide();
			}						
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

function IsValidJSONString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

jQuery(document).on("click", ".reset_default_provider", function(){
	var form = jQuery('#edit_provider_form');
	
	jQuery(".edit_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	jQuery('#search_provider').removeAttr('value');
	var provider_id = jQuery(form).find('#provider_id').val();
	
	var ajax_data = {
		action: 'reset_default_provider',		
		provider_id: provider_id,		
	};
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();									
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=active]").addClass('active');				
			jQuery('.edit_provider_popup').hide();			
			jQuery(".edit_provider_popup").unblock();
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("submit", "#edit_provider_form", function(){
	
	var form = jQuery('#edit_provider_form');
	var error;
	var shipping_provider = jQuery("#edit_provider_form .shipping_provider");
	var shipping_country = jQuery("#edit_provider_form .shipping_country");
	var api_provider_name = jQuery(".api_provider_new .api_provider_name");
	var thumb_url = jQuery("#edit_provider_form .thumb_url");
	var tracking_url = jQuery("#edit_provider_form .tracking_url");	
	var provider_type = jQuery("#edit_provider_form #provider_type");	
	
	if(provider_type.val() == 'custom_provider'){
		if( shipping_provider.val() === '' ){				
			showerror(shipping_provider);
			error = true;
		} else{		
			hideerror(shipping_provider);
		}	
		
		if( shipping_country.val() === '' ){				
			showerror(shipping_country);
			error = true;
		} else{		
			hideerror(shipping_country);
		}		
	}	

	if(provider_type.val() == 'default_provider'){
		/*if( api_provider_name.val() === '' ){				
			showerror(api_provider_name);
			error = true;
		} else{		
			hideerror(api_provider_name);
		}*/
		
		for(var i=0; i<api_provider_name.length; i++) {					
			if(validate(api_provider_name[i]) == false){
				showerror(jQuery(api_provider_name[i]));
				error = true;
			} else{
				hideerror(jQuery(api_provider_name[i]));
			}			
		}
	}
	
	if(error == true){
		return false;
	}	
	jQuery(".edit_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	jQuery('#search_provider').removeAttr('value');
	
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();									
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=active]").addClass('active');				
			jQuery('.edit_provider_popup').hide();			
			jQuery(".edit_provider_popup").unblock();
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery( ".thumb_url" ).keyup(function() {
  var url = jQuery(this).val();
  if(url == ''){
	  jQuery('.thumb_id').val('');
  }
});

jQuery(document).on("click", ".reset_active", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( 'Do you really want to change all provider status to active?' );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
		
	jQuery('#search_provider').removeAttr('value');
	
	var error;		
	var ajax_data = {
		action: 'update_provider_status_active',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=active]").addClass('active');				
			jQuery("#content1").unblock();
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});	
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".reset_inactive", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( 'Do you really want to change all provider status to inactive?' );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
	
	jQuery('#search_provider').removeAttr('value');	
	
	var error;		
	var ajax_data = {
		action: 'update_provider_status_inactive',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=inactive]").addClass('active');				
			jQuery("#content1").unblock();
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}	
			});	
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".sync_providers", function(){		
	jQuery('.sync_provider_popup').show();
	jQuery("#reset_tracking_providers").prop("checked", false);	
});

jQuery(document).on("click", ".sync_providers_btn", function(){
	
	jQuery('.sync_providers_btn').attr("disabled", true);	
	jQuery('.sync_provider_popup .spinner').addClass('active');	
	jQuery('#reset_tracking_providers').val;
	
	var reset_checked = 0;
	if(jQuery('#reset_tracking_providers').prop("checked") == true){
		reset_checked = 1;
	}
	
	jQuery('.sync_message').hide();
	jQuery('#search_provider').removeAttr('value');
	
	var ajax_data = {
		action: 'sync_providers',
		reset_checked: reset_checked,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		dataType: "json",
		success: function(response) {			
			jQuery('.sync_provider_popup .spinner').removeClass('active');			
			jQuery(".provider_list").replaceWith(response.html);
			jQuery('.status_filter a').removeClass('active');
			jQuery("[data-status=active]").addClass('active');
			
			if(response.sync_error == 1 ){
				jQuery( ".sync_message" ).text( response.message );
				jQuery( ".sync_providers_btn" ).text( 'Retry' );				
			} else{
				if(reset_checked == 1){
					jQuery('.reset_db_message').show();
				} else{
					jQuery(".providers_added span").text(response.added);
					if(response.added > 0 ){
						jQuery( ".providers_added" ).append( response.added_html );
					}
					
					jQuery(".providers_updated span").text(response.updated);
					if(response.updated > 0 ){
						jQuery( ".providers_updated" ).append( response.updated_html );
					}
					
					jQuery(".providers_deleted span").text(response.deleted);
					if(response.deleted > 0 ){
						jQuery( ".providers_deleted" ).append( response.deleted_html );
					}	
					jQuery(".synch_result").show();
				}								
			}
			
			jQuery(".reset_db_fieldset").hide();
			jQuery(".sync_providers_btn").attr("disabled", false);
			jQuery(".sync_providers_btn").hide();
			jQuery(".close_synch_popup").show();
							
			jQuery('#shipping-provider-table').datatable({
				pageSize: 50,	
				pagingDivClass: 'text-left',
				firstPage:'',
				prevPage:'',
				nextPage:'',
				lastPage:'',
				sort: [false, false, false, false, false, false],    
				onChange: function(old_page, new_page){      
					jQuery(".woocommerce-help-tip").tipTip();
				},
				counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
					return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
				}				
			});
			jQuery(".woocommerce-help-tip").tipTip();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", "#view_added_details", function(){	
	jQuery('#added_providers').show();
	jQuery(this).hide();
	jQuery('#hide_added_details').show();
});
jQuery(document).on("click", "#hide_added_details", function(){	
	jQuery('#added_providers').hide();
	jQuery(this).hide();
	jQuery('#view_added_details').show();
});

jQuery(document).on("click", "#view_updated_details", function(){	
	jQuery('#updated_providers').show();
	jQuery(this).hide();
	jQuery('#hide_updated_details').show();
});
jQuery(document).on("click", "#hide_updated_details", function(){	
	jQuery('#updated_providers').hide();
	jQuery(this).hide();
	jQuery('#view_updated_details').show();
});

jQuery(document).on("click", "#view_deleted_details", function(){	
	jQuery('#deleted_providers').show();
	jQuery(this).hide();
	jQuery('#hide_deleted_details').show();
});
jQuery(document).on("click", "#hide_deleted_details", function(){	
	jQuery('#deleted_providers').hide();
	jQuery(this).hide();
	jQuery('#view_deleted_details').show();
});

jQuery(document).on("change", "#wcast_enable_delivered_email", function(){	
	if(jQuery(this).prop("checked") == true){
		 jQuery('.delivered_shipment_label').addClass('delivered_enabel');
	     jQuery('.delivered_shipment_label .email_heading').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').addClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').prop('disabled', true);			 
    } else{
		 jQuery('.delivered_shipment_label').removeClass('delivered_enabel');
		 jQuery('.delivered_shipment_label .email_heading').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').removeClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').removeAttr('disabled');
	}	
});
jQuery(document).on("change", "#wc_ast_status_delivered", function(){	
	if(jQuery(this).prop("checked") == false){		
		jQuery('#wcast_enable_delivered_email')[0].checked = false;		
	}
	if(jQuery(this).prop("checked") == true && jQuery("#wcast_enable_delivered_email").prop("checked") == true){
		 jQuery('.delivered_shipment_label').addClass('delivered_enabel');
	     jQuery('.delivered_shipment_label .email_heading').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').addClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').addClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').prop('disabled', true);			 
    } else{
		 jQuery('.delivered_shipment_label').removeClass('delivered_enabel');
		 jQuery('.delivered_shipment_label .email_heading').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .edit_customizer_a').removeClass('disabled_link');
		 jQuery('.delivered_shipment_label .delivered_message').removeClass('disable_delivered');
		 jQuery('#wcast_enable_delivered_status_email').removeAttr('disabled');
	}	
});

jQuery(document).click(function(){
	var $trigger = jQuery(".dropdown");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".dropdown-content").hide();
    }   
});

jQuery(document).on("click", ".dropdown_menu", function(){	
	jQuery('.dropdown-content').show();
});

function validate (input) {
	if(jQuery(input).val().trim() == '' || jQuery(input).val().trim() == 0){
        return false;
    }
}

function showerror(element){
	element.css("border","1px solid red");
}
function hideerror(element){
	element.css("border","1px solid #ddd");
}
jQuery(document).on("change", "#wc_ast_status_shipped", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery("[for=show_in_completed] .multiple_label").text('Shipped');
		jQuery("label .shipped_label").text('shipped');
	} else{
		jQuery("[for=show_in_completed] .multiple_label").text('Completed');
		jQuery("label .shipped_label").text('completed');
	}
});

jQuery(document).on("click", ".tab_input", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	jQuery('.zorem-layout__header-breadcrumbs .header-breadcrumbs-last').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=woocommerce-advanced-shipment-tracking&tab="+tab;
	window.history.pushState({path:url},'',url);	
});
jQuery(document).on("click", ".inner_tab_input", function(){
	var tab = jQuery(this).data('tab');
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=woocommerce-advanced-shipment-tracking&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).click(function(){
	var $trigger = jQuery(".ast_dropdown");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".ast-dropdown-content").hide();
    }   
});

jQuery(document).on("click", ".ast-dropdown-menu", function(){	
	jQuery('.ast-dropdown-content').show();
});

jQuery(document).on("click", ".ast-dropdown-content li a", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	var section = jQuery(this).data('section');
	jQuery('.inner_tab_section').hide();
	jQuery('.ast_nav_div').find("[data-tab='" + tab + "']").prop('checked', true); 
	jQuery('#'+section).show();
	jQuery('.zorem-layout__header-breadcrumbs .header-breadcrumbs-last').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=woocommerce-advanced-shipment-tracking&tab="+tab;
	window.history.pushState({path:url},'',url);
	jQuery(".ast-dropdown-content").hide();
});

jQuery(document).on("click", ".open_video_popup", function(){
	jQuery('.how_to_video_popup').show();	 
});

jQuery(document).on("click", ".ts_addons_header", function(){
	jQuery('.ts_video_popup').show();	 
});
jQuery(document).on("click", ".import_tracking_sidebar", function(){
	jQuery('.import_tracking_video_popup').show();	 
});

jQuery(document).on("click", ".how_to_video_popup .popupclose", function(){
	jQuery('#how_to_video').each(function(index) {
		jQuery(this).attr('src', jQuery(this).attr('src'));
		return false;
    });
});
jQuery(document).on("click", ".ts_video_popup .popupclose", function(){
	jQuery('#ts_video').each(function(index) {
		jQuery(this).attr('src', jQuery(this).attr('src'));
		return false;
    });
});
jQuery(document).on("click", ".import_tracking_video_popup .popupclose", function(){
	jQuery('#import_tracking_video').each(function(index) {
		jQuery(this).attr('src', jQuery(this).attr('src'));
		return false;
    });
});

jQuery('#shipping-provider-table').datatable({
    pageSize: 50,	
	pagingDivClass: 'text-left',
	firstPage:'',
	prevPage:'',
	nextPage:'',
	lastPage:'',
	dom: "Bfriptip",
    sort: [false, false, false, false, false, false],    
    onChange: function(old_page, new_page){      
		jQuery(".woocommerce-help-tip").tipTip();
    },
	counterText: function (currentPage, totalPage, firstRow, lastRow, totalRow) {		
		return 'Showing ' + firstRow +  ' to ' + lastRow + ' of ' + totalRow + ' entries' ;
	}	
});

jQuery(document).on("click", ".tool_link", function(){
	jQuery('#tab_tools').trigger( "click" );
});

jQuery(document).on("change", ".order_status_toggle", function(){	
	save_custom_order_status();
});

jQuery(document).on("change", ".enable_order_status_email_input", function(){
	save_custom_order_status();
});

jQuery(document).on("change", ".custom_order_color_select", function(){
	save_custom_order_status();
});

jQuery('#wc_ast_status_label_color').wpColorPicker({
	change: function(e, ui) {		
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-delivered').css('background',color);			
	}, 	
});

jQuery('body').click( function(){	
	if ( jQuery('.delivered_row button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
		save_custom_order_status(); 
	}
});

jQuery('.delivered_row button.button.wp-color-result').click( function(){	
	if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{save_custom_order_status();}
});
		
jQuery('#wc_ast_status_partial_shipped_label_color').wpColorPicker({
	change: function(e, ui) {
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-partially-shipped').css('background',color);
	},
});

jQuery('body').click( function(){	
	if ( jQuery('.partial_shipped_row button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
		save_custom_order_status(); 
	}
});

jQuery('.partial_shipped_row button.button.wp-color-result').click( function(){	
	if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{save_custom_order_status();}
});

jQuery('#wc_ast_status_updated_tracking_label_color').wpColorPicker({
	change: function(e, ui) {
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-updated-tracking').css('background',color);
	},
});

jQuery('body').click( function(){	
	if ( jQuery('.updated_tracking_row button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
		save_custom_order_status(); 
	}
});

jQuery('.updated_tracking_row button.button.wp-color-result').click( function(){	
	if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{save_custom_order_status();}
});

function save_custom_order_status(){
	jQuery(".custom_order_status_section").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var form = jQuery('#wc_ast_order_status_form');
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {
			jQuery(".custom_order_status_section").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
}