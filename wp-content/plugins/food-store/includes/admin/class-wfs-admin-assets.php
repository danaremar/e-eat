<?php
/**
 * Load assets
 *
 * @package FoodStore/Admin
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'WFS_Admin_Assets', false ) ) :

  /**
   * WFS_Admin_Assets Class.
   */
  class WFS_Admin_Assets {

    /**
     * Hook in tabs.
     */
    public function __construct() {
      add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen') );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    /**
     * Add settings page Screen ID to WooCommerce screen IDs
     */
    public function woocommerce_screen( $screens ) {
      $screens[] = 'toplevel_page_wfs-settings';
      return $screens;
    }

    /**
     * Enqueue styles.
     */
    public function admin_styles() {
      
      global $wp_scripts;

      $screen    = get_current_screen();
      $screen_id = $screen ? $screen->id : '';

      $version = defined( 'WFS_VERSION' ) ? WFS_VERSION : '1.0';

      // Register admin styles.
      wp_register_style( 'wfs_admin_menu_styles', WFS()->plugin_url() . '/assets/css/menu.css', array(), $version );
      wp_register_style( 'jquery_timepicker', WFS()->plugin_url() . '/assets/css/jquery.timepicker.css', array(), $version );
      wp_register_style( 'wfs_admin_styles', WFS()->plugin_url() . '/assets/css/admin.css', array(), $version );

      // Sitewide menu CSS.
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_style( 'jquery_timepicker' );
      wp_enqueue_style( 'wfs_admin_menu_styles' );
      wp_enqueue_style( 'wfs_admin_styles' );
    }

    /**
     * Enqueue scripts.
     */
    public function admin_scripts() {
      
      global $wp_query, $post;

      $version = defined( 'WFS_VERSION' ) ? WFS_VERSION : '1.0';

      $screen       = get_current_screen();
      $screen_id    = $screen ? $screen->id : '';
      $wc_screen_id = sanitize_title( __( 'FoodStore', 'food-store' ) );

      wp_register_script( 'jquery-timepicker', WFS()->plugin_url() . '/assets/js/admin/jquery.timepicker.js', array( 'jquery' ), '1.11.14', true );
      wp_register_script( 'jquery-tiptip', WFS()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), '1.0.0', true );
      wp_register_script( 'wfs-admin', WFS()->plugin_url() . '/assets/js/admin/foodstore-admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'jquery-timepicker', 'wp-color-picker' ), $version );

      if( $screen_id == 'toplevel_page_wfs-settings' ) {
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-timepicker' );
        wp_enqueue_script( 'jquery-tiptip' );
        wp_enqueue_script( 'wfs-admin' );
      }
    }
  }

endif;

return new WFS_Admin_Assets();