function receiveMessage(event)
{
    //console.log('received event', event);
    var re = /restaurantlogin.com$/;

    //Do we trust the sender of this message?
    if (!re.test(event.origin))
        return;


    if(typeof event.data != 'undefined' && event.data != null && typeof event.data == 'string' && event.data.indexOf('iFrameSizer') == -1)
    {
        try
        {
            msgresponse=event.data;
            jQuery(document).find('#glf-button-custom-css-location').val(jQuery( "#js_glf_mor_ruid" ).val());
            jQuery(document).find('#glf-button-custom-css').val(msgresponse);
            jQuery(document).find('#glf-customize-button').submit();
            tb_remove();
        }
        catch(error){}
    }

}

window.addEventListener("message", receiveMessage, false);


function glfMorShowCustomCssInput(checkBoxfield , inputField) {
    if (jQuery(document).find('#' + checkBoxfield).is(':checked')) {
        jQuery(document).find('#' + inputField).show();
    } else {
        jQuery(document).find('#' + inputField).hide();
    }
}

