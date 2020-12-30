<?php
/**
 * FoodStore Product Listing End
 *
 * This template can be overridden by copying it to yourtheme/food-store/wfs-product-listing-end.php.
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$category_name = '';

if ( !empty( $category_id ) ) {
  $term_details = get_term_by( 'id', $category_id, 'product_cat' );
  $category_name = $term_details->name;
  $category_title = $term_details->slug;
}

?>

<div id="<?php echo $category_title; ?>_end"></div>
<?php apply_filters( 'wfs_category_menu_end', $category_title ); ?>
<!--  Food Category Menu End -->