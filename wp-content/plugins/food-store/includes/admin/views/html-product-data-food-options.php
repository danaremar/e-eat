<?php
/**
 * Admin View: Food Store Product Tab
 *
 * @package FoodStore
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

echo '<div id="food_product_options" class="panel woocommerce_options_panel hidden">';

woocommerce_wp_text_input( array(
	'id' 			=> '_wfs_variation_price_label',
	'value'			=> get_post_meta( get_the_ID(), '_wfs_variation_price_label', true ),
	'label'			=> __( 'Variation Pricing Label', 'food-store' ),
	'wrapper_class' => 'show_if_variable',
	'desc_tip'		=> true,
	'description'	=> __( 'Customized text to display on price selection label.', 'food-store' )
) );

woocommerce_wp_select( array(
	'id'			=> '_wfs_food_item_type',
	'value'			=> get_post_meta( get_the_ID(), '_wfs_food_item_type', true ),
	'label'			=> __( 'Food Type', 'food-store' ),
	'options'		=> array( 
		'' 			=> __( 'Please select', 'food-store' ), 
		'veg' 		=> __( 'Vegetarian', 'food-store' ), 
		'nonveg'	=> __( 'Non Vegiterian', 'food-store' )
	)
) );

$enable_instructions = get_option( '_wfs_enable_special_note', 'yes' );
if( 'yes' == $enable_instructions ) {

	woocommerce_wp_checkbox( array(
		'id'			=> '_wfs_disable_instruction',
		'value'			=> get_post_meta( get_the_ID(), '_wfs_disable_instruction', true ),
		'label'			=> __( 'Disable Instructions', 'food-store' ),
		'description'   => __( 'Hide special instructions textarea for this item.', 'woocommerce' ),
	) );
}

echo '</div>';