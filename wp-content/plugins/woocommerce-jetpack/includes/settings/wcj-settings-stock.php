<?php
/**
 * Booster for WooCommerce - Settings - Stock
 *
 * @version 3.6.0
 * @since   2.8.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

return array(
	array(
		'title'    => __( 'Custom "In Stock" Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_stock_custom_in_stock_options',
	),
	array(
		'title'    => __( 'Custom "In Stock"', 'woocommerce-jetpack' ),
		'desc'     => '<strong>' . __( 'Enable section', 'woocommerce-jetpack' ) . '</strong>',
		'id'       => 'wcj_stock_custom_in_stock_section_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Custom "In Stock" Text', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_in_stock_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc'     => __( '"In Stock" text.', 'woocommerce-jetpack' ) . ' ' .
			sprintf( __( 'If needed, use %s to insert stock quantity.', 'woocommerce-jetpack' ), '<code>%s</code>' ),
		'desc_tip' => __( 'You can also use shortcodes here.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_in_stock',
		'default'  => '',
		'type'     => 'custom_textarea',
		'css'      => 'width:100%;height:100px;',
	),
	array(
		'desc'     => __( '"Low amount" text.', 'woocommerce-jetpack' ) . ' ' . __( 'Ignored if empty.', 'woocommerce-jetpack' ) . ' ' .
			sprintf( __( 'If needed, use %s to insert stock quantity.', 'woocommerce-jetpack' ), '<code>%s</code>' ),
		'desc_tip' => __( 'You can also use shortcodes here.', 'woocommerce-jetpack' ) . ' ' .
			sprintf( __( 'Used only if %s is selected for %s in %s.', 'woocommerce-jetpack' ),
				'<em>' . __( 'Only show quantity remaining in stock when low', 'woocommerce-jetpack' ) . '</em>',
				'<em>' . __( 'Stock display format', 'woocommerce' ) . '</em>',
				'<em>' . __( 'WooCommerce > Settings > Products > Inventory', 'woocommerce-jetpack' ) ) . '</em>',
		'id'       => 'wcj_stock_custom_in_stock_low_amount',
		'default'  => '',
		'type'     => 'custom_textarea',
		'css'      => 'width:100%;height:100px;',
	),
	array(
		'desc'     => __( '"Can be backordered" text.', 'woocommerce-jetpack' ) . ' ' . __( 'Ignored if empty.', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'You can also use shortcodes here.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_in_stock_can_be_backordered',
		'default'  => '',
		'type'     => 'custom_textarea',
		'css'      => 'width:100%;height:100px;',
	),
	array(
		'title'    => __( 'Custom "In Stock" Class', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_in_stock_class_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc'     => sprintf( __( 'Default: %s.', 'woocommerce-jetpack' ), '<code>in-stock</code>' ),
		'css'      => 'width:100%;',
		'id'       => 'wcj_stock_custom_in_stock_class',
		'default'  => '',
		'type'     => 'text',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_stock_custom_in_stock_options',
	),
	array(
		'title'    => __( 'Custom "Out of Stock" Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_stock_custom_out_of_stock_options',
	),
	array(
		'title'    => __( 'Custom "Out of Stock"', 'woocommerce-jetpack' ),
		'desc'     => '<strong>' . __( 'Enable section', 'woocommerce-jetpack' ) . '</strong>',
		'id'       => 'wcj_stock_custom_out_of_stock_section_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Custom "Out of Stock" Text', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_out_of_stock_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc_tip' => __( 'You can also use shortcodes here.', 'woocommerce-jetpack' ),
		'desc'     => sprintf( __( 'Default: %s.', 'woocommerce-jetpack' ), '<code>' . __( 'Out of stock', 'woocommerce' ) . '</code>' ),
		'id'       => 'wcj_stock_custom_out_of_stock',
		'default'  => '',
		'type'     => 'custom_textarea',
		'css'      => 'width:100%;height:100px;',
	),
	array(
		'title'    => __( 'Custom "Out of Stock" Class', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_out_of_stock_class_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc'     => sprintf( __( 'Default: %s.', 'woocommerce-jetpack' ), '<code>out-of-stock</code>' ),
		'css'      => 'width:100%;',
		'id'       => 'wcj_stock_custom_out_of_stock_class',
		'default'  => '',
		'type'     => 'text',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_stock_custom_out_of_stock_options',
	),
	array(
		'title'    => __( 'Custom "Available on backorder" Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_stock_custom_backorder_options',
	),
	array(
		'title'    => __( 'Custom "Available on backorder"', 'woocommerce-jetpack' ),
		'desc'     => '<strong>' . __( 'Enable section', 'woocommerce-jetpack' ) . '</strong>',
		'id'       => 'wcj_stock_custom_backorder_section_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Custom "Available on backorder" Text', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_backorder_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc_tip' => __( 'You can also use shortcodes here.', 'woocommerce-jetpack' ),
		'desc'     => sprintf( __( 'Default: %s or empty string.', 'woocommerce-jetpack' ), '<code>' . __( 'Available on backorder', 'woocommerce' ) . '</code>' ),
		'id'       => 'wcj_stock_custom_backorder',
		'default'  => '',
		'type'     => 'custom_textarea',
		'css'      => 'width:100%;height:100px;',
	),
	array(
		'title'    => __( 'Custom "Available on backorder" Class', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_custom_backorder_class_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc'     => sprintf( __( 'Default: %s.', 'woocommerce-jetpack' ), '<code>available-on-backorder</code>' ),
		'css'      => 'width:100%;',
		'id'       => 'wcj_stock_custom_backorder_class',
		'default'  => '',
		'type'     => 'text',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_stock_custom_backorder_options',
	),
	array(
		'title'    => __( 'Custom Stock HTML', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_stock_custom_stock_html_options',
	),
	array(
		'title'    => __( 'Custom Stock HTML', 'woocommerce-jetpack' ),
		'desc'     => '<strong>' . __( 'Enable section', 'woocommerce-jetpack' ) . '</strong>',
		'id'       => 'wcj_stock_custom_stock_html_section_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
		'desc_tip' => apply_filters( 'booster_message', '', 'desc' ),
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'    => __( 'HTML', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'You can also use shortcodes here.', 'woocommerce-jetpack' ),
		'desc'     => wcj_message_replaced_values( array( '%class%', '%availability%' ) ) . '. ' . apply_filters( 'booster_message', '', 'desc' ),
		'id'       => 'wcj_stock_custom_stock_html',
		'default'  => '<p class="stock %class%">%availability%</p>',
		'type'     => 'textarea',
		'css'      => 'width:100%;height:100px;',
		'custom_attributes' => apply_filters( 'booster_message', '', 'readonly' ),
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_stock_custom_stock_html_options',
	),
	array(
		'title'    => __( 'More Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_stock_more_options',
	),
	array(
		'title'    => __( 'Remove Stock Display', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will remove stock display from frontend.', 'woocommerce-jetpack' ) . ' ' . apply_filters( 'booster_message', '', 'desc' ),
		'desc'     => __( 'Remove', 'woocommerce-jetpack' ),
		'id'       => 'wcj_stock_remove_frontend_display_enabled',
		'default'  => 'no',
		'type'     => 'checkbox',
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_stock_more_options',
	),
);
