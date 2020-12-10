
function glfDisplayShortcode() {
    var ruid = document.getElementById('js_glf_mor_ruid').value;
    jQuery('.glf-ordering-location').attr('data-location', ruid);
    jQuery('.glf-reservations-location').attr('data-location', ruid);
    document.getElementById('js_glf_mor_ordering').value = glf_mor_createShortcode('ordering', ruid);
    document.getElementById('js_glf_mor_reservations').value = glf_mor_createShortcode('reservations', ruid);
    document.getElementById('js_glf_mor_full_menu').value = glf_mor_createShortcode('full-menu', ruid);
}

function glfUpdateFullMenu(element) {
    var ruid = document.getElementById('js_glf_mor_ruid').value;
    //window.location.href = jQuery(element).data('page') + '&refresh_menu=' + ruid;

    // Fix for alert appearing everytime you customize a button.
    // If the page is changed the alert doesn't appear anymore.
    // Delete the refresh_menu parameter from the URL.
    let form = jQuery('<form action="' + jQuery(element).data('page') + '" method="post">' +
        '<input type="text" name="refresh_menu" value="' + ruid + '" />' +
        '</form>');
    jQuery('body').append(form);
    form.submit();
}
