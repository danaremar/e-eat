jQuery(function($) {

  // Make the Category Sidebar stick to left while scrolling
  if ( wfs_script.sticky_category_list == 'yes' ) {
    jQuery('#wfs-sticky-sidebar').theiaStickySidebar({
      additionalMarginTop: 40
    });
  }

  // Load js when service modal has been trigger
  $( document ).on( 'wfs_service_modal_trigger', function() {
    
    var action = 'render_service_options';
    var currentDate = $('.tab-pane.active').find('.wfs-service-dates').val();
    var data = {
        action      : action,
        currentDate : currentDate,
    };

    $.ajax({
      type      : "POST",
      data      : data,
      url       : wfs_script.ajaxurl,
      success : function( response ) {}
    });
  });

  // Enable showing popup from Image and Title based on settings
  if ( wfs_script.item_title_popup == 'yes' ) {
    $('a.wfs-food-item-title, .wfs-food-item-image-container img').on('click', function(event) {
      $(this).parents('.wfs-food-item-container').find('.wfs-product-modal').trigger('click');
    });
  }

  // Make the menu active when clicked
  $('a.wfs-loop-category__title').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    $(this).addClass('active');
    $(this).find('span.wfs-items-count').addClass('active');

    /* Remove active Class from siblings */
    $(this).parents('.wfs-category-menu-li').siblings().each(function() {
      var other_menu = $(this).find('.wfs-loop-category__title');
      other_menu.removeClass('active');
      other_menu.find('span.wfs-items-count').removeClass('active');
    });
  });

  // Scroll to specfic section when category menu is clicked
  $('.wfs-loop-category__title').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    var category = $(this).data('category-title');
    $('html, body').animate({
      scrollTop: $("#" + category + "_start").offset().top
    }, 350);
  });

  // Food Store Live Search
  $('#wfs-food-items').find('a.wfs-food-item-title').each(function(){
    $(this).attr('data-search-term', $(this).text().toLowerCase());
  });

  // Search items on Keyup
  $('.wfs-food-search').on('keyup', function(){
    
    var search_term = $(this).val().toLowerCase();
    var term_id;
    
    $('#wfs-food-items').find('.wfs-category-title-container').each(function(index, elem) {
      $(this).removeClass('not-in-search');
      $(this).removeClass('in-search');
    });

    $('#wfs-food-items').find('.wfs-food-item-summery a').each(function(){
      
      term_id = $(this).parents('.wfs-food-item-container').attr('data-term-id');

      if ($(this).filter('[data-search-term *= ' + search_term + ']').length > 0 || search_term.length < 1) {
        
        $(this).parents('.wfs-food-item-container').show();
        $('#wfs-food-items').find('.wfs-category-title-container').each(function(index, elem) {
          
          if( $(this).attr('data-term-id') == term_id ) {
            $(this).addClass('in-search');
          } else {
            $(this).addClass('not-in-search');
          }
        });
      
      } else {
        
        $(this).parents('.wfs-food-item-container').hide();
        $('#wfs-food-items').find('.wfs-category-title-container').each(function(index, elem) {
          $(this).addClass('not-in-search');
        });
      }
    });
  });

  function wfs_cart_fragments() {
    
    $('.wfs-expand-cart').on('click', function(event) {
      event.preventDefault();

      /* Enable Clear Cart Button */
      $('.wfs-cart-purchase-actions .wfs-clear-cart').removeClass('fs-hidden')

      /* Act on Cart Overview Area */
      $('.wfs-cart-overview').css('background-color', '#eae7e7');
      
      /* Act on Cart Expanded Area */
      $('.wfs-cart-expanded').addClass('active');
      $('.wfs-cart-expanded').css('bottom', $('.wfs-cart-overview').outerHeight() + 'px');

      /* Switch the Toggle Buttons */
      $('.wfs-compress-cart').removeClass('fs-hidden');
      $('.wfs-expand-cart').addClass('fs-hidden');

      /* Enable Fade Effect */
      $('.wfs-body-fade').addClass('active');
    });

    $('.wfs-compress-cart, .wfs-close-cart-icon').on('click', function(event) {
      event.preventDefault();

      /* Disable Clear Cart Button */
      $('.wfs-cart-purchase-actions .wfs-clear-cart').addClass('fs-hidden')

      /* Act on Cart Overview Area */
      $('.wfs-cart-overview').css('background-color', '#fff');
      
      /* Act on Cart Expanded Area */
      $('.wfs-cart-expanded').removeClass('active');

      /* Switch the Toggle Buttons */
      $('.wfs-compress-cart').addClass('fs-hidden');
      $('.wfs-expand-cart').removeClass('fs-hidden');

      /* Disable Fade Area */
      $('.wfs-body-fade').removeClass('active');
    });

    // Updating mini cart content 
    $(document.body).trigger('wc_fragment_refresh');
  }

  /* Cart Visibility Actions */
  wfs_cart_fragments();

  // Add To Cart Modal
  $('body').on('click', '.wfs-product-modal', function(e) {
    
    e.preventDefault();

    var button = $(this);
    var product_id = button.data('product-id');

    /* Open service modal based on settings */
    if ( wfs_script.service_option == 'yes' && ( wfs_script.service_modal_option == 'manual_modal' || wfs_script.service_modal_option == 'auto_modal' ) && ( wfs_script.service_type == '' || wfs_script.service_time == '' ) ) {
      
      $('.wfs-update-service').attr('data-add-item', product_id );
      $('.wfs-change-service').trigger('click');
      $('#wfsServiceModal li.nav-item').eq(0).find('a').trigger('click');
      return;
    }
    
    var current_text = button.html();
    var loading_text = button.data('loading-text');
    var product_id = button.data('product-id');

    if ( typeof product_id !== 'undefined' ) {

      /* Replace the button text with loading text */
      button.html(loading_text);

      var data = {
        action      : 'show_product_modal',
        product_id  : product_id,
        security    : wfs_script.product_modal_nonce,
      };

      $.ajax({
        type      : "POST",
        data      : data,
        url       : wfs_script.ajaxurl,
        success : function( response ) {
          if( response ) {
            
            $( '#wfsModal .wfsmodal-title' ).html( response.title );
            $( '#wfsModal .wfsmodal-body' ).html( response.content );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr( 'data-product-id', response.product_id );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr( 'data-product-type', response.product_type );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-quantity input').val( response.product_qty );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr('data-item-qty', response.product_qty );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr('data-cart-action', response.action );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-add-to-cart .wfs-cart-action-text' ).html( response.action_text );
            
            if( typeof response.is_essential !== 'undefined' ) {
              $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-add-to-cart .wfs-live-item-price' ).html( '(' + response.price + ')' );
              $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-add-to-cart .wfs-live-item-price' ).attr( 'data-price', response.raw_price );
            }

            if ( response.product_type =='variable' ) {
              $('#wfsModal .wfsmodal-footer').find('.wfs-product-add-to-cart').addClass('disabled').addClass('variation-selection-needed');
            }

            if ( response.product_type =='simple' ) {
              $('#wfsModal .wfsmodal-footer').find('.wfs-product-add-to-cart').removeClass('disabled').removeClass('variation-selection-needed');
            }

            if( $('.variations_form').length > 0  ) {
              $('.variations_form').each(function () {
                $(this).wc_variation_form();
              });
            }

            /* Open Modal */
            MicroModal.show('wfsModal');

            /* Trigger Modal Window Opened */
            $( document.body ).trigger( 'wfs_modal_opened' );

            /* Put the original text for add to cart button */
            button.html(current_text);
          }
        }
      });
    }
  });

  // Variations on change
  $('body').on('change', '#wfsModal .variations_form select', function() {
    var _self = $(this);
    variation_id = _self.parents('form').find('.variation_id').val();

    if ( variation_id !== '' ) {
      _self.parents('#wfsModal').find('.wfs-product-add-to-cart').removeClass('disabled').removeClass('variation-selection-needed');
      _self.parents('#wfsModal').find('.wfs-product-add-to-cart').attr('data-variation-id', variation_id);
    } else {
      _self.parents('#wfsModal').find('.wfs-product-add-to-cart').addClass('disabled').addClass('variation-selection-needed');
      _self.parents('#wfsModal').find('.wfs-product-add-to-cart').attr('data-variation-id', '');
    }
  });

  // Add to cart through ajax from modal
  $('body').on('click', '.wfs-product-add-to-cart', function(e) {
    
    e.preventDefault();
    
    if ( $(this).hasClass('variation-selection-needed') ) {
      return false;
    }
    
    var _self        = $(this);
    var action       = _self.attr('data-cart-action');
    var item_key     = _self.attr('data-item-key');
    var product_id   = _self.attr('data-product-id');
    var quantity     = _self.attr('data-item-qty');
    var variation_id = _self.attr('data-variation-id');
    
    var postdata     = '';
    var security     = '';
    var special_note = $('textarea#special_note').val();

    var addonData = $('.wfs-item-addons-container :input').serializeArray();

    if( 'add_to_cart' === action ) {
      security = wfs_script.add_to_cart_nonce
    } else {
      security = wfs_script.update_cart_nonce
    }

    if ( _self.parents('#wfsModal').find('.variations_form').length > 0 ) {
      postdata = _self.parents('#wfsModal').find('.variations_form').serializeArray();
    }

    if ( typeof product_id !== 'undefined' ) {

      _self.find('span.wfs-cart-action-text').text(wfs_script.cart_process_message);

      var data = {
        action       : action,
        item_key     : item_key,
        product_id   : product_id,
        quantity     : quantity,
        variation_id : variation_id,
        postdata     : postdata,
        security     : security,
        addon_data   : addonData,
        special_note : special_note,
      };

      $.ajax({
        type     : "POST",
        data     : data,
        url      : wfs_script.ajaxurl,
        success : function( response ) {

          $.toast({
            text     : response.status_message,
            position: {
              right: 10,
              bottom: 70
            },
          });

          $('.wfs-cart-wrapper').html(response.cart_content);
          MicroModal.close('wfsModal');
          wfs_cart_fragments();
          _self.find('span.wfs-cart-action-text').text(wfs_script.add_to_cart_text);
        }
      });
    }
  });

  // Cart edit button
  $('body').on( 'click', '.wfs-cart-item-edit', function(e) {
    
    e.preventDefault();

    /* Close the Cart */
    $('.wfs-close-cart-icon').trigger('click');
    
    var product_id = $(this).attr('data-product-id');
    var cart_key = $(this).attr('data-cart-key');

    if ( cart_key !== '' 
      && product_id !== '' 
      && typeof product_id !== 'undefined' ) {

      var data = {
        action      : 'show_product_modal',
        product_id  : product_id,
        cart_key    : cart_key,
        security    : wfs_script.product_modal_nonce,
      };

      $.ajax({
        type     : "POST",
        data     : data,
        url      : wfs_script.ajaxurl,
        success : function( response ) {
          if( response ) {
            
            if ( typeof response.variation_id !== 'undefined' ) {
              $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr('data-variation-id', response.variation_id );
            }

            $( '#wfsModal .wfsmodal-title' ).html( response.title );
            $( '#wfsModal .wfsmodal-body' ).html( response.content );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr( 'data-product-id', response.product_id );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr( 'data-product-type', response.product_type );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-quantity input').val( response.product_qty );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr('data-item-qty', response.product_qty );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr('data-cart-action', response.action );
            $( '#wfsModal .wfsmodal-footer' ).find('.wfs-product-add-to-cart').attr('data-item-key', response.item_key );
            $( '#wfsModal .wfsmodal-footer' ).find( '.wfs-modal-add-to-cart .wfs-cart-action-text' ).html( response.action_text );
            $( '#wfsModal .wfsmodal-body' ).find( '#special_note' ).html( response.special_note );

            if( typeof response.is_essential !== 'undefined' ) {
              $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-add-to-cart .wfs-live-item-price' ).html( '(' + response.price + ')' );
              $( '#wfsModal .wfsmodal-footer' ).find('.wfs-modal-add-to-cart .wfs-live-item-price' ).attr( 'data-price', response.raw_price );
            }

            $('.variations_form').each( function () {
              $(this).wc_variation_form();
            });

            /* Open Modal */
            MicroModal.show('wfsModal');

            /* Trigger Modal Window Opened */
            $( document ).trigger( 'wfs_modal_opened' );
          }
        }
      });
    }
  });

  // Empty Cart
  $('body').on( 'click', '.wfs-clear-cart', function(e) {
    
    e.preventDefault();

    var data = {
      action       : 'empty_cart',
      security     : wfs_script.empty_cart_nonce,
    };

    $.ajax({
      type     : "POST",
      data     : data,
      url      : wfs_script.ajaxurl,
      success : function( response ) {
          
        if( response.status == 'success' ) {

          // Manually Clear the Service Values
          wfs_script.service_type = '';
          wfs_script.service_time = '';

          $('.wfs-cart-wrapper').html(response.cart_content);
          
          wfs_cart_fragments();
          
          $.toast({
            text     : wfs_script.cart_empty_message,
            position: {
              right: 10,
              bottom: 70
            },
          });
        }
      }
    });
  });

  // Remove Item From Cart
  $('body').on('click', '.wfs-cart-item-delete', function(e) {
    e.preventDefault();

    var product_id = $(this).attr('data-product-id');
    var cart_key   = $(this).attr('data-cart-key');

    if ( product_id !== '' ) {
      var data = {
        product_id   : product_id,
        cart_key     : cart_key,
        action       : 'product_remove_cart',
        security     : wfs_script.remove_item_nonce,
      };

      $.ajax({
        type     : "POST",
        data     : data,
        url      : wfs_script.ajaxurl,
        success : function( response ) {
          
          if( response.status == 'success' ) {
            $('.wfs-cart-wrapper').html(response.cart_content);
            wfs_cart_fragments();
            
            $.toast({
              text     : response.message,
              position: {
                right: 10,
                bottom: 70
              },
            });
          }
        }
      });
    }
  });

  // Show store close message when store is closed
  $('body').on( 'click', '.wfs-store-closed', function() {
    $.toast({
      icon : 'warning',
      text: wfs_script.store_closed_message,
      position: {
        right: 10,
        bottom: 70
      },
    });
  });

  // Proceed to Checkout
  $('body').on('click', '.wfs-proceed-to-checkout', function(event) {
    
    event.preventDefault();

    var data = {
      action  : 'validate_proceed_checkout',
    };

    $.ajax({
      type      : "POST",
      data      : data,
      url       : wfs_script.ajaxurl,
      success : function( response ) {
        
        if( response.status == 'error' ) {
          
          $.toast({
            text     : response.message,
            position : { right: 10, bottom: 70 },
          });
          
          return false;
        
        } else {
          
          /* Set URL based on Admin Settings */
          if( wfs_script.purchase_redirect == 'cart') {
            $(location).attr( 'href', wfs_script.cart_url );
          } else if( wfs_script.purchase_redirect == 'checkout') {
            $(location).attr( 'href', wfs_script.checkout_url );
          }
        }
      }
    });
  });

  // Variations Radio Buttons
  $(document).on('click touch mouseover', '.wfs-variations', function() {
    $(this).attr('data-click', 1);
  });

  $('body').on('click', '.wfs-variation-radio', function() {
    
    var _this = $(this);
    var _variations = _this.closest('.wfs-variations');
    var _click = parseInt(_variations.attr('data-click'));
    var _variations_form = _this.closest('.variations_form');

    wfs_variations_select(_this, _variations, _variations_form, _click);
    _this.find('input[type="radio"]').prop('checked', true);

    /* Trigger Once Variation is Selected */
    $( document.body ).trigger( 'wfs_variation_selected' );
  });

  $(document).on( 'click', '#wfsServiceModal .wfs-update-service', function(e) {
    
    e.preventDefault();
    
    var _this = jQuery(this);
    var selected_method   = _this.parents('.wfs-service-modal-container').find('.tab-pane.active');
    var selected_service  = selected_method.data('service-type');
    var selected_time     = selected_method.find('select.wfs-service-hours').val();

    _this.text(wfs_script.please_wait_text);
    _this.parents('.wfs-service-modal-container').find('.foodstore_service_error').addClass('inactive');

    var data = {
      action            : 'update_service_time',
      selected_service  : selected_service,
      selected_time     : selected_time
    };

    $.ajax({
      type      : "POST",
      data      : data,
      url       : wfs_script.ajaxurl,
      success : function( response ) {
        
        if ( response.status == 'error' ) {

          _this.text(wfs_script.update_service_text);
          _this.parents('.wfs-service-modal-container').find('.foodstore_service_error').html(response.message).removeClass('inactive');

        } else {
          
          $('.wfs-cart-service-settings').find('.wfs-service-type').text(response.service_type);
          $('.wfs-cart-service-settings').find('.wfs-service-time').text(response.service_time);

          wfs_script.service_type = response.service_type;
          wfs_script.service_time = response.service_time;

          $('.wfs-cart-service-settings').removeClass('fs-hidden');
          MicroModal.close('wfsServiceModal');

          /* Open service modal based on settings */
          if ( wfs_script.service_modal_option == 'manual_modal' || wfs_script.service_modal_option == 'auto_modal' ) {

            var add_item_id = _this.attr('data-add-item');
            if( add_item_id !== '' ) {
              setTimeout(function() {
                $('.wfs-food-item-container').find('[data-product-id='+add_item_id+']').trigger('click');
                _this.attr( 'data-add-item', '' );
              }, 1000);
            }
          }

          _this.text(wfs_script.update_service_text);
        }

      }
    });

  });

  // Open service modal on Manual Click
  jQuery(document).on( 'click', '.wfs-change-service', function(e) {

    MicroModal.show('wfsServiceModal');
    $(document).trigger('wfs_service_modal_trigger');
  });

  // Trigger main service modal when clicked on mobile service icon
  jQuery(document).on( 'click', '.wfs-change-service-mobile', function() {
    $( '.wfs-change-service' ).trigger('click');
  });

  // Open service modal once page is loaded based on admin settings
  if ( wfs_script.service_option == 'yes' && wfs_script.service_modal_option == 'auto_modal' && ( wfs_script.service_type == '' || wfs_script.service_time == '' ) ) {
    if( $('#wfsServiceModal').length ) {
      $( '.wfs-change-service' ).trigger('click');
    }
  }
});

jQuery(document).on('found_variation', function(e,t) {
  
  var variation_id = t['variation_id'];
  var $variations_default = jQuery(e['target']).find('.wfs-variations-default');

  if ($variations_default.length) {
    if (parseInt($variations_default.attr('data-click')) < 1) {
      $variations_default.find(
          '.wfs-variation-radio[data-id="' + variation_id + '"] input[type="radio"]').prop('checked', true);
    }
  }
});

function wfs_variations_select(selected, variations, variations_form, click) {
  
  if ( click > 0 ) {
   
    variations_form.find('.reset_variations').trigger('click');

    if (selected.attr('data-attrs') !== '') {
      var attrs = jQuery.parseJSON(selected.attr('data-attrs'));

      if (attrs !== null) {
        for (var key in attrs) {
          variations_form.find('select[name="' + key + '"]').val(attrs[key]).trigger('change');
        }
      }
    }
  }
  jQuery(document).trigger('wfs_selected', [selected, variations, variations_form]);
}