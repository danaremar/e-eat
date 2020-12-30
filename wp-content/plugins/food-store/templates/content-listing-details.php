<?php
/**
 * FoodStore Product
 *
 * This template can be overridden by copying it to yourtheme/food-store/content-listing-details.php.
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $product;
global $shortcode_args;

?>

<div class="wfs-food-item-container" data-term-id="<?php echo $term_id ? $term_id : ''; ?>">

  <?php do_action( 'wfs_before_product_summary' ); ?>

  <div class="wfs-food-item-summery">
    
    <?php do_action( 'wfs_product_summary' ); ?>
    
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

  <?php do_action( 'wfs_after_product_summary' ); ?>

</div>