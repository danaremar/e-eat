<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$this->update_restaurants();
?>

<div class="glf-media-button-container wrap js_glf_mor_insert_code_main_container" id="js_glf_mor_insert_code_main_container"">
    <label for="glf_mor_restaurant">
                <?php if (sizeof($this->restaurants) != 1) {?>
                <select name="ruid" id="js_glf_mor_ruid" onchange="glfUpdateButtonLabel()">
                    <?php foreach ($this->restaurants as $restaurant) {?>
                        <option value="<?php echo $restaurant->uid; ?>"><?php echo $restaurant->name; ?></option>
                    <?php } ?>
                </select>
                <?php } else {?>
                    <?= $this->restaurants[0]->name;?>
                    <input type="hidden" name="ruid" id="js_glf_mor_ruid" value="<?= $this->restaurants[0]->uid;?>">
                <?php }?>
            </label>
    <div class="glf-white-box">
        <table class="form-table">
            <tbody>
            <tr class="glf-border-bottom">
        <td>
            <div>
                <label>
                    <input type="radio" name="type"
                           class="js_glf_mor_btn_type"
                           value="ordering"
                           checked>
                    <span><?php _e('Restaurant menu and ordering', 'menu-ordering-reservations'); ?></span>
                </label>
            </div>
        </td>
                <td class="glf-ordering-location" data-location="<?= $this->restaurants[0]->uid ?>">
                    <?= $this->add_ordering_shortcode(array('rid' => $this->restaurants[0]->uid)) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <di>
                        <label>
                            <input type="radio" name="type"
                                   class="js_glf_mor_btn_type"
                                   value="reservations" >
                            <span><?php _e('Table reservations', 'menu-ordering-reservations'); ?></span>

                        </label>
                    </di>
                </td>
                <td class="glf-reservations-location" data-location="<?= $this->restaurants[0]->uid ?>">
                    <?= $this->add_reservations_shortcode(array('rid' => $this->restaurants[0]->uid)) ?>
                </td>
            </tr>
        </td>
    </tr>

    </tbody></table>
</div>
<br>
<div class="alignright">
    <button class="button" value="Cancel" onclick="glf_mor_removeThickBox();"><?php _e('Cancel', 'menu-ordering-reservations'); ?></button>
    <button class="button button-primary" style="margin-left: 10px" value="Insert code" onclick="glf_mor_insertShortcode();"><?php _e('Insert button code', 'menu-ordering-reservations'); ?></button>
</div>


<script>
    glf_mor_resizeThickbox(400);
    jQuery(document).find('.glf-button').css('pointer-events', 'none');
</script>
<?php exit();