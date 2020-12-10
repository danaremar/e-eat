<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
To accomplish this you need a plugin that allows you to add shortcodes to the navigation. Here’s how to do it using the <a href="<?= admin_url();?>plugin-install.php?s=shortcode+in+menus&tab=search&type=term" target="_blank">Shortcode in Menus</a> plugin.
<ul>
    <li>Install and activate the <a href="<?= admin_url();?>plugin-install.php?s=shortcode+in+menus&tab=search&type=term" target="_blank">Shortcode in Menus plugin</a></li>
    <li>Go to the <i>Menus</i> page under <i>Appearance</i></li>
    <li>Click on <i>Screen Options</i> and check the <i>Shortcodes</i> box<br>
        <img  src="<?= plugins_url('/images/help/publishing/q2-1.png', dirname(__FILE__).'/../../../../')?>">
    </li>
    <li>Having that done, the Shortcode section becomes available</li>
    <li>
        Copy paste the shortcode for the button you want to add in the navigation and add it to the menu<br>
        <img  src="<?= plugins_url('/images/help/publishing/q2-2.png', dirname(__FILE__).'/../../../../')?>"></li>
    <li>Depending on your theme, your navigation will look something like this:<br>
        <img  src="<?= plugins_url('/images/help/publishing/q2-3.png', dirname(__FILE__).'/../../../../')?>">
    </li>
    <li>
        <strong>Since the ordering button generates revenue, we highly recommend that you make it stand out in the navigation.</strong> So if possible, leave it as it is, a big orange button (orange is a very good color for conversion).<br><br>

        If you cannot make it work in this default design, you can use this shortcode instead:
        <div class="code"><?= Glf_Mor_Utils::glf_mor_get_shortcode($this->restaurants[0]->uid , 'ordering', true, "glf-btn-basic");?></div>
        which will produce this result:<br>
        <img  src="<?= plugins_url('/images/help/publishing/q2-4.png', dirname(__FILE__).'/../../../../')?>"><br>
        Please note that we’ve added a new attribute called class in the shortcode. If none of the above options work for your website (design wise) then you can:
        <ol>
            <li style="list-style-type: lower-alpha">
                Leave the class attribute empty to inherit the parent’s class:
                <div class="code"><?= Glf_Mor_Utils::glf_mor_get_shortcode($this->restaurants[0]->uid, 'ordering', true);?></div>
                <img  src="<?= plugins_url('/images/help/publishing/q2-5.png', dirname(__FILE__).'/../../../../')?>">
            </li>
            <li style="list-style-type: lower-alpha">
                Or just use a class of yours, like this:
                <div class="code"><?= Glf_Mor_Utils::glf_mor_get_shortcode($this->restaurants[0]->uid,'ordering', true, "your_class_name");?></div>
            </li>
        </ol>
    </li>
</ul>