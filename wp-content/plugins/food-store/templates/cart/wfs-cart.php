<?php
/**
 * The template for displaying prduct cart
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $woocommerce;

$items = $woocommerce->cart->get_cart();

$cart_count = count( $items );
$cart_class = $cart_count ? 'content' : 'empty';

// Cart display option
$hide_cart = get_option( '_wfs_listing_hide_cart_area', 'no' );

?>

<?php if( 'no' == $hide_cart ) : ?>

  <!-- Fade body when the cart is expanded -->
  <dir class="wfs-body-fade"></dir>
  
  <!-- Complete Cart View -->
  <div class="wfs-cart-expanded <?php echo $cart_class; ?>">
    <div class="fs-container">

      <div class="wfs-cart-expanded-header">
        <p class="wfs-cart-expanded-header-title"><?php esc_html_e('Order Details', 'food-store'); ?></p>
        <span class="wfs-close-cart-icon"><i class="wfs-icon-close"></i></span>
      </div>

      <?php if ( $cart_count ) : ?>
        
        <div class="wfs-cart-content-area">
          
          <div class="cart-content-left">
            <?php wfs_get_template( 'cart/cart-contents.php' ); //cart with items ?>
          </div>
          
          <div class="cart-content-right">
            <?php wfs_get_template( 'cart/cart-totals.php' ); ?>
          </div>

        </div>

      <?php else: ?>

        <?php wfs_get_template( 'cart/empty-cart.php' ); //empty cart ?>

      <?php endif; ?>

    </div>

  </div>

  <!-- Cart Overview Area -->
  <div class="wfs-cart-overview">
    <div class="fs-container">
      <div class="wfs-cart-overview-row">
        
        <div class="wfs-cart-toggle">
          <a href="javascript:void(0);" class="wfs-expand-cart"><i class="wfs-icon-chevron-with-circle-up"></i></a>
          <a href="javascript:void(0);" class="wfs-compress-cart fs-hidden"><i class="wfs-icon-chevron-with-circle-down"></i></a>
        </div>

        <div class="wfs-cart-overview-description">
          <p><?php 
            if ( !empty( $cart_count ) ) :
              /* translators: %s: cart items count */
              echo sprintf( __( 'Your Orders (%s)', 'food-store' ), $cart_count ); 
            else :
              echo sprintf( __( 'Your Order', 'food-store' ), $cart_count );
            endif;
            ?></p>
        </div>

        <?php if ( wfs_is_service_enabled() ) : ?>
          <?php echo wfs_service_time(); ?>
        <?php endif; ?>
        
        <div class="fs-text-right wfs-cart-purchase-actions">
          <span class="wfs-cart-subtotal"><?php echo __( 'Total:&nbsp;', 'food-store' ); ?><?php wc_cart_totals_order_total_html(); ?></span>
          <button class="fs-hidden fs-btn-md fs-btn-secondary wfs-clear-cart">
            <?php echo wfs_empty_cart(); ?>
          </button>
          <?php if( apply_filters( 'wfs_allow_proceed_to_checkout', true ) ) : ?>
            <button class="fs-btn-md fs-btn-primary wfs-proceed-to-checkout">
              <?php echo __( 'Continue' , 'food-store' ); ?>
            </button>
          <?php endif; ?>
          <?php apply_filters( 'wfs_after_proceed_to_checkout', '' ); ?>
        </div>

        <div class="fs-text-right wfs-cart-purchase-actions-mobile">
          
          <span class="wfs-cart-subtotal"><?php echo __( 'Total:&nbsp;', 'food-store' ); ?><?php wc_cart_totals_order_total_html(); ?></span>

          <?php if( wfs_is_service_enabled() && ! wfs_check_store_closed() ) { ?>
            <a href="javascript:void(0);" class="wfs-change-service-mobile"><i class="wfs-icon-access_time"></i></a>
          <?php } ?>

          <a href="javascript:void(0);" class="wfs-clear-cart"><i class="wfs-icon-trash-o"></i></a>

          <?php if( apply_filters( 'wfs_allow_proceed_to_checkout', true ) ) : ?>
            <a href="javascript:void(0);" class="wfs-proceed-to-checkout" onclick=""><i class="wfs-icon-shopping-cart"></i></a>
          <?php endif; ?>

          <?php apply_filters( 'wfs_after_proceed_to_checkout_mobile', '' ); ?>
          
        </div>

      </div>
    </div>
  </div>

<?php endif; ?>