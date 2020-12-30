<?php
/**
 * Installation related functions and actions.
 *
 * @package FoodStore/Classes
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WFS_Install Class.
 */
class WFS_Install {

  /**
   * DB updates and callbacks that need to be run per version.
   *
   * @var array
   */
  private static $db_updates = array(
    '1.1.4' => array(
      'wfs_update_114_add_prefix',
      'wfs_update_114_reassign_deprecated_options',
    ),
  );

  /**
   * Init hooks for update
   */
  public static function init() {
    add_action( 'init', array( __CLASS__, 'update' ), 5 );
  }

  /**
   * Install WFS.
   */
  public static function install() {
    
    if ( ! is_blog_installed() ) {
      return;
    }

    self::create_options();
    self::create_pages();

    do_action( 'foodstore_installed' );
  }

  /**
   * Default options.
   *
   * Sets up the default options used on the settings page.
   */
  private static function create_options() {

    $settings = WFS_Admin_Settings::get_settings_pages();

    foreach ( $settings as $section ) {
      if ( ! method_exists( $section, 'get_settings' ) ) {
        continue;
      }
      $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

      foreach ( $subsections as $subsection ) {
        foreach ( $section->get_settings( $subsection ) as $value ) {
          if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
            $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
            add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
          }
        }
      }
    }
  }

  /**
   * Create pages that the plugin relies on, storing page IDs in variables.
   */
  public static function create_pages() {
    
    $pages = apply_filters(
      'foodstore_create_pages',
      array(
        'order_online'      => array(
          'name'    => _x( 'Order Online', 'Page slug', 'food-store' ),
          'title'   => _x( 'Order Online', 'Page title', 'food-store' ),
          'content' => '[foodstore]',
        ),
      )
    );

    foreach ( $pages as $key => $page ) {
      wfs_create_page( esc_sql( $page['name'] ), 'foodstore_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? wfs_get_page_id( $page['parent'] ) : '' );
    }
  }

  /**
   * Get list of DB update callbacks.
   *
   * @since  1.4.4
   * @return array
   */
  public static function get_db_update_callbacks() {
    return self::$db_updates;
  }

  /**
   * Update plugin with its db version
   */
  public static function update() {
    
    $current_db_version = get_option( 'foodstore_db_version', '1.0.0' );
    
    if ( version_compare( WFS()->version , $current_db_version, '=' ) ) {
      return;
    }

    foreach ( self::get_db_update_callbacks() as $version => $update_callbacks) {
      if ( version_compare( $current_db_version, $version, '<' ) ) {
        foreach ( $update_callbacks as $update_callback ) {
          call_user_func( $update_callback );
        }
      }
    }
    self::update_db_version();
    self::update_wfs_version();
  }

  /**
   * Update DB version to current.
   *
   * @param string|null $version FoodStore DB version or null.
   */
  public static function update_db_version( $version = null ) {
    delete_option( 'foodstore_db_version' );
    add_option( 'foodstore_db_version', is_null( $version ) ? WFS()->version : $version );
  }


  /**
   * Update WFS version to current.
   */
  private static function update_wfs_version() {
    delete_option( 'foodstore_version' );
    add_option( 'foodstore_version', WFS()->version );
  }

}

WFS_Install::init();