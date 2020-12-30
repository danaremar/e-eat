<?php
/**
 * Taxonomies
 *
 * Registers taxonomies.
 *
 * @package FoodStore/Classes
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomies Class.
 */
class WFS_Taxonomies {

  /**
   * Hook in methods.
   */
  public static function init() {
    add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
  }

  /**
   * Register core taxonomies.
   */
  public static function register_taxonomies() {

    if ( ! is_blog_installed() ) {
      return;
    }
    
    do_action( 'wfs_register_taxonomy' );

    /**
     * Add taxonomy for Product Addon 
     *
     * @since 1.0.0
     * @return void
     */
    $labels = array(
      'name'                       => _x( 'Addons', 'Taxonomy General Name', 'food-store' ),
      'singular_name'              => _x( 'Addon', 'Taxonomy Singular Name', 'food-store' ),
      'menu_name'                  => __( 'Addons', 'food-store' ),
      'all_items'                  => __( 'All Addons', 'food-store' ),
      'parent_item'                => __( 'Parent Addon', 'food-store' ),
      'parent_item_colon'          => __( 'Parent Addon:', 'food-store' ),
      'new_item_name'              => __( 'New Addon Name', 'food-store' ),
      'add_new_item'               => __( 'Add New Addon', 'food-store' ),
      'edit_item'                  => __( 'Edit Addon', 'food-store' ),
      'update_item'                => __( 'Update Addon', 'food-store' ),
      'view_item'                  => __( 'View Addon', 'food-store' ),
      'separate_items_with_commas' => __( 'Separate addons with commas', 'food-store' ),
      'add_or_remove_items'        => __( 'Add or remove addons', 'food-store' ),
      'choose_from_most_used'      => __( 'Choose from the most used', 'food-store' ),
      'popular_items'              => __( 'Popular Addons', 'food-store' ),
      'search_items'               => __( 'Search Addons', 'food-store' ),
      'not_found'                  => __( 'Not Found', 'food-store' ),
      'no_terms'                   => __( 'No addons', 'food-store' ),
      'items_list'                 => __( 'Addons list', 'food-store' ),
      'items_list_navigation'      => __( 'Addons list navigation', 'food-store' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => false,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => true,
    );
    register_taxonomy( 'product_addon', array( 'product' ), $args );

    do_action( 'wfs_after_register_taxonomy' );
  }
}

WFS_Taxonomies::init();