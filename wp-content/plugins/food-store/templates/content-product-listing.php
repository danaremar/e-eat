<?php
/**
 * The template for displaying product listings
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
$get_all_categories = get_terms( $category_args );

if ( is_array( $get_all_categories ) ) {

  foreach( $get_all_categories as $wfs_category ) {
    
    $term_id = $wfs_category->term_id;

    $args = array(
      'post_type'       => 'product',
      'post_status'     => 'publish',
      'posts_per_page'  => -1,
    );

    $args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
      'field'    => 'term_id',
      'terms'    => array( $term_id ) ,
    );

    $query = apply_filters( 'wfs_get_products_args', $args );
    
    $wfs_products = new WP_Query( $query );

    if ( $wfs_products->have_posts() ) :

      wfs_listing_start( $echo = true, $term_id );

      // Get template column options 
      $columns = get_option( '_wfs_listing_column_count', '1' );
      switch ($columns) {
        case '2':
          $template_classes = 'fs-col-lg-6 fs-col-md-6 fs-col-sm-12 fs-col-xs-12 fs-2-columns';
          break;

        default:
          $template_classes = 'fs-col-lg-12 fs-col-md-12 fs-col-sm-12 fs-col-xs-12';
          break;
      }

      echo '<div class="fs-row">';

      $element = 1;

      while ( $wfs_products->have_posts() ) : $wfs_products->the_post();

        $product = wc_get_product( get_the_ID() );

        echo '<div class="'.apply_filters( 'wfs_template_columns', $template_classes, $columns ).'">';

        wfs_get_template(
          'content-listing-details.php',
          array(
            'product' => $product,
            'term_id' => $term_id,
          )
        );

        echo '</div>';

        if( $element % $columns  == 0 ) {
          echo '</div>';
          echo '<div class="fs-row">';
        }

        $element++;

      endwhile;

      echo '</div>';

      wfs_listing_end( $echo = true, $term_id );

      wp_reset_postdata();

    endif;
  }
}