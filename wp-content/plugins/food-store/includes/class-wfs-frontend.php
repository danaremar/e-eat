<?php
/**
 * FoodStore frontend related functions and actions.
 *
 * @package FoodStore/Classes
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

class WFS_Frontend {

  /**
   * Frontend Class Constructor
   *
   * @author WP Scripts
   * @since 1.0.0
   */
	public function __construct() {

    add_action( 'wp_head', array( $this, 'inline_style_sheet' ) );
    add_action( 'woocommerce_before_variations_form', array( $this, 'item_variations') );
    add_filter( 'woocommerce_get_item_data', array( $this, 'wfs_get_item_data' ), 10, 2 );
    add_action( 'woocommerce_check_cart_items', array( $this, 'validate_cart_items') );
    add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'wfs_save_cart_item_meta' ), 10, 4 );
    add_action( 'woocommerce_order_item_meta_start', array( $this, 'wfs_order_item_meta' ), 10, 3 );
    add_action( 'woocommerce_before_calculate_totals', array( $this, 'wfs_adding_custom_price' ), 99, 1);
    add_action( 'wp_footer', array( $this, 'variation_script' ), 10 );
  }

  /**
   * Enqueues the custom styles from Admin settings
   *
   * @since 1.0.2
   * Author FoodStore
   */
  public function inline_style_sheet() {

    $pri_color_scheme = get_option( '_wfs_primary_color_scheme', '#337ab7' );
    $pri_color_scheme = ( ! empty( $pri_color_scheme ) ) ? $pri_color_scheme : '#337ab7';
    $sec_color_scheme = get_option( '_wfs_secondary_color_scheme', '#337ab7' );
    $sec_color_scheme = ( ! empty( $sec_color_scheme ) ) ? $sec_color_scheme : '#337ab7';
    $user_style_sheet = get_option( '_wfs_user_stylesheet' );

    $style_meta = '';

    $style_meta.= '<style id="wfs-inline-css">
      .fs-btn-primary,
      .wfsmodal-header .modal__close,
      .wfs-modal-minus input,
      .wfs-modal-plus input,
      .wfsmodal-footer .wfs-modal-add-to-cart a,
      .wfs-service-modal-container .nav-tabs li a.active,
      .wfs-service-modal-container button.wfs-update-service,
      .wfs-sidebar-menu ul li a span.wfs-items-count.active {
        background-color: ' . $pri_color_scheme . ' !important;
      }
      .fs-btn-primary:hover {
        background-color: ' . $sec_color_scheme . ' !important;
      }
      .wfs-category-title,
      .wfs-expand-cart i,
      .wfs-compress-cart i,
      .wfs-close-cart-icon i,
      .wfs-cart-purchase-actions-mobile > a i,
      .wfs-cart-service-settings .wfs-change-service {
        color: ' . $pri_color_scheme . ' !important;
      }
      .wfs-cart-wrapper .wfs-close-cart-icon {
        background-color: ' . $pri_color_scheme . ' !important;
      }
      .fs-btn-link,
      .wfs-food-item-title:hover,
      .wfs-expand-cart i:hover,
      .wfs-compress-cart i:hover,
      .wfs-close-cart-icon i:hover,
      .wfs-loop-category__title:hover {
        color: ' . $sec_color_scheme . ' !important;
      }';

    $style_meta .= $user_style_sheet;
    $style_meta .= '</style>';

    echo $style_meta;
  }

  /**
   * Validate cart items based on Food Store admin settings
   * and rules set for each item
   *
   * @since 1.0
   * @author FoodStore
   */
  public function validate_cart_items() {

    $service_type = isset( $_COOKIE['service_type'] ) ? $_COOKIE['service_type'] : 'pickup';

    $minimum_order_amount       = 0;
    $minimum_order_amount_error = '';

    if ( $service_type == 'delivery' ) {
      $minimum_order_amount = get_option( '_wfs_min_delivery_order_amount', true );
      $minimum_order_amount_error = get_option( '_wfs_min_delivery_order_amount_error', true );
    }

    if ( $service_type == 'pickup' ) {
      $minimum_order_amount = get_option( '_wfs_min_pickup_order_amount', true );
      $minimum_order_amount_error = get_option( '_wfs_min_pickup_order_amount_error', true );
    }

    $search   = array( '{service_type}', '{min_order_amount}' );
    $replace  = array( wfs_get_service_label($service_type), wc_price( $minimum_order_amount ) );
    $message  = str_replace( $search, $replace, $minimum_order_amount_error );

    if ( wfs_is_service_enabled() && WC()->cart->total > 0 && WC()->cart->total < $minimum_order_amount ) {
      wc_add_notice( $message, 'error' );
    }
  }

  /**
   * Enqueue script for handelling product variations
   *
   * @since 1.0
   */
  public function variation_script() {
    global $woocommerce;
    wp_enqueue_script( 'wc-add-to-cart-variation' );
  }

  /**
   * Check is item is purchasable or not
   *
   * @since 1.0
   */
  static function wfs_is_purchasable( $product ) {
    return $product->is_purchasable() && $product->is_in_stock() && $product->has_enough_stock( 1 );
  }

  /**
   * Prepare attributes for variations
   *
   * @since 1.0
   */
  static function wfs_data_attributes( $attrs ) {

    $attrs_arr = array();
    foreach ( $attrs as $key => $attr ) {
      $attrs_arr[] = 'data-' . sanitize_title( $key ) . '="' . esc_attr( $attr ) . '"';
    }
    return implode( ' ', $attrs_arr );
  }

  /**
   * Update addons and special notes if any to
   * Cart Item Data
   *
   * @since 1.0.6
   * @author FoodStore
   * @return array $item_data
   */
  public function wfs_get_item_data( $item_data, $cart_item_data  ) {

    $addon_name = '';

    if ( isset( $cart_item_data['addons'] ) && !empty( $cart_item_data['addons'] ) ) {
      $addon_items = $cart_item_data['addons'];

      foreach( $addon_items as $addon_item ) {

        $quantity     = isset( $addon_item['quantity'] ) ? $addon_item['quantity'] : 1;
        $addon_slug   = isset( $addon_item['addon_item']['value'] ) ? $addon_item['addon_item']['value'] : '';
        $addon_price  = isset( $addon_item['price'] ) ? $addon_item['price'] : 0;

        $addon_price  = wfs_calculate_addon_price( $addon_price, $quantity );
        $addon_price  = wc_price( $addon_price );

        if ( !empty( $addon_slug ) ) {
          $addon_term = get_term_by( 'slug', $addon_slug, 'product_addon' );

          if ( $addon_term ) {

            $item_data[] = array(
              'key'     => $addon_term->name,
              'value'   => wc_price( $addon_price ),
              'display' => $addon_price,
            );
          }
        }
      }
    }

    if ( isset( $cart_item_data['special_note'] ) && !empty( $cart_item_data['special_note'] ) ) {

      $item_data[] = array(
        'key'     => __( 'Special Note' , 'food-store' ),
        'display' => $cart_item_data['special_note'],
      );

    }

    return $item_data;
  }

  /**
   * Update addons and special notes to Cart item meta
   *
   * @since 1.0.0
   * @author FoodStore
   */
  public function wfs_save_cart_item_meta( $item, $cart_item_key, $values, $order ) {

    if ( array_key_exists( 'addons', $values ) ) {
      $item->add_meta_data( '_addon_items', $values['addons'], true );
    }

    if ( array_key_exists( 'special_note', $values ) ) {
      $item->add_meta_data( '_special_note', $values['special_note'], true );
    }
  }

  /**
   * Get addons and special notes cart item meta
   *
   * @since 1.0.0
   * @author FoodStore
   */
  public function wfs_order_item_meta( $item_id, $item, $order ) {

    $item_data = '';

    $addon_items = wfs_get_addons_from_meta( $item_id );

    if ( !empty( $addon_items ) && is_array( $addon_items ) ) {
      foreach( $addon_items as $key => $addon_item ) {
        $addon_name  = isset( $addon_item['name'] ) ? $addon_item['name'] : '';
        $addon_quantity = isset( $addon_item['quantity'] ) ? $addon_item['quantity'] : 1;
        $addon_price = isset( $addon_item['price'] ) ? $addon_item['price'] : '';
        $item_data .= sprintf( '<span class="wfs_order_meta">%1$s - %2$s </span>', $addon_name, $addon_price );
      }
    }

    $special_note = wc_get_order_item_meta( $item_id, '_special_note', true );

    if ( !empty( $special_note ) ) {
      $item_data .= sprintf( '<span class="wfs_order_meta">Special Note : %1$s  </span>', $special_note );
    }

    echo $item_data;
  }

  /**
   * Output the Product Variations as Radio buttons
   *
   * @since 1.0.0
   * @author FoodStore
   */
  public function item_variations() {

    global $product;
    global $woocommerce;

    $cart_items = $woocommerce->cart->get_cart();
    $product_id = $product->get_id();
    $cart_key = isset( $_REQUEST['cart_key'] ) ? sanitize_key( $_REQUEST['cart_key'] ) : '';
    $variation_id = '';

    if ( !empty( $cart_key ) && !empty( $cart_items ) ) {

      foreach( $cart_items as $cart_item_key => $cart_item ) {

        if ( $cart_key == $cart_item_key ) {
          $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : '';
        }
      }
    }

    $wfs_item_children = $product->get_children();

    if ( is_array( $wfs_item_children ) && count( $wfs_item_children ) > 0 ) {

      echo '<div class="wfs-variations wfs-variations-default" data-click="0" data-description="no">';

      // get custom pricing title
      $pricing_label = get_post_meta( $product_id, '_wfs_variation_price_label', true );
      $pricing_label = ! empty( $pricing_label ) ? $pricing_label : __( 'Choose Price Option', 'food-store' );

      echo '<p class="wfs-pricing-option-label">' . apply_filters( 'wfs_pricing_option_label', $pricing_label ) . '</p>';

      // show radio buttons
      foreach ( $wfs_item_children as $item_child ) {

          $wfs_child_product = wc_get_product( $item_child );

          if ( ! $wfs_child_product || ! $wfs_child_product->variation_is_visible() ) {
              continue;
          }

          if ( ! self::wfs_is_purchasable( $wfs_child_product ) ) {
              continue;
          }

          $wfs_child_class  = 'wfs-variation wfs-variation-radio';
          $wfs_child_attrs  = htmlspecialchars( json_encode( $wfs_child_product->get_variation_attributes() ), ENT_QUOTES, 'UTF-8' );
          $wfs_child_namee  = wc_get_formatted_variation( $wfs_child_product, true, false, false );

          if( $wfs_child_product->get_image_id() ) {
              $wfs_child_image      = wp_get_attachment_image_src( $wfs_child_product->get_image_id(), 'thumbnail' );
              $wfs_child_image_src  = $wfs_child_image[0];
          } else {
              $wfs_child_image_src  = wc_placeholder_img_src();
          }

          $wfs_child_image_src  = esc_url( apply_filters( 'wfs_variation_image_src', $wfs_child_image_src, $wfs_child_product ) );
          $data_attrs_price = wc_get_price_to_display( $wfs_child_product );
          $data_attrs_price_regular = wc_get_price_to_display( $wfs_child_product, array( 'price' => $wfs_child_product->get_regular_price() ) );

          $data_attrs = apply_filters( 'wfs_item_data_attributes', array(
              'id'            => $item_child,
              'sku'           => $wfs_child_product->get_sku(),
              'purchasable'   => self::wfs_is_purchasable( $wfs_child_product ) ? 'yes' : 'no',
              'attrs'         => $wfs_child_attrs,
              'price'         => $data_attrs_price,
              'regular-price' => $data_attrs_price_regular,
              'pricehtml'     => htmlentities( $wfs_child_product->get_price_html() ),
              'imagesrc'      => $wfs_child_image_src,
          ), $wfs_child_product );

          $attribute_checked = ( $variation_id == $item_child ) ? 'checked' : '';

          echo '<div class="' . esc_attr( $wfs_child_class ) . '" ' . self::wfs_data_attributes( $data_attrs ) . '>';
          echo apply_filters( 'wfs_variation_radio_selector', '<div class="wfs-variation-selector"><input type="radio" name="wfs_variation_' . $product_id . '" ' . $attribute_checked . ' data-child-product="'.$item_child.'" data-attrs="'.$item_child.'|'.$data_attrs_price.'|radio" /></div>', $product_id, $item_child );
          echo apply_filters( 'wfs_variation_image_content', '<div class="wfs-variation-image"><img src="' . $wfs_child_image_src . '"/></div>', $wfs_child_product );
          echo '<div class="wfs-variation-info">';
          echo '<div class="wfs-variation-name">' . apply_filters( 'wfs_variation_name', $wfs_child_namee, $wfs_child_product ) . '</div>';
          echo '<div class="wfs-variation-price">' . apply_filters( 'wfs_variation_price', $wfs_child_product->get_price_html(), $wfs_child_product ) . '</div>';
          echo '</div><!-- /wfs-variation-info -->';
          echo '</div><!-- /wfs-variation -->';
      }

      echo '</div><!-- /wfs-variations -->';
    }
  }

  /**
   * Cart/Checkout price calculations considering the
   * Addons in cart item meta for each product
   *
   * @since 1.0.0
   * @author FoodStore
   */
  public function wfs_adding_custom_price( $cart ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
    	return;


    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
    	return;

    $action     = isset( $_POST['action'] ) ? $_POST['action'] : '';
    $addon_data = isset( $_POST['addon_data'] ) ? $_POST['addon_data'] : '';

    $cart_action = isset( $_POST['action'] ) ? $_POST['action'] : '';
    $item_key    = isset( $_POST['item_key'] ) ? $_POST['item_key'] : '';

    foreach ( $cart->get_cart() as $key => $cart_item ) {

    	$addon_prices = 0;

      if ( 'product_update_cart' == $cart_action ) {
        if ( !empty( $item_key ) && $key == $item_key ) {
          $product_id   = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : '';
          $quantity     = isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1;
          $product      = wc_get_product( $product_id );
          $addon_items  = wfs_format_addons( $addon_data, $quantity, $product );
          $addon_items  = isset( $addon_items['addons'] ) ? $addon_items['addons'] : array() ;
          $cart_item['addons'] = $addon_items;
        }
      }

      if ( isset( $cart_item['addons'] ) && !empty( $cart_item['addons'] ) ) {
        foreach( $cart_item['addons'] as $addon_items ) {
          $addon_price    = $addon_items['raw_price'];
          $addon_quantity = $addon_items['quantity'];
          $addon_prices   = $addon_prices + $addon_price;
        }
      }

    	$product_price 	= $cart_item['data']->get_price();
    	$total_price	  = $product_price + $addon_prices;
    	$cart_item['data']->set_price( round( $total_price, wc_get_price_decimals() ) );
    }
  }

}

new WFS_Frontend();