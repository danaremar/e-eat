<?php
/**
 * Booster for WooCommerce - Settings - Product Visibility by Condition
 *
 * @version 3.6.0
 * @since   3.6.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$settings = array(
	array(
		'title'    => __( 'Visibility Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_' . $this->id . '_options',
	),
	array(
		'title'    => __( 'Hide Visibility', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will hide selected products in shop and search results. However product still will be accessible via direct link.', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_visibility',
		'default'  => 'yes',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Make Non-Purchasable', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will make selected products non-purchasable (i.e. product can\'t be added to the cart).', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_purchasable',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Modify Query', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will hide selected products completely (including direct link).', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_query',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'desc_tip' => __( 'Enable this if you are still seeing hidden products in "Products" widgets.', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_query_widgets',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_' . $this->id . '_options',
	),
);
$settings = array_merge( $settings, $this->maybe_add_extra_settings() );
$settings = array_merge( $settings, array(
	array(
		'title'    => __( 'Admin Options', 'woocommerce-jetpack' ),
		'type'     => 'title',
		'id'       => 'wcj_' . $this->id . '_admin_options',
	),
	array(
		'title'    => __( 'Visibility Method', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This option sets how do you want to set product\'s visibility.', 'woocommerce-jetpack' ) . ' ' .
			__( 'Possible values: "Set visible", "Set invisible" or "Set both".', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_visibility_method',
		'default'  => 'visible',
		'type'     => 'select',
		'options'  => array(
			'visible'   => __( 'Set visible', 'woocommerce-jetpack' ),
			'invisible' => __( 'Set invisible', 'woocommerce-jetpack' ),
			'both'      => __( 'Set both', 'woocommerce-jetpack' ),
		),
		'desc'     => '<br>' . apply_filters( 'booster_message', '', 'desc' ),
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'    => __( 'Select Box Style', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_select_style',
		'default'  => 'chosen_select',
		'type'     => 'select',
		'options'  => array(
			'chosen_select' => __( 'Chosen select', 'woocommerce-jetpack' ),
			'standard'      => __( 'Standard', 'woocommerce-jetpack' ),
		),
	),
	array(
		'title'    => __( 'Quick Edit', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will add options to the "Quick Edit".', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_admin_quick_edit',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'title'    => __( 'Bulk Edit', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_admin_bulk_edit',
		'default'  => 'no',
		'type'     => 'checkbox',
		'desc_tip' => __( 'This will add options to the "Bulk Actions > Edit".', 'woocommerce-jetpack' ) . '<br>' .
			apply_filters( 'booster_message', '', 'desc' ),
		'custom_attributes' => apply_filters( 'booster_message', '', 'disabled' ),
	),
	array(
		'title'    => __( 'Products List Column', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will add column to the admin products list.', 'woocommerce-jetpack' ),
		'desc'     => __( 'Add', 'woocommerce-jetpack' ),
		'id'       => 'wcj_' . $this->id . '_admin_add_column',
		'default'  => 'no',
		'type'     => 'checkbox',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_' . $this->id . '_admin_options',
	),
) );
return $settings;
