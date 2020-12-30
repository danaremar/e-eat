<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

$display_image = get_option( '_wfs_popup_enable_image', 'yes' );

// Allow filter to create image gallery for Essentials
echo apply_filters( 'wfs_popup_area_top', '', $product );

?>

<div class="modal-content-wrapper fs-row">

  <?php 

  // Allow filter to create image gallery for Essentials
  echo apply_filters( 'wfs_popup_image_area_left', '', $product );

  if( 'yes' === $display_image && apply_filters( 'wfs_popup_image_container', true ) ) : ?>
    
    <div class="product-thumbnail-wrapper">
      <div class="product-image-container">
        
        <?php 
        
        $thumbnail_id   = $product->get_image_id();
        $thumbnail_size = 150;
        $thumbnail_src  = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
        $thumbnail_src  = isset( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';

        if ( $thumbnail_src ) : ?>
          <img width="150" height="150" alt="<?php echo $product->get_name(); ?>" src="<?php echo $thumbnail_src; ?>">
        <?php endif; ?>
      </div>
    </div>

  <?php endif; ?>

  <div class="product-content">
    <?php 
    
    echo apply_filters( 'wfs_short_description', $product->get_short_description() );
    
    if( 'variable' == $product->get_type() ) {
      do_action( 'wfs_variable_data' ); 
    }
    
    do_action( 'wfs_product_addon', $product, $cart_key ); 

    ?>
  </div>
</div>