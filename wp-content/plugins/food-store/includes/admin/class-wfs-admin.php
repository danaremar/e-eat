<?php
/**
 * FoodStore Admin
 *
 * @class    WFS_Admin
 * @package  FoodStore/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * WFS_Admin class.
 */
class WFS_Admin {

  /**
   * Constructor.
   */
  public function __construct() {
    add_action( 'init', array( $this, 'includes' ) );
    add_action( 'admin_init', array( $this, 'buffer' ), 1 );
    add_action( 'woocommerce_before_order_itemmeta', array( $this, 'wfs_admin_order_line_item' ), 10, 3 );
    add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'wfs_hide_special_note' ), 10 );
  }

  /**
   * Output buffering allows admin screens to make redirects later on.
   */
  public function buffer() {
    ob_start();
  }

  /**
   * Include any classes we need within admin.
   */
  public function includes() {
    include_once dirname( __FILE__ ) . '/wfs-admin-functions.php';
    include_once dirname( __FILE__ ) . '/class-wfs-admin-menus.php';
    include_once dirname( __FILE__ ) . '/class-wfs-admin-assets.php';
  }

  /**
   * Add addons as line items to WooCommerce Order
   *
   * @since 1.0.0
   * @param int $item_id
   * @param obj $item
   * @param obj $product
   *
   * @return array $item_data
   */
  public function wfs_admin_order_line_item( $item_id, $item, $product ) {

    $item_data = '';
    
    $addon_items = wfs_get_addons_from_meta( $item_id );

    if ( !empty( $addon_items ) && is_array( $addon_items ) ) {
      foreach( $addon_items as $key => $addon_item ) {
        $addon_name  = isset( $addon_item['name'] ) ? $addon_item['name'] : '';
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
   * Restric special note to be shown at some places
   *
   * @author WP Scripts
   * @since 1.1
   * @param array $hidden_items
   */
  public function wfs_hide_special_note( $hidden_items ) {

    array_push( $hidden_items, '_special_note' );
    return $hidden_items;
  }
}

return new WFS_Admin();