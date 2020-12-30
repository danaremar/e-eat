<?php
/**
 * The template for displaying product category
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$exclude_categories = wfs_get_exclude_categories();

$category_args = array(
  'taxonomy'    => 'product_cat',
  'hide_empty'  => true,
  'include'     => $category_ids,
  'exclude'     => $exclude_categories,
);

$category_args = apply_filters( 'wfs_categories',  $category_args );
$get_categories = get_terms( $category_args );

if ( $get_categories ) {
  
  wfs_category_start();

  foreach ( $get_categories as $category ) {

    wfs_get_template(
      'content-wfs-category.php',
      array(
        'category' => $category,
      )
    );
    
  }
  wfs_category_end();
}