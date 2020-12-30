<?php
/**
 * FoodStore Advanced Settings
 *
 * @package FoodStore/Admin
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WFS_Settings_Advanced', false ) ) {
  return new WFS_Settings_Advanced();
}

/**
 * WFS_Settings_Advanced.
 */
class WFS_Settings_Advanced extends WFS_Settings_Page {

  /**
   * Constructor.
   */
  public function __construct() {
    
    $this->id    = 'advanced';
    $this->label = __( 'Advanced', 'food-store' );

    parent::__construct();
  }

  /**
   * Get sections.
   *
   * @return array
   */
  public function get_sections() {
    
    $sections = array(
      '' => __( 'Advanced', 'food-store' ),
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

    $settings = apply_filters(
      
      'foodstore_advanced_settings',
      
      array(

        array(
          'title'     => __( 'Advanced Settings', 'food-store' ),
          'type'      => 'title',
          'id'        => 'advanced_options',
        ),

        array(
          'title'     => __( 'Other Product Types', 'food-store' ),
          'desc'      => __( 'Keep other product options like <i>Grouped</i>, <i>External</i>, <i>Virtual</i> etc.', 'food-store' ),
          'id'        => '_wfs_adv_keep_other_product_types',
          'default'   => 'no',
          'type'      => 'checkbox',
        ),

        array(
          'title'     => __( 'Purge Settings !!', 'food-store' ),
          'desc'      => __( 'Remove Food Store data when plugin is deactivated.', 'food-store' ),
          'id'        => '_wfs_adv_remove_data_on_uninstall',
          'default'   => 'no',
          'type'      => 'checkbox',
        ),
        
        array(
          'type'      => 'sectionend',
          'id'        => 'advanced_options',
        ),
      )
    );

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

return new WFS_Settings_Advanced();