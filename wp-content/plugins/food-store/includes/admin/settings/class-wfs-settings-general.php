<?php
/**
 * FoodStore General Settings
 *
 * @package FoodStore/Admin
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WFS_Settings_General', false ) ) {
  return new WFS_Settings_General();
}

/**
 * WFS_Settings_General.
 */
class WFS_Settings_General extends WFS_Settings_Page {

  /**
   * Constructor.
   */
  public function __construct() {
    
    $this->id     = 'general';
    $this->label  = __( 'General', 'food-store' );

    parent::__construct();
  }

  /**
   * Get sections.
   *
   * @return array
   */
  public function get_sections() {
    
    $sections = array(
      '' => __( 'General', 'food-store' ),
    );
    return apply_filters( 'foodstore_get_sections_' . $this->id, $sections );
  }

  /**
   * Get settings array.
   *
   * @return array
   */
  public function get_settings( $current_section = '' ) {

    $settings = apply_filters(

      'foodstore_general_settings',
      
      array(

        array(
          'title'   => __( 'General Options', 'food-store' ),
          'type'    => 'title',
          'desc'    => $this->wfs_usage_intro(),
          'id'      => 'general_options',
        ),

        array(
          'title'   => __( 'Enable Catalog Mode', 'food-store' ),
          'desc'    => __( 'Present your Restaurant Menu without the Add to Cart button.', 'food-store' ),
          'id'      => '_wfs_catalog_mode',
          'default' => 'no',
          'type'    => 'checkbox',
        ),

        array(
          'title'     => __( 'Exclude Categories', 'food-store' ),
          'desc'      => __( 'This would exclude the categories from the frontend categories menu and items under those.', 'food-store' ),
          'id'        => '_wfs_exclude_categories',
          'type'      => 'multiselect',
          'desc_tip'  =>  true,
          'options'   => $this->wfs_get_wc_categories(),
          'class'     => 'wc-enhanced-select',
        ),

        array(
          'title'   => __( 'Enable Special Instructions', 'food-store' ),
          'desc'    => __( 'Allow customers to add notes for each item during order.', 'food-store' ),
          'id'      => '_wfs_enable_special_note',
          'default' => 'yes',
          'type'    => 'checkbox',
        ),

        array(
          'title'     => __( 'Purchase Button Redirect', 'food-store' ),
          'desc'      => __( 'Redirect page when customer clicks on Purchase from Food Store Cart.', 'food-store' ),
          'id'        => '_wfs_purchase_redirect',
          'type'      => 'select',
          'desc_tip'  =>  true,
          'options'   => array(
            'cart'      => __( 'Cart', 'food-store' ),
            'checkout'  => __( 'Checkout', 'food-store' ),
          ),
          'class'     => 'wc-enhanced-select',
        ),

        array(
          'type' => 'sectionend',
          'id'   => 'general_options',
        ),

      )
    );

    return apply_filters( 'foodstore_get_settings_' . $this->id, $settings, $current_section );
  }

  /**
   * Usage detailed description 
   *
   * @since 1.0.0
   */
  public function wfs_usage_intro() {
    if( get_option( 'foodstore_order_online_page_id' ) ) {
      $page_id = get_option( 'foodstore_order_online_page_id' );
      $page_title = get_the_title( $page_id );
      $page_permalink = esc_attr( esc_url( get_page_link( $page_id ) ) );
      /* translators: %1$s: get page permalink */
      /* translators: %2$s: get page title */
      return sprintf( __( 'Check <a target="_blank" href="%1$s">%2$s</a> page on frontend or use shortcode <b><i>[foodstore]</i></b> to create custom shop page for your food items.', 'food-store' ), $page_permalink, $page_title );
    } else {
      return __( 'Use shortcode <b><i>[foodstore]</i></b> in your custom shop page to list your food items.', 'food-store' );
    }
  }

  /**
   * List out the WooCommerce Categories for multiselect option
   *
   * @return array categories 
   */
  public function wfs_get_wc_categories() {
    
    $terms = get_terms( array(
      'taxonomy' => 'product_cat',
      'hide_empty' => false,
      'fields' => 'id=>name'
    ));

    if( !is_wp_error( $terms ) ) 
      return $terms;
    else
      return array('Select Categories');
  }

  /**
   * Output the settings.
   */
  public function output() {
    
    global $current_section;

    $settings = $this->get_settings( $current_section );
    WFS_Admin_Settings::output_fields( $settings );
  }

  /**
   * Save settings.
   */
  public function save() {
    
    global $current_section;

    $settings = $this->get_settings( $current_section );
    WFS_Admin_Settings::save_fields( $settings );

    if ( $current_section ) {
      do_action( 'foodstore_update_options_' . $this->id . '_' . $current_section );
    }
  }
}

return new WFS_Settings_General();