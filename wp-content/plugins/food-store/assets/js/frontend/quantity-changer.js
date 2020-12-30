jQuery(function($) {
  
  var wfsQty;

  function wfsQuantityChanger( method ) {

    var currentVal = parseInt( $('input[name=wfs-quantity]').val() );

    if ( method == 'add' ) {
      if ( !isNaN( currentVal ) ) {
        wfsQty = currentVal + 1;
      } else {
        wfsQty = 1;
      }
    }

    if( method == 'remove' ) {

      if ( !isNaN( currentVal ) && currentVal > 1 ) {
        wfsQty = currentVal - 1;
      } else {
        wfsQty = 1;
      }
    }

    $('input[name=wfs-quantity]').val( wfsQty );
    $('#wfsModal .wfsmodal-footer').find('.wfs-product-add-to-cart').attr( 'data-item-qty', wfsQty );

    /* Add Trigger After Quantity Changed */
    $( document ).trigger( 'wfs_modal_quantity_updated' );
  }

  $( document ).on('click', '.wfs-qtyplus', function(e) {
    wfsQuantityChanger( 'add' );
  });

  $( document ).on('click', '.wfs-qtyminus', function(e) {
    wfsQuantityChanger( 'remove' );
  });

});