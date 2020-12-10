<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

The easiest way to customize the buttons is to click on the <strong>Customize</strong> links in the table above. This popup will appear:
<img src="<?= plugins_url('/images/help/publishing/q4-1.png', dirname(__FILE__).'/../../../../')?>">
Please note that the style changes of the <i>See Menu & Order / Table reservations</i> buttons apply to all the buttons that were added using the shortcodes provided above.<br><br>

<div><strong>Advanced tips:</strong></div>
<ul>
    <li>If you want your button to use the same style of your other buttons in the theme, you can pass a class name in the shortcode below:
        <div class="code"><?= Glf_Mor_Utils::glf_mor_get_shortcode($this->restaurants[0]->uid, 'ordering', true,"your_class_name");?></div>
        Like in this example:
        <div class="code"><?= Glf_Mor_Utils::glf_mor_get_shortcode($this->restaurants[0]->uid, 'ordering', true, "glf-btn-basic");?></div>
    </li>
    <li>If you want to strip the button of its default style and just let it inherit the parent’s style, leave the class attribute empty, like this:
        <div class="code"><?= Glf_Mor_Utils::glf_mor_get_shortcode($this->restaurants[0]->uid, 'ordering', true, "");?></div>
    </li>
</ul>
