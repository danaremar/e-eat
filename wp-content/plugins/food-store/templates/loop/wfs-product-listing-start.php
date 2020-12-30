<?php
/**
 * FoodStore Product Listing Start
 *
 * This template can be overridden by copying it to yourtheme/food-store/wfs-product-listing-start.php.
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$category_name = '';

if ( !empty( $category_id ) ) {
	$term_details 	= get_term_by( 'id', $category_id, 'product_cat' );
	$category_name 	= $term_details->name;
	$category_title = $term_details->slug;
	$category_desc 	= $term_details->description;
}

?>

<!--  Food Category Menu -->
<div id="<?php echo $category_title; ?>_start" class="wfs-category-title-container not-in-search in-search"  data-category-title="<?php echo $category_title; ?>" data-term-id="<?php echo $category_id; ?>" >
	
	<?php apply_filters( 'wfs_category_menu_start_title_before', $category_title ); ?>

	<h3 id="<?php echo $category_title; ?>" class="wfs-category-title"><?php echo $category_name; ?></h3>
	<span class="wfs-category-short-desciption"><?php echo $category_desc; ?></span>

	<?php apply_filters( 'wfs_category_menu_start_title_after', $category_title ); ?>

</div>