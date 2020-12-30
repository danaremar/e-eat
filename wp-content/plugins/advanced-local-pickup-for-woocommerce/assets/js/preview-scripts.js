( function( $ ) {
	$('.hide').hide();
    /* Hide/Show Header */
	
	wp.customize( 'woocommerce_customer_pickup_order_settings[heading]', function( value ) {		
		value.bind( function( wclp_pickup_email_heading ) {
					
			var str = wclp_pickup_email_heading;
			var res = str.replace("{site_title}", wclp_preview.site_title);
			
			var res = res.replace("{order_number}", wclp_preview.order_number);
				
			if( wclp_pickup_email_heading ){				
				$( '#header_wrapper h1' ).text(res);
			} else{
				$( '#header_wrapper h1' ).text('');
			}			
		});
	});
	
	wp.customize( 'woocommerce_customer_pickup_order_settings[additional_content]', function( value ) {		
		value.bind( function( additional_content ) {					
			var str = additional_content;
			var res = str.replace("{site_title}", wclp_preview.site_title);			
			var res = res.replace("{order_number}", wclp_preview.order_number);				
			if( additional_content ){				
				$( '.wclp_additional_content' ).text(res);
			} else{
				$( '.wclp_additional_content' ).text('');
			}			
		});
	});		
	
	wp.customize( 'woocommerce_customer_ready_pickup_order_settings[heading]', function( value ) {		
		value.bind( function( wclp_ready_pickup_email_heading ) {
					
			var str = wclp_ready_pickup_email_heading;
			var res = str.replace("{site_title}", wclp_preview.site_title);
			
			var res = res.replace("{order_number}", wclp_preview.order_number);
				
			if( wclp_ready_pickup_email_heading ){				
				$( '#header_wrapper h1' ).text(res);
			} else{
				$( '#header_wrapper h1' ).text('');
			}			
		});
	});

	wp.customize( 'woocommerce_customer_ready_pickup_order_settings[additional_content]', function( value ) {		
		value.bind( function( additional_content ) {					
			var str = additional_content;
			var res = str.replace("{site_title}", wclp_preview.site_title);			
			var res = res.replace("{order_number}", wclp_preview.order_number);				
			if( additional_content ){				
				$( '.wclp_additional_content' ).text(res);
			} else{
				$( '.wclp_additional_content' ).text('');
			}			
		});
	});	
	
	wp.customize( 'wclp_failure_email_heading', function( value ) {		
		value.bind( function( wclp_failure_email_heading ) {
					
			var str = wclp_failure_email_heading;
			var res = str.replace("{site_title}", wclp_preview.site_title);
			
			var res = res.replace("{order_number}", wclp_preview.order_number);
				
			if( wclp_failure_email_heading ){				
				$( '#header_wrapper h1' ).text(res);
			} else{
				$( '#header_wrapper h1' ).text('');
			}			
		});
	});
	wp.customize( 'pickup_instruction_display_settings[location_box_border_color]', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( location_box_border_color ) {		
			/* Update callback for setting change */
			$( '.wclp_location_box_heading' ).css( 'border-color',location_box_border_color );					
			$( '.wclp_location_box' ).css( 'border-color',location_box_border_color );					
		} );		
	} );
	wp.customize( 'pickup_instruction_display_settings[location_box_background_color]', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( location_box_background_color ) {		
			/* Update callback for setting change */							
			$( '.wclp_location_box' ).css( 'background-color',location_box_background_color );					
		} );		
	} );
	wp.customize( 'pickup_instruction_display_settings[location_box_padding]', function( setting ) {
		/* Deferred callback for when setting exists */
		setting.bind( function( location_box_padding ) {			
			/* Update callback for setting change */
			$( '.wclp_location_box_heading' ).css( 'padding',location_box_padding+'px' );
			$( '.wclp_location_box_content' ).css( 'padding',location_box_padding+'px' );			
		} );		
	} );
	wp.customize( 'pickup_instruction_display_settings[location_box_heading]', function( value ) {		
		value.bind( function( location_box_heading ) {
			if( location_box_heading ){
				$( '.local_pickup_email_title' ).text(location_box_heading);
			} else{
				$( '.local_pickup_email_title' ).text('');
			}	 		
		});
	});
} )( jQuery );