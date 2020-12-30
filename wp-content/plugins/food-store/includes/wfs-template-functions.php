<?php
/**
 * FoodStore Template
 *
 * Functions for the templating system.
 *
 * @package  FoodStore\Functions
 * @version  1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wfs_start' ) ) {

  /**
   * Output the start of a foodstore.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_start( $echo = true ) {
    
    ob_start();

    wfs_get_template( 'wfs-start.php' );

    $container_start = apply_filters( 'wfs_start', ob_get_clean() );

    if ( $echo ) {
      echo $container_start;
    } else {
      return $container_start;
    }
  }

}

if ( ! function_exists( 'wfs_end' ) ) {

  /**
   * Output the end of a foodstore.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_end( $echo = true ) {
    
    ob_start();

    wfs_get_template( 'wfs-end.php' );

    $container_end = apply_filters( 'wfs_end', ob_get_clean() );

    if ( $echo ) {
      echo $container_end;
    } else {
      return $container_end;
    }
  }
}

if ( ! function_exists( 'wfs_category_start' ) ) {

  /**
   * Output the start of a foodstore category.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_category_start( $echo = true ) {
    
    ob_start();

    wfs_get_template( 'loop/wfs-category-start.php' );

    $container_start = apply_filters( 'wfs_category_start', ob_get_clean() );

    if ( $echo ) {
      echo $container_start;
    } else {
      return $container_start;
    }
  }
}

if ( ! function_exists( 'wfs_category_end' ) ) {

  /**
   * Output the end of a foodstore category.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_category_end( $echo = true ) {
    ob_start();

    wfs_get_template( 'loop/wfs-category-end.php' );

    $container_end = apply_filters( 'wfs_category_end', ob_get_clean() );

    if ( $echo ) {
      echo $container_end;
    } else {
      return $container_end;
    }
  }
}

if ( ! function_exists( 'wfs_product_start' ) ) {

  /**
   * Output the start of a foodstore products.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_product_start( $echo = true ) {
    
    ob_start();

    wfs_get_template( 'loop/wfs-product-start.php' );

    $container_start = apply_filters( 'wfs_product_start', ob_get_clean() );

    if ( $echo ) {
      echo $container_start;
    } else {
      return $container_start;
    }
  }
}

if ( ! function_exists( 'wfs_product_end' ) ) {

  /**
   * Output the end of a foodstore products.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_product_end( $echo = true ) {
    ob_start();

    wfs_get_template( 'loop/wfs-product-end.php' );

    $container_end = apply_filters( 'wfs_product_end', ob_get_clean() );

    if ( $echo ) {
      echo $container_end;
    } else {
      return $container_end;
    }
  }
}

if ( ! function_exists( 'wfs_listing_start' ) ) {

  /**
   * Output the start of a foodstore products listings.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_listing_start( $echo = true, $term_id = '' ) {
    ob_start();

    wfs_get_template( 'loop/wfs-product-listing-start.php', array(
      'category_id' => $term_id,
    ) );

    $container_start = apply_filters( 'wfs_product_listing_start', ob_get_clean() );

    if ( $echo ) {
      echo $container_start;
    } else {
      return $container_start;
    }
  }
}

if ( ! function_exists( 'wfs_listing_end' ) ) {

  /**
   * Output the end of a foodstore products.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_listing_end( $echo = true, $term_id = '' ) {
    ob_start();

    wfs_get_template( 'loop/wfs-product-listing-end.php', array(
      'category_id' => $term_id,
    ) );

    $container_end = apply_filters( 'wfs_product_listing_end', ob_get_clean() );

    if ( $echo ) {
      echo $container_end;
    } else {
      return $container_end;
    }
  }
}

if ( ! function_exists( 'wfs_template_loop_category_title' ) ) {

  /**
   * Show the subcategory title in the product loop.
   *
   * @param object $category Category object.
   */
  function wfs_template_loop_category_title( $category ) {

    $show_count = get_option( '_wfs_listing_show_sidebar_count', 'no' );
    ?>
    <a class="wfs-loop-category__title" data-category-title="<?php echo $category->slug; ?>" href="javascript:void(0);">
      <?php
      echo esc_html( $category->name );
      if ( $category->count > 0 && 'yes' === $show_count ) {
        echo apply_filters( 'wfs_subcategory_count_html', ' <span class="wfs-items-count">' . esc_html( $category->count ) . '</span>', $category );
      }
      ?>
    </a>
    <?php
  }
}

if ( ! function_exists( 'wfs_product_title' ) ) {

  /**
   * Output the product title.
   */
  function wfs_product_title() {
    wfs_get_template( 'single-product/title.php' );
  }
}

if ( ! function_exists( 'wfs_template_show_images' ) ) {

  /**
   * Output the product image.
   */
  function wfs_template_show_images() {
    wfs_get_template( 'single-product/product-image.php' );
  }
}

if ( ! function_exists( 'wfs_product_price' ) ) {

  /**
   * Output the product price.
   */
  function wfs_product_price() {
    wfs_get_template( 'single-product/price.php' );
  }
}

if ( ! function_exists( 'wfs_product_short_description' ) ) {

  /**
   * Output the product short description.
   */
  function wfs_product_short_description() {
    wfs_get_template( 'single-product/short-description.php' );
  }
}

if ( ! function_exists( 'wfs_product_action_button' ) ) {

  /**
   * Output the product add to cart button for modal.
   */
  function wfs_product_action_button() {
    wfs_get_template( 'single-product/add-cart-button.php' );
  }
}

if ( ! function_exists( 'wfs_footer_cart' ) ) {

  /**
   * Output the footer cart.
   */
  function wfs_footer_cart() {

    if( wfs_is_foodstore_page() ) {
      
      wfs_cart_start();
      wfs_get_template( 'cart/wfs-cart.php' );
      wfs_cart_end();

      // Product Modal Content
      wfs_get_template( 'cart/product-modal.php' );

      // Service Modal Content
      wfs_get_template( 'services/service-modal.php' );
    }    
  }
}

if ( ! function_exists( 'wfs_cart_start' ) ) {

  /**
   * Output the start of a foodstore cart.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_cart_start( $echo = true ) {
    
    ob_start();

    wfs_get_template( 'cart/cart-start.php' );

    $cart_container_start = apply_filters( 'wfs_cart_start', ob_get_clean() );

    if ( $echo ) {
      echo $cart_container_start;
    } else {
      return $cart_container_start;
    }
  }
}

if ( ! function_exists( 'wfs_cart_end' ) ) {

  /**
   * Output the end of a foodstore cart.
   *
   * @param bool $echo Should echo?.
   * @return string
   */
  function wfs_cart_end( $echo = true ) {
    
    ob_start();

    wfs_get_template( 'cart/cart-end.php' );

    $cart_container_end = apply_filters( 'wfs_cart_end', ob_get_clean() );

    if ( $echo ) {
      echo $cart_container_end;
    } else {
      return $cart_container_end;
    }
  }
}

if ( ! function_exists( 'wfs_render_product_addons' ) ) {

  /**
   * Get contents for addons area
   *
   * @param obj | $product
   * @param str | $cart_key
   */
  function wfs_render_product_addons( $product, $cart_key ) {
    
    ob_start();

    wfs_get_template( 'single-product/add-to-cart/addons.php', 
      array(
        'product'   => $product,
        'cart_key'  => $cart_key,
      )
    );
   
    $addon_data = ob_get_clean();
    
    echo $addon_data;
  }
}