<?php
/**
 * Customer completed order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-completed-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$wcast_partial_shipped_customizer_settings = new wcast_partial_shipped_customizer_email();

$ast = new WC_Advanced_Shipment_Tracking_Actions;
$email_content = $ast->get_option_value_from_array('woocommerce_customer_partial_shipped_order_settings','wcast_partial_shipped_email_content',$wcast_partial_shipped_customizer_settings->defaults['wcast_partial_shipped_email_content']);	

$email_content = str_replace( '{customer_email}', $order->get_billing_email(), $email_content );
$email_content = str_replace( '{site_title}', $wcast_partial_shipped_customizer_settings->get_blogname(), $email_content );	
$email_content = str_replace( '{customer_first_name}', $order->get_billing_first_name(), $email_content );	
$email_content = str_replace( '{customer_last_name}', $order->get_billing_last_name(), $email_content );

if($order->get_billing_company()){
	$email_content = str_replace( '{customer_company_name}', $order->get_billing_company(), $email_content );	
} else{
	$email_content = str_replace( '{customer_company_name}','', $email_content );	
}
		
$user = $order->get_user();
if($user){
	$username = $user->user_login;
}
if(isset($username)){
	$email_content = str_replace( '{customer_username}', $username, $email_content );
} else{
	$email_content = str_replace( '{customer_username}', '', $email_content );
}
$email_content = str_replace( '{order_number}', $order->get_id(), $email_content );	
/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Site title */ ?>
<p class="partial_email_content"><?php echo $email_content; ?></p>
<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additonal content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
