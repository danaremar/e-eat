<?php
/**
 * FoodStore Styling Settings
 *
 * @package FoodStore/Admin
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WFS_Settings_Styling', false ) ) {
  return new WFS_Settings_Styling();
}

/**
 * WFS_Settings_Styling.
 */
class WFS_Settings_Styling extends WFS_Settings_Page {

  /**
   * Constructor.
   */
  public function __construct() {
    
    $this->id    = 'styling';
    $this->label = __( 'Layout & Styling', 'food-store' );

    parent::__construct();
  }

  /**
   * Get sections.
   *
   * @return array
   */
  public function get_sections() {
    
    $sections = array(
      ''           => __( 'Layouts', 'food-store' ),
      'stylesheet' => __( 'Customize', 'food-store' ),
    );
    return apply_filters( 'foodstore_get_sections_' . $this->id, $sections );
  }

  /**
   * Get settings array.
   *
   * @param string $current_section Current section name.
   * @return array
   */
  public function get_settings( $current_section = '' ) {

    if ( 'stylesheet' === $current_section ) {

      $settings = apply_filters(
        
        'foodstore_styling_settings',
        
        array(

          array(
            'title'     => __( 'Styling Settings', 'food-store' ),
            'type'      => 'title',
            'id'        => 'styling_options',
          ),

          array(
            'title'     => __( 'Primary Color Scheme', 'food-store' ),
            'desc'      => __( 'Selected color will be used for Links, Buttons and Active Classes etc.', 'food-store' ),
            'id'        => '_wfs_primary_color_scheme',
            'default'   => '#267dc9',
            'type'      => 'text',
            'class'     => 'wfs-colorpicker',
          ),

          array(
            'title'     => __( 'Secondary Color Scheme', 'food-store' ),
            'desc'      => __( 'This will be mostly used with Button/Link Hovers, Borders etc.', 'food-store' ),
            'id'        => '_wfs_secondary_color_scheme',
            'default'   => '#267dc9',
            'type'      => 'text',
            'class'     => 'wfs-colorpicker',
          ),

          array(
            'title'     => __( 'Add your own CSS', 'food-store' ),
            'desc_tip'  => __( 'Add your css to override any styling option given by theme or plugin as per your need.', 'food-store' ),
            'id'        => '_wfs_user_stylesheet',
            'placeholder'   => 'Add your stylesheet here..',
            'type'      => 'textarea',
            'css'       => 'min-width: 50%; height: 120px;',
          ),
          
          array(
            'type'      => 'sectionend',
            'id'        => 'styling_options',
          ),
        )
      );

    } else {

      $settings = apply_filters(
        
        'foodstore_layout_settings',
        
        array(

          array(
            'title'     => __( 'Layout Settings', 'food-store' ),
            'type'      => 'title',
            'id'        => 'layout_options',
          ),

          array(
            'title'     => __( 'Number of Columns', 'food-store' ),
            'desc'      => __( 'Please select number of columns for Food Items. Suitable for desktop view.', 'food-store' ),
            'id'        => '_wfs_listing_column_count',
            'default'   => '1',
            'type'      => 'select',
            'options'   => array(
              '1'       => __( 'One', 'food-store' ),
              '2'       => __( 'Two', 'food-store' ),
            ),
            'desc_tip'  => true,
            'class'     => 'wc-enhanced-select',
          ),

          array(
            'title'     => __( 'Hide Bottom Cart', 'food-store' ),
            'desc'      => __( 'Disable bottom cart on items page.', 'food-store' ),
            'id'        => '_wfs_listing_hide_cart_area',
            'default'   => 'no',
            'type'      => 'checkbox',
          ),
          
          array(
            'type'      => 'sectionend',
            'id'        => 'layout_options',
          ),

          array(
            'title'     => __( 'Usability Tweaks', 'food-store' ),
            'type'      => 'title',
            'id'        => 'usability_options',
          ),

          array(
            'title'     => __( 'Sticky Categories', 'food-store' ),
            'desc'      => __( 'Make the categories area stick to its position while scrolling.', 'food-store' ),
            'id'        => '_wfs_listing_sidebar_is_sticky',
            'default'   => 'yes',
            'type'      => 'checkbox',
          ),

          array(
            'title'     => __( 'Show Items Count', 'food-store' ),
            'desc'      => __( 'Displays the number of items available for the category.', 'food-store' ),
            'id'        => '_wfs_listing_show_sidebar_count',
            'default'   => 'no',
            'type'      => 'checkbox',
          ),

          array(
            'title'     => __( 'Food Item Image', 'food-store' ),
            'desc'      => __( 'Please select how would you like to show the item images on frontend.', 'food-store' ),
            'id'        => '_wfs_listing_item_image_display',
            'default'   => 'medium',
            'type'      => 'select',
            'options'   => array(
              'medium'    => __( 'Medium Image', 'food-store' ),
              'small'     => __( 'Small Image', 'food-store' ),
              'hide'      => __( 'Hide Image Completely', 'food-store' ),
            ),
            'desc_tip'  => true,
            'class'     => 'wc-enhanced-select',
          ),

          array(
            'title'     => __( 'Enable Lazy Load', 'food-store' ),
            'desc'      => __( 'Item image will be loaded once you scroll to it. Loads your items page faster.', 'food-store' ),
            'id'        => '_wfs_enable_lazy_loading',
            'default'   => 'no',
            'type'      => 'checkbox',
          ),

          array(
            'title'     => __( 'Enable Popup from Title', 'food-store' ),
            'desc'      => __( 'If enabled popup can be opened by clicking on item title or image.', 'food-store' ),
            'id'        => '_wfs_enable_title_popup',
            'default'   => 'no',
            'type'      => 'checkbox',
          ),

          array(
            'title'     => __( 'Show Image in Popup', 'food-store' ),
            'desc'      => __( 'Display item image/gallery in popup.', 'food-store' ),
            'id'        => '_wfs_popup_enable_image',
            'default'   => 'yes',
            'type'      => 'checkbox',
          ),

          array(
            'type'      => 'sectionend',
            'id'        => 'usability_options',
          ),
        )
      );
    }

    return apply_filters( 'foodstore_get_settings_' . $this->id, $settings, $current_section );
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

return new WFS_Settings_Styling();