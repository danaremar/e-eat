jQuery(function($) {

  // Timepicker for service hours
  $('input.wfs_service_time').timepicker({
    dropdown: true,
    scrollbar: true,
  });

  // Tip tip tooltip
  $( '.tips, .help_tip, .foodstore-help-tip' ).tipTip({
    'attribute': 'data-tip',
    'fadeIn': 50,
    'fadeOut': 50,
    'delay': 200
  });

  // ColorPicker
  $('.wfs-colorpicker').wpColorPicker();

});