jQuery(document).ready(function(){

  // Add Color Picker to all inputs that have 'color-field' class
  jQuery('.color-picker').wpColorPicker();


  if ( true === jQuery("#sbr_is_expirable").is(':checked') ) {
    jQuery("#sbr-expiry-wrap").show();
  }
  else {
    jQuery("#sbr-expiry-wrap").hide();
  }

  jQuery('body').on('click', '#sbr_is_expirable', function() {
    if( jQuery(this).is(':checked')) {
      jQuery("#sbr-expiry-wrap").show();
    } else {
      jQuery("#sbr-expiry-wrap").hide();
    }
  });

});
