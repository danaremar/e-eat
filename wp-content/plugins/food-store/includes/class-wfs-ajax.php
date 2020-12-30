<?php
/**
 * FoodStore WFS_AJAX. AJAX Event Handlers.
 *
 * @class   WFS_AJAX
 * @package FoodStore/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * WFS_Ajax class.
 */
class WFS_AJAX {

  /**
   * Hook in ajax handlers.
   *
   * @author Automatic
   * @since 1.0.0
   */
  public static function init() {
    
    add_action( 'init',               array( __CLASS__, 'define_ajax' ), 0 );
    add_action( 'template_redirect',  array( __CLASS__, 'do_wfs_ajax' ), 0 );
    
    self::add_ajax_events();
  }

  /**
   * Set WFS AJAX constant and headers.
   *
   * @author Automatic
   * @since 1.0.0
   */
  public static function define_ajax() {
    // phpcs:disable
    if ( ! empty( $_GET['wfs-ajax'] ) ) {
      wfs_maybe_define_constant( 'DOING_AJAX', true );
      wfs_maybe_define_constant( 'WFS_DOING_AJAX', true );
      $GLOBALS['wpdb']->hide_errors();
    }
  }

  /**
   * Check for WFS Ajax request and fire action.
   *
   * @author Automatic
   * @since 1.0.0
   */
  public static function do_wfs_ajax() {
    global $wp_query;

    if ( ! empty( $_GET['wfs-ajax'] ) ) {
      $wp_query->set( 'wfs-ajax', sanitize_text_field( wp_unslash( $_GET['wfs-ajax'] ) ) );
    }

    $action = $wp_query->get( 'wfs-ajax' );

    if ( $action ) {
      self::wfs_ajax_headers();
      $action = sanitize_text_field( $action );
      do_action( 'wfs_ajax_' . $action );
      wp_die();
    }
  }

  /**
   * Send headers for WFS Ajax Requests.
   *
   * @author Automatic
   * @since 1.0.0
   */
  private static function wfs_ajax_headers() {
    if ( ! headers_sent() ) {
      send_origin_headers();
      send_nosniff_header();
      wfs_nocache_headers();
      header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
      header( 'X-Robots-Tag: noindex' );
      status_header( 200 );
    } elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
      headers_sent( $file, $line );
      trigger_error( "wfs_ajax_headers cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
    }
  }

  /**
   * AJAX Hook in methods
   * Uses WordPress ajax handlers
   *
   * @author Automatic
   * @author WP Scripts
   * @since 1.0.0
   */
  public static function add_ajax_events() {

    $ajax_events_nopriv = array(
      'show_product_modal',
      'add_to_cart',
      'empty_cart',
      'product_remove_cart',
      'product_update_cart',
      'update_service_time',
      'validate_proceed_checkout',
      'render_service_options',
    );

    foreach ( $ajax_events_nopriv as $ajax_event ) {
      add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
      add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
    }

    $ajax_events = array(
      'add_product_addon',
    );

    foreach ( $ajax_events as $ajax_event ) {
      add_action( 'wp_ajax_wfs_' . $ajax_event, array( __CLASS__, $ajax_event ) );
    }
  }

  /**
   * Add Addon Item to Product Row
   *
   * @author WP Scripts
   * @since 1.0.6
   */
  public static function add_product_addon() {

    check_ajax_referer( 'add-addon', 'security', false );

    ob_start();

    if ( !current_user_can( 'edit_products' ) || !isset( $_POST['taxonomy'], $_POST['i'] ) ) {
      wp_die( -1 );
    }

    $i             = absint( $_POST['i'] );
    $metabox_class = array();
    $taxonomy      = sanitize_text_field( $_POST['taxonomy'] );
    $addon         = get_term_by( 'slug', $taxonomy, 'product_addon');

    if( $addon ) {
      $metabox_class[] = 'taxonomy';
      $metabox_class[] = $addon->slug;
    }

    include 'admin/views/html-product-addon.php';
    wp_die();
  }

  /**
   * Show Product Modal with Variations and Addons
   *
   * @author WP Scripts
   * @since 1.0.0
   */
  public static function show_product_modal() {

    check_ajax_referer( 'product-modal', 'security', false );

    global $product;

    ob_start();

    $product_id = isset( $_POST['product_id'] ) ? absint($_POST['product_id']) : '';
    $cart_key   = isset( $_POST['cart_key'] ) ? sanitize_key( $_POST['cart_key'] ) : '';

    // Set default values if its a add to cart call
    $item_quantity  = 1;
    $item_action    = 'add_to_cart';
    $button_text    = wfs_modal_add_to_cart_text();
    $item_key       = '';
    $variation_id   = '';
    $special_note   = '';
    $item_price     = '';

    // Get Product Details
    $product  = wc_get_product( $product_id );
    $title    = $product->get_name();

    if( 'variable' == $product->get_type() ) {
      $item_price = $product->get_variation_price('min');
    } else {
      $item_price = $product->get_price();
    }

    if( !empty( $cart_key ) ) {

      $cart_product   = WC()->cart->get_cart_item( $cart_key );

      // Update values if it's and Edit Cart Call
      $item_quantity  = absint( $cart_product['quantity'] );
      $item_action    = 'product_update_cart';
      $button_text    = wfs_modal_update_cart_text();
      $item_key       = $cart_key;
      $variation_id   = isset( $cart_product['variation_id'] ) ? $cart_product['variation_id'] : '';
      $special_note   = isset( $cart_product['special_note'] ) ? $cart_product['special_note'] : '';
    }

    wfs_get_template(
      'single-product/add-to-cart/item.php',
      array(
        'product'   => $product,
        'cart_key'  => $cart_key,
      )
    );

    $content = ob_get_contents();

    ob_get_clean();

    $response = apply_filters( 'wfs_ajax_load_item_popup', array(
      'content'       => $content,
      'title'         => $title,
      'price'         => wc_price($item_price),
      'raw_price'     => $item_price,
      'product_id'    => $product->get_id(),
      'product_type'  => $product->get_type(),
      'product_qty'   => $item_quantity,
      'action'        => $item_action,
      'action_text'   => $button_text,
      'item_key'      => $item_key,
      'variation_id'  => $variation_id,
      'special_note'  => $special_note,
    ) );

    echo wp_send_json( $response );
    wp_die();
  }

  /***
   * Ajax Add to Cart
   *
   * @author WP Scripts
   * @since 1.0.0
   */
  public static function add_to_cart() {

    check_ajax_referer( 'add-to-cart', 'security', false );

    global $woocommerce;

    $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : '';
    $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity']  ) : 1;
    $addon_data   = isset( $_POST['addon_data'] ) ? $_POST['addon_data'] : array();
    $special_note = isset( $_POST['special_note'] ) ? sanitize_text_field( $_POST['special_note'] ) : '';

    $product_name   = '';
    $status         = '';
    $status_message = '';

    if ( !empty( $product_id ) && 'product' == get_post_type( $product_id ) ) {

      $product = wc_get_product( $product_id );
      $product_name = $product->get_name();

      $addon_items  = wfs_format_addons( $addon_data, $quantity, $product );
      
      if ( ! empty( $special_note ) && is_array( $addon_items ) ) {
        $addon_items['special_note'] = $special_note;
      }

      if ( 'simple' == $product->get_type() ) {
        
        if( ( $product->managing_stock() && $product->get_stock_quantity() < $quantity ) || ! $product->is_in_stock() ) {
          
          $cart_response = false;
          /* translators: %s: $product_name could not be added. Item is out of stock. */
          $status_message = sprintf( __( '%s could not be added. Item is out of stock.', 'food-store' ), $product_name );

        } else {
          
          $cart_response = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id = 0, $variation = array(), $addon_items );
        }
      }

      if ( 'variable' == $product->get_type() ) {

        $variation_data = isset( $_POST['postdata'] ) ? $_POST['postdata'] : array();
        $variation_id = isset( $_POST['variation_id'] ) ? absint($_POST['variation_id']) : '';

        if ( ! empty( $variation_id ) ) {

          $variations = new WC_Product_Variable( $product_id );
          $variations = $variations->get_available_variations();
          $available_variation_ids = array();

          if ( is_array( $variations ) ) {
            $available_variation_ids = wp_list_pluck( $variations, 'variation_id' );
          }

          if ( in_array( $variation_id, $available_variation_ids ) ) {

            $selected_variations = array();

            foreach( $variation_data as $key => $variation_attr ) {
              if( strpos( $variation_attr['name'], 'attribute' ) !== false ) {
                $selected_variations[ $variation_attr['name'] ] = $variation_attr['value'];
              }
            }

            $var_obj = wc_get_product( $variation_id );

            if( ( $var_obj->managing_stock() && $var_obj->get_stock_quantity() < $quantity ) || ! $var_obj->is_in_stock() ) {
              
              $cart_response = false;
              /* translators: %s: $product_name could not be added. Item variation is out of stock. */
              $status_message = sprintf( __( '%s could not be added. Item variation is out of stock.', 'food-store' ), $product_name );

            } else {

              $cart_response = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $selected_variations, $addon_items );
            }
          }
        }
      }

      if ( !empty( $cart_response ) ) {
        $status = 'success';
        /* translators: %s: $product_name added to cart */
        $status_message = sprintf( __( '%s added to cart', 'food-store' ), $product_name );
      } else {
        $status = 'error';
      }
    }

    $cart_contents = wfs_get_cart_contents();

    $response = array(
      'status_message'  => $status_message,
      'status'          => $status,
      'product_name'    => $product_name,
      'cart_content'    => $cart_contents,
    );

    echo wp_send_json( $response );
    wp_die();
  }

  /**
   * Ajax update cart item
   *
   * @author WP Scripts
   * @since 1.0.0
   */
  public static function product_update_cart() {

    check_ajax_referer( 'update-cart-item', 'security', false );

    global $woocommerce;

    $product_id   = isset( $_POST['product_id'] ) ? absint($_POST['product_id']) : '';
    $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
    $item_key     = isset( $_POST['item_key'] ) ? sanitize_key( $_POST['item_key'] ) : '';
    $addon_data   = isset( $_POST['addon_data'] ) ? $_POST['addon_data'] : array();
    $variation_id = isset( $_POST['variation_id'] ) ? absint($_POST['variation_id']) : '';
    $special_note = isset( $_POST['special_note'] ) ? sanitize_text_field( $_POST['special_note'] ) : '';

    $cart = WC()->cart->cart_contents;

    $product_name   = '';
    $status         = '';
    $status_message = '';

    if ( !empty( $product_id ) && !empty( $item_key ) ) {

      $product = wc_get_product( $product_id );
      $product_name = $product->get_name();

      $addon_items  = wfs_format_addons( $addon_data, $quantity, $product );

      if ( !empty( $special_note ) && is_array( $addon_items ) ) {
        $addon_items['special_note'] = $special_note;
      }

      if ( 'simple' == $product->get_type() ) {

        if( ( $product->managing_stock() && $product->get_stock_quantity() < $quantity ) || ! $product->is_in_stock() ) {
          
          $cart_response = false;
          /* translators: %s: $product_name could not be updated. Item is low in stock. */
          $status_message = sprintf( __( '%s could not be updated. Item is low in stock.', 'food-store' ), $product_name );

        } else {

          // Update Quantity
          $cart_response = $woocommerce->cart->set_quantity( $item_key, $quantity );

          // Update Item addons
          if( ! empty( $addon_items['addons'] ) ) {
            WC()->cart->cart_contents[$item_key]['addons'] = $addon_items['addons'];
          }
        }

        // Update special instruction
        $special_note = isset( $addon_items['special_note'] ) ? $addon_items['special_note'] : '';
        WC()->cart->cart_contents[$item_key]['special_note'] = $special_note;
      }

      if ( 'variable' == $product->get_type() ) {

        $variation_data = isset( $_POST['postdata'] ) ? $_POST['postdata'] : array();
        $cart_variation_id = $cart[$item_key]['variation_id'];

        if ( ! empty( $variation_id ) ) {

          $var_obj = wc_get_product( $variation_id );

          if( ( $var_obj->managing_stock() && $var_obj->get_stock_quantity() < $quantity ) || ! $var_obj->is_in_stock() ) {
            
            $cart_response = false;
            /* translators: %s: $product_name could not be updated. Item variation is low in stock. */
            $status_message = sprintf( __( '%s could not be updated. Item variation is low in stock.', 'food-store' ), $product_name );

          } else {

            if ( $cart_variation_id == $variation_id ) {

              // Update quantity
              $cart_response = $woocommerce->cart->set_quantity( $item_key, $quantity );

              if( ! empty( $addon_items['special_note'] ) ) {
                WC()->cart->cart_contents[$item_key]['special_note'] = $addon_items['special_note'];
              }

              // Update Item addons
              WC()->cart->cart_contents[$item_key]['addons'] = $addon_items['addons'];

            } else {

              // Lets delete the item first
              WC()->cart->remove_cart_item( $item_key );

              // Now add the new item
              $variations = new WC_Product_Variable( $product_id );
              $variations = $variations->get_available_variations();
              $available_variation_ids = array();

              if ( is_array( $variations ) ) {
                $available_variation_ids = wp_list_pluck( $variations, 'variation_id' );
              }

              if ( in_array( $variation_id, $available_variation_ids ) ) {

                $selected_variations = array();

                foreach( $variation_data as $key => $variation_attr ) {
                  if( strpos( $variation_attr['name'], 'attribute' ) !== false ) {
                    $selected_variations[ $variation_attr['name'] ] = $variation_attr['value'];
                  }
                }
              }

              $cart_response = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $selected_variations, $addon_items );
            }
          }
        }
      }

      if ( !empty( $cart_response ) ) {
        $status = 'success';
        /* translators: %s: $product_name updated in cart */
        $status_message = sprintf( __( '%s updated in cart.', 'food-store' ), $product_name );
      } else {
        $status = 'error';
      }
    }

    // Updating Woocommerce Cart and Totals
    WC()->cart->calculate_totals();

    $cart_contents = wfs_get_cart_contents();

    $response = array(
      'status_message'  => $status_message,
      'status'          => $status,
      'product_name'    => $product_name,
      'cart_content'    => $cart_contents,
    );

    echo wp_send_json( $response );
    wp_die();
  }

  /**
   * Remove Product From Cart
   *
   * @author WP Scripts
   * @since 1.0.0
   */
  public static function product_remove_cart() {

    check_ajax_referer( 'product-remove-cart', 'security', false );

    global $woocommerce;

    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : '';
    $cart_key = isset( $_POST['cart_key'] ) ? sanitize_key( $_POST['cart_key'] ) : '';

    $status = 'error';

    if ( !empty( $product_id ) && !empty( $cart_key ) ) {

      $product = wc_get_product( $product_id );
      $product_name = $product->get_name();
      WC()->cart->remove_cart_item( $cart_key );

      $status = 'success';
    }

    $cart_contents = wfs_get_cart_contents();

    $response = array(
      'status'       => $status,
      'cart_content' => $cart_contents,
      /* translators: %s: $product_name removed from cart */
      'message'      =>  sprintf( __( '%s has been removed from cart', 'food-store' ), $product_name ),
    );

    echo wp_send_json( $response );
    wp_die();
  }

  /**
   * Empty Cart
   *
   * @author WP Scripts
   * @since 1.0.0
   */
  public static function empty_cart() {

    check_ajax_referer( 'empty-cart', 'security', false );

    ob_start();

    global $woocommerce;

    $woocommerce->cart->empty_cart();

    // Empty the session for Service
    unset( $_COOKIE['service_type'] );
    unset( $_COOKIE['service_date'] );
    unset( $_COOKIE['service_time'] );

    setcookie( 'service_type', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
    setcookie( 'service_date', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
    setcookie( 'service_time', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN );

    $cart_contents = wfs_get_cart_contents();

    $response = array(
      'status'       => 'success',
      'cart_content' => $cart_contents,
    );

    echo wp_send_json( $response );
    wp_die();
  }

  /**
   * Update Service Type and Time
   *
   * @author WP Scripts
   * @since 1.1
   */
  public static function update_service_time() {
    
    $service_type = ! empty( $_POST['selected_service'] ) ? sanitize_text_field( $_POST['selected_service'] ) : wfs_get_default_service_type();
    $service_time = ! empty( $_POST['selected_time'] ) ? sanitize_text_field( $_POST['selected_time'] ) : '';

    if ( ! empty( $service_type ) && ! empty( $service_time ) ) {
      
      setcookie( 'service_type', $service_type, time() + 1800, COOKIEPATH, COOKIE_DOMAIN );
      setcookie( 'service_time', $service_time, time() + 1800, COOKIEPATH, COOKIE_DOMAIN );

      $status = 'success';
      $message = '';

    } else {
      $status = 'error';
      $message = __( 'Please select a service time.', 'food-store' );
    }

    $response = array(
      'status'        => $status,
      'service_type'  => wfs_get_service_label($service_type),
      'service_time'  => $service_time,
      'message'       => $message
    );

    $response = apply_filters( 'wfs_service_details', $response, $_POST );

    echo wp_send_json( $response );
    wp_die();
  }

  /**
   * Validate Proceed to Checkout
   *
   * @author WP Scripts
   * @since 1.0.10
   */
  public static function validate_proceed_checkout() {

    $response = wfs_pre_validate_order();
    $response = apply_filters( 'wfs_validate_proceed_checkout', $response );

    wp_send_json( $response );
    wp_die();
  }

  /**
   * Prepare Service date and Time options
   *
   * @author WP Scripts
   * @since 1.1.4
   */
  public static function render_service_options() {
    $get_response = apply_filters( 'wfs_service_options', $_POST );
    wp_send_json( $get_response );
    wp_die();
  }
}

WFS_AJAX::init();