<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/food-store/single-product/title.php.
 *
 * @package    FoodStore/Templates
 * @version    1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

$food_type  	= get_post_meta( get_the_id(), '_wfs_food_item_type', true );
$image_option	= get_option('_wfs_listing_item_image_display');

the_title( '<a href="javascript:void(0);" class="wfs-food-item-title">', '</a>' );
if( $image_option === 'hide' ) :
	if( !empty( $food_type ) ) : ?>
		<div class="wfs-food-item-type <?php echo $food_type; ?>">
	        <div></div>
	    </div>
	<?php endif;
endif;