function glfUpdateButtonLabel(){
    var ruid = document.getElementById('js_glf_mor_ruid').value;
    jQuery('.glf-ordering-location').attr('data-location', ruid);
    jQuery('.glf-reservations-location').attr('data-location', ruid);
}

function glf_mor_insertShortcode() {
    var ruid = document.getElementById('js_glf_mor_ruid').value;
    var type = jQuery(document.getElementById('js_glf_mor_insert_code_main_container')).find('.js_glf_mor_btn_type:checked').val();
    var code = glf_mor_createShortcode(type, ruid);
    window.send_to_editor(code);
    tb_remove();
}

function glf_mor_createShortcode(type, ruid) {
    var code = '[';

    switch (type) {
        case 'ordering':
            code+='restaurant-menu-and-ordering';
            break;
        case 'reservations':
            code+='restaurant-reservations';
            break;
        case 'full-menu':
            code+='restaurant-full-menu';
            break;
    }

    code += ' ruid="' + ruid + '"]';
    return code;
}

function glf_mor_showThickBox(action, extraParameters) {
    extraParameters += '&location=' + jQuery( "#js_glf_mor_ruid" ).val();
    tb_show('Button code', 'admin-ajax.php?action=' + action + (extraParameters ? '&' + extraParameters : ''));
}

function glf_mor_removeThickBox() {
    tb_remove();
}

function glf_mor_resizeThickbox(height) {
    setTimeout(function () {
        var TB_WIDTH = 600;
        var TB_HEIGHT = height || 600;
        var TB_window = jQuery(document).find('#TB_window');
        var windowHeight = jQuery(window).height();

        if (parseInt(TB_window.width()) > 600 && TB_window.height() > 600) {
            TB_window.width(TB_WIDTH).height(TB_HEIGHT);
            var marginL = (parseInt(TB_window.width())) / 2;
            TB_window.css('margin-left', -marginL);

            if (windowHeight > TB_window.height()) {
                var marginT = (windowHeight - TB_window.height()) / 2;
                TB_window.css('margin-top', marginT).css('top', 0);
            }
        }

        if (parseInt(TB_window.height()) < 768) {
            var TB_HEIGHT = 432;

            if (windowHeight > TB_window.height()) {
                var marginT = (windowHeight - TB_window.height()) / 2;
                TB_window.css('margin-top', marginT).css('top', 0);
            }
        }
        }, 0);
}
