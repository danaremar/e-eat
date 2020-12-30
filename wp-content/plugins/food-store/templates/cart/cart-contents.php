<?php
/**
 * FoodStore Cart Items
 *
 * This template can be overridden by copying it to yourtheme/food-store/cart/cart-contents.php
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
} 
?>

<div class="wfs-cart-item-container">

  <?php 

  foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : 

    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

    $quantity     = isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1;
    $food_type    = get_post_meta( $product_id, '_wfs_food_item_type', true );
    $get_addons   = wfs_get_formatted_addons( $cart_item ); 
    $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : '';
    $special_note = isset( $cart_item['special_note'] ) ? $cart_item['special_note'] : '';
    
    ?>

    <div class="fs-row wfs-cart-item">
      
      <div class="wfs-cart-item-left">
        <div class="wfs-cart-item-title">
          <?php if( ! empty( $food_type ) ) : ?>
            <div class="wfs-food-item-type <?php echo $food_type; ?>">
              <div></div>
            </div>
          <?php endif; ?>

          <p><?php echo $_product->get_name(); ?></p>
        </div>

        <?php 

        if ( 'variable' == $_product->get_type() ) : 
          
          $variation_name = ''; 
          $variations = isset( $cart_item['variation'] ) ? $cart_item['variation'] : array();

          if ( is_array( $variations ) && !empty( $variations ) ) : ?>
            <?php $variation_name = implode(' / ', $variations); ?>
            <div class="wfs-cart-addon variations">
              <p class="wfs-cart-variation-item">- <?php echo $variation_name; ?></p>
            </div>
          <?php endif; ?>

        <?php endif; ?>

        <?php if ( !empty( $get_addons ) && apply_filters( 'wfs_show_cart_addon_items', true ) ) : ?>
          <div class="wfs-cart-addon">
            <?php echo $get_addons; ?>
          </div>
        <?php endif; ?>

        <?php if ( !empty( $special_note ) ) : ?>
          <div class="wfs-special-instruction">- <?php 
          /* translators: %s: special note */
          echo sprintf( __( 'Special Note : %s', 'food-store' ), $special_note ); ?></div>
        <?php endif; ?>
      </div>

      <div class="fs-text-right">
        
        <span class="wfs-cart-item-price">
          <?php echo $quantity . '&nbsp;&times;&nbsp;'; ?>
          <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok. ?>
        </span>

        <span class="wfs-cart-actions wfs-cart-item-edit" data-cart-key="<?php echo $cart_item_key; ?>" data-variation-id="<?php echo $variation_id; ?>" data-product-id="<?php echo $product_id; ?>">
        <i class="wfs-icon-pencil"></i>
        </span>

        <span class="wfs-cart-actions wfs-cart-item-delete" data-product-id="<?php echo $product_id; ?>" data-cart-key="<?php echo $cart_item_key; ?>">
        <i class="wfs-icon-trash-o"></i>
        </span>
      </div>
    </div>

  <?php endforeach; ?>

</div>