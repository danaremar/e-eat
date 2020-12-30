<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

    <head>

        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta name="viewport" content="width=device-width" />
		<style type="text/css" id="wclp_designer_custom_css">.woocommerce-store-notice.demo_store, .mfp-hide {display: none;}</style>
    </head>

    <body class="wclp_preview_body">
		<div id="overlay"></div>
        <div id="wclp_preview_wrapper" style="display: block;">

            <?php wclp_pickup_customizer_email::preview_pickup_email(); ?>

        </div>

		<?php
		do_action( 'woomail_footer' );
		wp_footer(); ?>

    </body>

</html>