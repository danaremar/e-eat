<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!$this->is_authenticated()) {
    require(dirname(__FILE__) . '/admin.php');
    die();
}

if (isset($_POST['css'])) {
    if (! (current_user_can('manage_options') && isset($_POST[ '_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'glf-mor-customize-css' )))
        die( 'Access restricted, security check failed!' );

    $custom_css = json_decode(stripslashes(sanitize_text_field($_POST['css'])), true);
    $custom_css_location = stripslashes(sanitize_text_field($_POST['location']));

    $temp_custom_css = Glf_Mor_Utils::glf_get_locations_custom_css( $custom_css_location );
    $temp_custom_css[$custom_css['type']] = $custom_css;
    Glf_Mor_Utils::glf_set_locations_custom_css( $temp_custom_css, $custom_css_location );
    /*$this->custom_css[$custom_css['type']] = $custom_css;
    $this->save_user_data(array('custom_css' => $this->custom_css));*/
}
if ( ! empty( $_POST['refresh_menu'] ) ) {
	if ( $this->glf_mor_restaurant_menu( $_POST['refresh_menu'], true ) ) {
		?>
        <script type="text/javascript">
            alert('Menu refreshed');
        </script>
		<?php
	}
}
?>
<div class="wrap">
<h1><?php _e('Publish on your website', 'menu-ordering-reservations');?></h1>
    <div class="clear"><br></div>
    <div class="glf-d-flex">
    <label for="glf_mor_restaurant">
        <?php
        $selected_option = '';
        if(isset( $custom_css_location )){
            $selected_option = $custom_css_location;
        }
        if (sizeof($this->restaurants) != 1) {
        ?>

            <select name="ruid" id="js_glf_mor_ruid" onchange="glfDisplayShortcode()">
                <?php
                    foreach ($this->restaurants as $restaurant) {
                        $add_selected = '';
                        if( !empty( $selected_option ) && $selected_option === $restaurant->uid ){
                            $add_selected = 'selected';
                        }
                ?>

                    <option value="<?php echo $restaurant->uid; ?>" <?= $add_selected; ?>><?php echo $restaurant->name; ?></option>
                <?php } ?>
            </select>
        <?php } else {?>
            <?= $this->restaurants[0]->name;?>
            <input type="hidden" name="ruid" id="js_glf_mor_ruid" value="<?= $this->restaurants[0]->uid;?>">
        <?php }?>
    </label>
    </div>
    <div class="glf-white-box publish">
        <table class="form-table">
            <tbody>
            <tr class="glf-border-bottom">
                <td colspan="2" class="glf-slim-cell"><strong><?php _e('Button Preview', 'menu-ordering-reservations');?></strong></td>
                <td class="glf-slim-cell" ><strong><?php _e('Shortcode', 'menu-ordering-reservations');?></strong></td>
            </tr>
            <tr class="glf-gray-bg">
                <td class="glf-cell glf-ordering-location" data-location="<?= $this->restaurants[0]->uid ?>">
                    <?= $this->add_ordering_shortcode(array('rid' => $this->restaurants[0]->uid)) ?>
                </td>
                <td nowrap="true" class="glf-cell">
                    <a  class="glf-customize" href="#" onclick="glf_mor_showThickBox('restaurant_system_customize_button', 'type=ordering')"> <img class="glf-customize-img" src="<?= plugins_url('../images/configure.png', __FILE__)?>"><strong><?php _e('Customize', 'menu-ordering-reservations');?></strong></a>
                </td>
                <td nowrap="true" class="glf-cell">
                        <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_ordering"  size="78">
                        <button class="copy-ordering-button glf-copy"  value="Copy" data-clipboard-action="copy" data-clipboard-target="#js_glf_mor_ordering"><?php _e('Copy', 'menu-ordering-reservations');?></button>
                </td>
            </tr>
            <tr class="glf-gray-bg">
                <td class="glf-cell glf-reservations-location" data-location="<?= $this->restaurants[0]->uid ?>">
                    <?= $this->add_reservations_shortcode(array('rid' => $this->restaurants[0]->uid)) ?>
                </td>
                <td nowrap="true" class="glf-cell">
                    <a class="glf-customize" href="#" onclick="glf_mor_showThickBox('restaurant_system_customize_button', 'type=reservations')"> <img class="glf-customize-img" src="<?= plugins_url('../images/configure.png', __FILE__)?>"><strong><?php _e('Customize', 'menu-ordering-reservations');?></strong></a>
                </td>
                <td nowrap="true" class="glf-cell">
                    <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_reservations"  size="78">
                    <button class="copy-reservations-button glf-copy"  value="Copy" data-clipboard-action="copy" data-clipboard-target="#js_glf_mor_reservations"><?php _e('Copy', 'menu-ordering-reservations');?></button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="glf-white-box publish">
        <table class="form-table">
            <tbody>
            <tr class="glf-border-bottom">
                <td class="glf-slim-cell">
                    <strong><?php _e( 'Menu Shortcode', 'menu-ordering-reservations' ); ?></strong></td>
            </tr>
            <tr class="glf-gray-bg">
                <td nowrap="true" class="glf-cell" style="text-align: center;">
                    <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_full_menu" style="width:54%">
                    <button class="copy-full-menu-button glf-copy" value="Copy" data-clipboard-action="copy"
                            data-clipboard-target="#js_glf_mor_full_menu"><?php _e( 'Copy', 'menu-ordering-reservations' ); ?></button>
                    <div style="display: flex; float: right; width: calc(46% - 6.25rem); box-sizing: border-box; white-space: normal; align-items: center;">
                        <button class="button button-primary" onClick="glfUpdateFullMenu(this)"
                                data-page="<?php menu_page_url( 'glf-publishing', true ); ?>" style="margin-right: 18px; margin-left: 20px;">Refresh menu
                        </button><span style="font-style: italic; line-height: 1.3; text-align: left;">Hit Refresh menu to publish your menu edits on the website.</span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<form method="post" id="glf-customize-button">
    <?php wp_nonce_field('glf-mor-customize-css') ?>
    <input name="location" id="glf-button-custom-css-location" type="hidden">
    <input name="css" id="glf-button-custom-css" type="hidden">
</form>


    <script>
        var clipboard1 = new Clipboard('.copy-ordering-button');
        var clipboard2 = new Clipboard('.copy-reservations-button');
        var clipboard3 = new Clipboard('.copy-full-menu-button');

        clipboard1.on('success', function(e) {
            alert('Code copied!')
        });

        clipboard1.on('error', function(e) {
            alert('Error! Please manually copy the code.')
        });

        clipboard2.on('success', function(e) {
            alert('Code copied!')
        });

        clipboard2.on('error', function(e) {
            alert('Error! Please manually copy the code.')
        });

        clipboard3.on('success', function(e) {
            alert('Code copied!')
        });

        clipboard3.on('error', function(e) {
            alert('Error! Please manually copy the code.')
        });


        jQuery(document).find('.glf-button').css('pointer-events', 'none');

        document.addEventListener("DOMContentLoaded", function (event) {
            if (document.readyState === 'interactive') {
                glfDisplayShortcode();
            }
        });
    </script>

