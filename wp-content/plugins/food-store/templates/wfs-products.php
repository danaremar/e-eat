<?php
/**
 * The template for displaying products
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Setting up product listing container
wfs_product_start();

// Showing the Store CLosed notice if any
wfs_store_message();

// Leaving filter to add more information from extension
apply_filters( 'wfs_before_fooditems_area', '' );

if ( isset( $shortcode_args['show_search'] ) && $shortcode_args['show_search'] == 'yes' ) {
  wfs_get_template( 'content-search.php' );
}

wfs_get_template( 'content-product-listing.php' ,
  array(
    'shortcode_args' => $shortcode_args,
    'category_ids'   => $category_ids
  )
);

wfs_product_end();