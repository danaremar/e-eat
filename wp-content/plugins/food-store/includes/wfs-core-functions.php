<?php
/**
 * FoodStore Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package FoodStore\Functions
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Include core functions (available in both admin and frontend).
require WFS_ABSPATH . 'includes/wfs-page-functions.php';

/**
 * Freemius Integration
 */
if ( ! function_exists( 'wfs_fs' ) ) {
    
    // Create a helper function for easy SDK access.
    function wfs_fs() {
        
        global $wfs_fs;

        if ( ! isset( $wfs_fs ) ) {
            
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wfs_fs = fs_dynamic_init( array(
                'id'                  => '6737',
                'slug'                => 'food-store',
                'type'                => 'plugin',
                'public_key'          => 'pk_98121c1590de468faa96e1851ad40',
                'is_premium'          => false,
                'has_addons'          => true,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'wfs-settings',
                    'first-path'     => 'admin.php?page=wfs-settings',
                ),
            ) );
        }

        return $wfs_fs;
    }

    // Init Freemius.
    wfs_fs();
    
    // Signal that SDK was initiated.
    do_action( 'wfs_fs_loaded' );
}

/**
 * Get the list of Addons available in Food Store
 */
function wfs_get_all_addons( $args = array() ) {

	$defaults = array(
	    'taxonomy' => 'product_addon',
	    'hide_empty' => false,
	);
	$args = wp_parse_args( $args, $defaults );
	$addons = get_terms( $args );
	
	return $addons;
}

/**
 * Get all the addons associated with a product 
 */
function wfs_get_product_addons( $product_id ) {

  $terms = get_the_terms( $product_id, 'product_addon' );

	if( $terms && !is_wp_error( $terms )) {
		return $terms; 
	} else {
		return array();
	}
}

/**
 * Load template
 *
 * @param string $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 */
 function wfs_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
  	if ( $args && is_array( $args ) ) {
    	extract( $args );
    }
    $located = wfs_locate_template( $template_name, $template_path, $default_path);
    include ( $located);
}

/**
 * Locate template file
 * @param string $template_name
 * @param string $template_path
 * @param string $default_path
 * @return string
 */
 function wfs_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	
	$default_path = apply_filters( 'wfs_template_path', $default_path );
  
  if ( ! $template_path ) {
  	$template_path = 'food-store';
  }
  if ( ! $default_path ) {
      $default_path = WFS_ABSPATH . 'templates/';
  }

  // Look within passed path within the theme - this is priority
  $template = locate_template( array( trailingslashit( $template_path ) . $template_name, $template_name ) );
  
  // Add support of third perty plugin
  $template = apply_filters( 'wfs_locate_template', $template, $template_name, $template_path, $default_path );
  
  // Get default template
  if ( ! $template ) {
  	$template = $default_path . $template_name;
  }
  return $template;
}

/**
 * Get the add to cart button text for product.
 *
 * @access public
 * @return string
 */
function wfs_add_to_cart_text() {
  return apply_filters( 'wfs_product_add_to_cart_text', __( 'Add', 'food-store' ) );
}

/**
 * Get the add to cart button text for product modal.
 *
 * @access public
 * @return string
 */
function wfs_modal_add_to_cart_text() {
  return apply_filters( 'wfs_modal_product_add_to_cart_text', __( 'Add To Cart', 'food-store' ) );
}

/**
 * Get the update cart button text for product modal.
 *
 * @access public
 * @return string
 */
function wfs_modal_update_cart_text() {
  return apply_filters( 'wfs_modal_product_update_cart_text', __( 'Update Cart', 'food-store' ) );
}

/**
 * Show processing message when product adds to cart
 *
 * @access public
 * @return string
 */
function wfs_cart_processing_message() {
  return apply_filters( 'wfs_cart_processing_text', __( 'Please wait', 'food-store' ) );
}

/**
 * Clear cart message
 *
 * @access public
 * @return string
 */
function wfs_empty_cart() {
  return apply_filters( 'wfs_empty_cart_text', __( 'Clear all', 'food-store' ) );
}

/** 
 * Show message when cart is empty
 * @access public
 * @return string
 */
function wfs_empty_cart_message() {
  return apply_filters( 'wfs_empty_cart_message', __( 'Your cart is empty.', 'food-store' ) );
}

/**
 * Get an array of exclude categories
 *
 * @access public
 * @return array
 */
function wfs_get_exclude_categories() {
  
  $exclude_categories = get_option( '_wfs_exclude_categories', true );
  $exclude_categories = is_array( $exclude_categories ) ? $exclude_categories : array();
  $exclude_categories = apply_filters( 'wfs_exclude_categories', $exclude_categories );
  return $exclude_categories;
}

/**
 * Get cat contents HTML with ajax call
 *
 * @access public
 * @return void
 */
function wfs_get_cart_contents() {
  ob_start();
  wfs_get_template( 'cart/wfs-cart.php' );
  return ob_get_clean();
}

/**
 * Get service status of the store
 *
 * @access public
 * @return boolean | status
 */
function wfs_is_service_enabled() {
  $service_option = get_option( '_wfs_enable_service', 'yes' );
  $service_option = apply_filters( 'wfs_is_service_enabled', $service_option );
  return ( 'yes' == $service_option ) ? true : false;
}

/**
 * Show default service time when set based 
 * on Admin Settings
 *
 * @access public
 * @return array
 */
function wfs_service_time() {

  $service_type = isset( $_COOKIE['service_type'] ) ? $_COOKIE['service_type'] : '';
  $service_time = isset( $_COOKIE['service_time'] ) ? $_COOKIE['service_time'] : '';
  
  /* translators: %1$s: get service label */
  /* translators: %2$s: get service time */
  /* translators: %3$s: javascript void URL */
  $output = sprintf( __( '<span class="wfs-service-type">%1$s</span> at <span class="wfs-service-time">%2$s</span> <a class="wfs-change-service" href="%3$s">Change?</a>', 'food-store' ), wfs_get_service_label( $service_type ), $service_time, 'javascript:void(0)' );

  $output = apply_filters( 'wfs_service_details_text', $output );

  if( ! empty( $service_time ) ) {
    $hidden_class = '';
  } else {
    $hidden_class = ' fs-hidden';
  }

  $html = '';
  $html.= '<div class="wfs-cart-service-settings'.$hidden_class.'">';
  $html.= $output;
  $html.= '</div>';

  return $html;
}

/** 
 * Show store message
 * 
 * @access public
 * @return string
 */
function wfs_store_message() {
  
  if ( wfs_check_store_closed() && wfs_is_service_enabled() ) {
    
    ob_start();
    
    $store_message = get_option( '_wfs_store_closed_message', true );
    $store_message = apply_filters( 'wfs_store_message', $store_message );
    
    ?>

    <div class="wfs-store-close-msg">
      <span>
        <?php echo $store_message; ?>
      </span>
    </div>
    
    <?php
    
    echo ob_get_clean();
  }
}

/** 
 * Check whether store is closed
 *
 * @access public
 * @return string
 */
function wfs_check_store_closed() {
  
  $current_time     = current_time( 'timestamp' );
  $store_open_time  = get_option( '_wfs_open_time', true );
  $store_close_time = get_option( '_wfs_close_time', true );

  if ( !empty( $store_open_time ) ) {
    $store_open_time = strtotime( date_i18n( 'Y-m-d' ) . ' ' . $store_open_time );
  }

  if ( !empty( $store_close_time ) ) {
    $store_close_time = strtotime( date_i18n( 'Y-m-d' ) . ' ' . $store_close_time );
  }

  $response = false;

  if ( $current_time > $store_close_time || $current_time < $store_open_time ) {
    $response = true;
  }

  $response = (bool) apply_filters( 'wfs_is_store_closed', $response );

  return $response;
}

/**
 * Verify if we need to check minimum order amount
 *
 * @access public
 * @return array
 */
function wfs_check_min_order() {
  global $woocommerce; 
  $pickup_min_order = get_option( '_wfs_min_pickup_order_amount', true );
  $cart_subtotal = $woocommerce->cart->subtotal;

  if ( !empty( $pickup_min_order ) && $pickup_min_order > $cart_subtotal ) {
    $response = true;
  }
  else {
    $response = false;
  }

  return $response;
}

/**
 * Get list of addon selected for an item
 *
 * @access public
 * @return array
 */
function wfs_get_term_choice( $term_id ) {
  
  if ( ! empty( $term_id ) ) {
    
    $choice = get_term_meta( $term_id, '_wfs_addon_selection_option', true );
    $choice = empty( $choice ) ? 'single' : $choice;
    $choice = ( $choice == 'single' ) ? 'radio' : 'checkbox';
    return apply_filters( 'wfs_category_choice', $choice, $term_id );
  }
}

/**
 * Get addon price
 *
 * @access public
 * @return array
 */
function wfs_get_addon_price( $product, $price ) {

  $consider_tax = true;
  
  if ( $consider_tax ) {
    $price = 'incl' === get_option('woocommerce_tax_display_shop') ?
      wc_get_price_including_tax( $product, array(
        'qty' => 1,
        'price' => $price,
      )) :
      wc_get_price_excluding_tax( $product, array(
        'qty' => 1,
        'price' => $price,
      ));
  }

  return apply_filters( 'wfs_addon_item_price', $price );
}

/**
 * Format addons for frontend display
 *
 * @access public
 * @return array
 */
function wfs_format_addons( $addons, $quantity, $product ) {
 
  $addons_array = array(); 
  
  if ( !empty( $addons ) && is_array( $addons ) ) {
    
    foreach( $addons as $key => $addon ) {

      $addon_slug = isset( $addon['value'] ) ? $addon['value'] : '';
      $addon_data = get_term_by( 'slug', $addon_slug, 'product_addon' );
      $addon_id   = $addon_data->term_id;
      $price      = get_term_meta( $addon_id, '_wfs_addon_item_price', true );
      $price      = !empty( $price ) ? floatval( $price ) : '0.00';
      
      $addon_price = wfs_get_addon_price( $product, $price );

      $addons_array['addons'][$key]['quantity']   = $quantity;
      $addons_array['addons'][$key]['addon_item'] = $addon;
      $addons_array['addons'][$key]['price']      = $addon_price;
      $addons_array['addons'][$key]['raw_price']  = $price;
    }
  }
  return apply_filters( 'wfs_addon_items', $addons_array );
}

/**
 * List of formatted addons
 *
 * @access public
 * @return array
 */
function wfs_get_formatted_addons( $cart_item ) {
  
  $addons_html = '';

  if ( isset( $cart_item['addons'] ) && !empty( $cart_item['addons'] ) ) {
    
    foreach( $cart_item['addons'] as $key => $addons ) {
    
      if ( isset( $addons['addon_item']['value'] ) && !empty( isset( $addons['addon_item']['value'] ) ) ) {
        
        $addon_item_name = $addons['addon_item']['value'];
        $addon_item = get_term_by( 'slug', $addon_item_name, 'product_addon' );
        $addon_quantity = isset( $addons['quantity'] ) ? $addons['quantity'] : 1;

        $addon_price = isset( $addons['price'] ) ? $addons['price'] : 0 ;

        if ( !empty( $addon_item ) ) {
          $addon_price = isset( $addons['price'] ) ? wc_price( $addons['price'] ) : w_price( '0.00' );
          $addons_html .= '<p class="wfs-cart-addon-item">- ' . $addon_item->name . ' - ' . $addon_quantity . ' &times; ' . $addon_price .  '</p>';
        }
      }
    }
  }
  return $addons_html;
}

/**
 * Check which addons are added in case of Edit Product
 *
 * @access public
 * @return array
 */
function wfs_check_addon_in_cart( $field_name, $category_slug, $cart_addons ) {
  
  $addon_values = array();

  if ( is_array( $cart_addons ) && !empty( $cart_addons ) ) {
    foreach( $cart_addons as $key => $cart_addon ) {
      if ( isset( $cart_addon['addon_item'] ) && !empty( $cart_addon['addon_item'] ) ) {
        $selected_addon = isset( $cart_addon['addon_item']['value'] ) ? $cart_addon['addon_item']['value'] : '';
        
        if ( !empty( $selected_addon ) ) {
          array_push( $addon_values, $selected_addon );
        }
      }
    }
  }

  $cond = in_array( $category_slug, $addon_values ) ? true : false;
  return $cond;
}

/**
 * Prepare and sort addon parent categories 
 * for Frontend Display
 *
 * @access public
 * @return array
 */
function wfs_sort_addon_categories( $addon_categories ) {
  
  $parent_ids = array();
  $term_array = array();

  if ( is_array( $addon_categories ) && !empty( $addon_categories ) ) {
    foreach( $addon_categories as $addon_category ) {
      array_push( $parent_ids, $addon_category->parent );
    }
    $parent_ids = array_unique( $parent_ids );

    foreach( $parent_ids as $parent_id ) {
      foreach( $addon_categories as $key => $addon_category ) {
        if ( $addon_category->parent == $parent_id ) {
          array_push( $term_array, $addon_categories[$key] );
        }
      }
    }
  }

  return $term_array;
}

/**
 * Get shorcode attrs and prepare for layout generation
 *
 * @access public
 * @return array
 */
function wfs_render_shortcode_cats( $args ) {
  
  $category_ids = array();
  $categories   = array();

  if ( $args['category'] && $args['category'] != '' ) {
    $categories = explode( ',', $args['category'] );
  }

  if ( is_array( $categories ) && !empty( $categories ) ) {
    foreach( $categories as $category ) {
      $is_ids = is_int( $category ) && ! empty( $category );
      
      if ( $is_ids ) {
        $term_id = $category;
      } else {
        $term = get_term_by( 'slug', $category, 'product_cat' );
        if( ! $term ) {
          continue;
        }

        $term_id = $term->term_id;
      }
      $category_ids[] = $term_id;
    }
  }

  return $category_ids;
}

/**
 * Get list of addons from Order Meta
 *
 * @access public
 * @return array
 */
function wfs_get_addons_from_meta( $item_id ) {
  
  if ( empty( $item_id ) ) 
    return;

  $addon_items = wc_get_order_item_meta( $item_id, '_addon_items', true );

  $item_data = [];
    
  if ( is_array( $addon_items ) && !empty( $addon_items ) ) {
      
    foreach( $addon_items as $key => $addon_item ) {
        
      $addon_slug   = isset( $addon_item['addon_item']['value'] ) ? $addon_item['addon_item']['value'] : '';
      $addon_qty    = isset( $addon_item['quantity'] ) ? $addon_item['quantity'] : 1;
      $addon_price  = isset( $addon_item['price'] ) ? $addon_item['price'] : 0;
      $addon_price  = wfs_calculate_addon_price( $addon_price, $addon_qty );
      $addon_price  = wc_price( $addon_price );

      if ( ! empty( $addon_slug ) ) {
          
        $addon_term = get_term_by( 'slug', $addon_slug, 'product_addon' );

        if ( $addon_term ) {

          $item_data[$key]['name']  = $addon_term->name;
          $item_data[$key]['price'] = $addon_price;

        }
      }
    }
  }

  return $item_data;
}

/**
 * Calculate price of individual addon in terms of quantity
 *
 * @access public
 * @return array
 */
function wfs_calculate_addon_price( $addon_price, $addon_quantity ) {
  $price = 0;

  if ( !empty( $addon_quantity ) && !empty( $addon_price ) ) {
    $price = (int) $addon_quantity  * floatval( $addon_price );
  }
  return $price;
}

/**
 * Generate aailable times of Delivery and Pickup
 * Based on admin settings
 *
 * @access public
 * @return array
 */
function wfs_get_store_timing( $service_type ) {
  
  $store_open_time    = get_option( '_wfs_open_time' );
  $store_close_time   = get_option( '_wfs_close_time' );

  $open_time          = strtotime( date_i18n( 'Y-m-d' ) . ' ' . $store_open_time );
  $close_time         = strtotime( date_i18n( 'Y-m-d' ) . ' ' . $store_close_time );
  
  $pickup_interval    = wfs_get_service_time_interval( 'pickup' );
  $delivery_interval  = wfs_get_service_time_interval( 'delivery' );

  $current_unix_time  = current_time( 'timestamp' );
  $store_timings      = array();

  if ( $service_type == 'delivery' ) {
    if( ( $close_time - $open_time ) > $delivery_interval )
      $store_timings = range( $open_time, $close_time, $delivery_interval );
  } else {
    if( ( $close_time - $open_time ) > $pickup_interval )
      $store_timings = range( $open_time, $close_time, $pickup_interval );
  }

  $store_hours = array();

  if ( !empty( $store_timings ) ) {
    
    foreach( $store_timings as $store_time ) {
      
      // Calculate allowed available time after considering food preparation time
      $allowed_time = wfs_render_with_prepartion_time( $current_unix_time );
      
      if ( $store_time > $allowed_time ) {
        $store_hours[] = $store_time;
      }
    }
  }
  
  return $store_hours;
}

/**
 * Get store time format
 *
 * @since 1.1
 * @return array
 */
function wfs_get_store_time_format() {
  
  $store_time_format = get_option( 'time_format' );
  return apply_filters( 'wfs_store_time_format', $store_time_format );
}

/**
 * Get the time interval for Pickup and Delivery Service
 *
 * @since 1.2.4
 * @return int | interval in seconds
 */
function wfs_get_service_time_interval( $service ) {
  
  $service_interval = get_option( '_wfs_'.$service.'_time_interval', 0 );
  $service_interval  = intval( $service_interval ) * 60;
  
  return $service_interval;
}
  
/**
 * Render food preparation time with service time 
 *
 * @return int
 * @since 1.0
 */
function wfs_render_with_prepartion_time( $service_time ) {
            
  $preparation_time = get_option( '_wfs_food_prepation_time', 0 );
  $preparation_time = $preparation_time * 60;

  if( !is_null( $preparation_time ) && $preparation_time > 0 ) {
    $service_time = $service_time + $preparation_time;
  }

  return $service_time;
}

/**
 * Get available service Hours
 *
 * @access public
 * @return array
 */
function wfs_render_service_hours( $service_type ) {
  
  $store_hours = wfs_get_store_timing( $service_type );
  $time_format = wfs_get_store_time_format();
  $store_closed_message = get_option( '_wfs_store_closed_message', true );

  ob_start(); 

  ?>

  <?php if ( ! empty( $store_hours ) && is_array( $store_hours ) ) : ?>
    
    <select class="wfs-service-hours" id="wfs-<?php echo $service_type; ?>-service" >
      <?php foreach( $store_hours as $store_time ) : 
        $store_time = date( $time_format, $store_time ); ?>
        <option value="<?php echo $store_time; ?>">
          <?php echo $store_time; ?>    
        </option>
      <?php endforeach; ?>
    </select>

  <?php else: ?>

    <select class="wfs-service-hours" style="display:none;" id="wfs-<?php echo $service_type; ?>-service" >
    </select>
    <div class="wfs-no-service-time-message">
      <?php echo apply_filters( 'wfs_no_service_hours_message', $store_closed_message ); ?>
    </div>

  <?php endif; ?>
  
  <?php echo ob_get_clean();
}

/**
 * Get list of available services
 *
 * @access public
 * @return array
 */
function wfs_get_available_services() {

  $enable_delivery = ( get_option( '_wfs_enable_delivery' ) == 'yes' ) ? true : false;
  $enable_pickup   = ( get_option( '_wfs_enable_pickup' ) == 'yes' ) ? true : false ;

  if( $enable_delivery && $enable_pickup )
    return 'all';
  else if( $enable_pickup )
    return 'pickup';
  else if( $enable_delivery )
    return 'delivery';
  else
    return 'pickup';
}

/**
 * Get label to display for a particular Service Type
 *
 * @access public
 * @return array
 */
function wfs_get_service_label( $service ) {

  $services = array(
    'pickup'    => ! empty( get_option( '_wfs_pickup_label' ) ) ? get_option( '_wfs_pickup_label' ) : __( 'Pickup', 'food-store' ),
    'delivery'  => ! empty( get_option( '_wfs_delivery_label' ) ) ? get_option( '_wfs_delivery_label' ) : __( 'Delivery', 'food-store' ),
  );

  $services = apply_filters( 'wfs_active_servicea', $services );

  if ( array_key_exists( $service, $services ) ) {
    $service = $services[$service];
  }

  return $service;
}

/**
 * Get default service type in case of Fallback
 *
 * @access public
 * @return array
 */
function wfs_get_default_service_type() {
  
  $service_type = isset( $_COOKIE['service_type'] ) ? $_COOKIE['service_type'] : '';
    
  if ( empty( $service_type ) ) {
    
    // Check pickup is enabled or not
    $enable_pickup = get_option( '_wfs_enable_pickup', '' );
    $enable_delivery = get_option( '_wfs_enable_delivery', '' );

    if ( $enable_pickup == 'yes' ) {
      $service_type = 'pickup';
    } else if ( $enable_delivery == 'yes' ) {
      $service_type = 'delivery';
    } else {
      $service_type = 'pickup';
    }
  }

  return apply_filters( 'wfs_get_default_service_type', $service_type );
}

/**
 * Validate all consitions with admin settings 
 * before placing an order
 *
 * @access public
 * @return array
 */
function wfs_pre_validate_order() {
  
  global $woocommerce;

  $service_time   = isset( $_COOKIE['service_time'] ) ? $_COOKIE['service_time'] : '';
  $service_type   = wfs_get_default_service_type();
  $cart_subtotal  = $woocommerce->cart->subtotal;

  if ( $service_type == 'pickup' ) {
    $min_order_amount = get_option( '_wfs_min_pickup_order_amount', true );
    $error_message    = get_option( '_wfs_min_pickup_order_amount_error', true );
  } else {
    $min_order_amount = get_option( '_wfs_min_delivery_order_amount', true );
    $error_message    = get_option( '_wfs_min_delivery_order_amount_error', true );
  }

  $search   = array( '{min_order_amount}', '{service_type}' );
  $replace  = array( wc_price( $min_order_amount ), $service_type );

  $error_message = str_replace( $search, $replace, $error_message );
  
  if( wfs_is_service_enabled() && empty( $service_time ) ) {

    $store_closed_message = get_option( '_wfs_store_closed_message', true );
    $response = array( 
      'status'  => 'error', 
      'message' => $store_closed_message
    );
    
    return $response;
  }
  
  if ( $cart_subtotal == 0 ) { 
    $response = array( 
      'status'  => 'error', 
      'message' => __( 'Your cart is empty!', 'food-store' )
    );
    
    return $response;
  }

  if ( wfs_is_service_enabled() && $min_order_amount > 0 && $cart_subtotal < $min_order_amount ) {
    
    $response = array( 
      'status'  => 'error',
      'message' => $error_message,
    );

    return $response;
  }

  $response = array( 'status' => 'success' );
  return $response;
}

/**
 * Get row price of particular addon with Tax Settings
 *
 * @access public
 * @return array
 */
function wfs_get_addon_raw_price( $addon_slug ) {
  
  $price = 0;

  if ( !empty( $addon_slug ) ) {
    $addon_data = get_term_by( 'slug', $addon_slug, 'product_addon' );

    if ( $addon_data ) {
      $addon_id   = $addon_data->term_id;
      $price      = get_term_meta( $addon_id, '_wfs_addon_item_price', true );
    }
  }
  return $price;
}

/**
 * Create a page and store the ID in an option.
 *
 * @param mixed  $slug Slug for the new page.
 * @param string $option Option name to store the page's ID.
 * @param string $page_title (default: '') Title for the new page.
 * @param string $page_content (default: '') Content for the new page.
 * @param int    $post_parent (default: 0) Parent for the new page.
 * @return int page ID.
 */
function wfs_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
  
  global $wpdb;

  $option_value = get_option( $option );

  if ( $option_value > 0 ) {
    $page_object = get_post( $option_value );

    if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
      // Valid page is already in place.
      return $page_object->ID;
    }
  }

  if ( strlen( $page_content ) > 0 ) {
    // Search for an existing page with the specified page content (typically a shortcode).
    $shortcode = str_replace( array( '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ), '', $page_content );
    $valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$shortcode}%" ) );
  } else {
    // Search for an existing page with the specified page slug.
    $valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
  }

  $valid_page_found = apply_filters( 'foodstore_create_page_id', $valid_page_found, $slug, $page_content );

  if ( $valid_page_found ) {
    if ( $option ) {
      update_option( $option, $valid_page_found );
    }
    return $valid_page_found;
  }

  // Search for a matching valid trashed page.
  if ( strlen( $page_content ) > 0 ) {
    // Search for an existing page with the specified page content (typically a shortcode).
    $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
  } else {
    // Search for an existing page with the specified page slug.
    $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
  }

  if ( $trashed_page_found ) {
    
    $page_id   = $trashed_page_found;
    $page_data = array(
      'ID'          => $page_id,
      'post_status' => 'publish',
    );
    wp_update_post( $page_data );
  
  } else {

    $page_data = array(
      'post_status'    => 'publish',
      'post_type'      => 'page',
      'post_author'    => 1,
      'post_name'      => $slug,
      'post_title'     => $page_title,
      'post_content'   => $page_content,
      'post_parent'    => $post_parent,
      'comment_status' => 'closed',
    );
    $page_id   = wp_insert_post( $page_data );
  }

  if ( $option ) {
    update_option( $option, $page_id );
  }

  return $page_id;
}

/**
 * Get default service date
 *
 * @return string service date
 */
function wfs_get_default_service_date() {
  
  $current_time = current_time( 'timestamp' );
  $service_date = date_i18n( 'Y-m-d', $current_time );
  $service_date = isset( $_COOKIE['service_date'] ) ? $_COOKIE['service_date'] : $service_date;
  
  return apply_filters( 'wfs_get_default_service_date', $service_date );
}

/**
 * Check if current page belongs to Food Store Items page
 *
 * @since 1.2.5
 * @return bool 
 */
function wfs_is_foodstore_page() {

  global $post, $wpdb;

  $shortcode_found = false;
  $pattern = get_shortcode_regex();

  if ( has_shortcode( $post->post_content, 'foodstore' ) ) {
    $shortcode_found = true;
  } else if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'foodstore', $matches[2] ) ) {
    $shortcode_found = true;
  } else if ( isset( $post->ID ) ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_value LIKE '%%my_shortcode%%'", $post->ID ) );
    $shortcode_found = ! empty( $result );
  }

  return apply_filters( 'wfs_is_foodstore_page', $shortcode_found );
}