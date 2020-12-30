<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta name="viewport" content="width=device-width" />
		<style type="text/css" id="kadence_woomail_designer_custom_css">.woocommerce-store-notice.demo_store, .mfp-hide {display: none;}</style>
    </head>

    <body>
        <div id="kt_woomail_preview_wrapper" style="display: block;">
            <?php wcast_initialise_customizer_email::preview_delivered_email(); ?>
        </div>

		<?php
		do_action( 'woomail_footer' );
		wp_footer(); ?>
    </body>
</html>