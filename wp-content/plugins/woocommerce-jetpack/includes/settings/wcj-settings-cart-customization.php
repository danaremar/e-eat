<?php
/**
 * Booster for WooCommerce - Settings - Cart Customization
 *
 * @version 3.1.0
 * @since   2.8.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

return array(
	array(
		'title'    => __( 'Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_cart_customization_options',
	),
	array(
		'title'    => __( 'Hide Coupon on Cart Page', 'woocommerce-jetpack' ),
		'desc'     => __( 'Hide', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_hide_coupon',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Hide Item Remove Link', 'woocommerce-jetpack' ),
		'desc'     => __( 'Hide', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_hide_item_remove_link',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Change Empty Cart "Return to shop" Button Text', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_customization_return_to_shop_button_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc'     => __( 'Method', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_customization_return_to_shop_button_text_method',
		'default'  => 'js',
		'type'     => 'select',
		'options'  => array(
			'js'       => __( 'Use JavaScript', 'woocommerce-jetpack' ),
			'template' => __( 'Replace empty cart template', 'woocommerce-jetpack' ),
		),
	),
	array(
		'desc'     => __( 'Text', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_customization_return_to_shop_button_text',
		'default'  => __( 'Return to shop', 'woocommerce' ),
		'type'     => 'text',
	),
	array(
		'title'    => __( 'Change Empty Cart "Return to shop" Button Link', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_customization_return_to_shop_button_link_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc'     => __( 'Link', 'woocommerce-jetpack' ),
		'id'       => 'wcj_cart_customization_return_to_shop_button_link',
		'default'  => '',
		'type'     => 'text',
		'css'      => 'width:300px;',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_cart_customization_options',
	),
);
