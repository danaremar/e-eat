<?php
/**
 * Single Product Add To Cart
 *
 * This template can be overridden by copying it to yourtheme/food-store/single-product/price.php.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $product;
global $shortcode_args;

?>

<div class="fs-text-right wfs-food-item-cart-action">
	
	<?php if( $shortcode_args['catalog_mode'] == 'yes' ) : ?>

	<?php elseif( ! $product->is_in_stock() ) : ?>

		<span class="wfs-text-out-of-stock"><?php echo apply_filters( 'wfs_empty_stock_button_text', __( 'Out of Stock', 'food-store' ) ); ?></span>
	
	<?php else : ?>

		<button class="fs-btn fs-btn-primary button-add-to-cart wfs-product-modal" data-product-id="<?php echo $product->get_id(); ?>" data-loading-text="<?php echo _e( 'Wait...', 'food-store' ); ?>">
    		<span class="wfs_btn_txt"><?php echo wfs_add_to_cart_text(); ?></span>
    		<i class="wfs-icon-plus"></i>
  		</button>

	<?php endif; ?>
</div>