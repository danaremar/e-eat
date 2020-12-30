<?php
/**
 * FoodStore Template Hooks
 *
 * Action/filter hooks used for FoodStore functions/templates.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

# Category Loop Items
add_action( 'wfs_subcategory_title', 'wfs_template_loop_category_title', 10 );

# Product Loop items
add_action( 'wfs_before_product_summary', 'wfs_template_show_images' , 10 );
add_action( 'wfs_product_summary', 'wfs_product_title', 5 );
add_action( 'wfs_product_summary', 'wfs_product_short_description', 10 );
add_action( 'wfs_product_summary', 'wfs_product_price', 15 );
add_action( 'wfs_after_product_summary', 'wfs_product_action_button', 20 );

# FoodStore Cart
add_action( 'wp_footer', 'wfs_footer_cart', 10 );

// add_action( 'wfs_variable_data', 'woocommerce_template_single_title', 5 );
// add_action( 'wfs_variable_data', 'woocommerce_template_single_rating', 10 );
// add_action( 'wfs_variable_data', 'woocommerce_template_single_price', 15 );
// add_action( 'wfs_variable_data', 'woocommerce_template_single_excerpt', 20 );
add_action( 'wfs_variable_data', 'woocommerce_template_single_add_to_cart', 25 );

# Disaplying available Addons to Choose
add_action( 'wfs_product_addon', 'wfs_render_product_addons', 10, 2 );