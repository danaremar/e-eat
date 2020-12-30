<?php
/**
 * FoodStore Page Functions
 *
 * Functions related to pages and menus.
 *
 * @package  FoodStore\Functions
 * @version  1.1.4
 */

defined( 'ABSPATH' ) || exit;


/**
 * Retrieve page ids - returns -1 if no page is found.
 *
 * @param string $page Page slug.
 * @return int
 */
function wfs_get_page_id( $page ) {
  
  $page = apply_filters( 'foodstore_get_' . $page . '_page_id', get_option( 'foodstore_' . $page . '_page_id' ) );

  return $page ? absint( $page ) : -1;
}

/**
 * Retrieve page permalink.
 *
 * @param string      $page page slug.
 * @param string|bool $fallback Fallback URL if page is not set. Defaults to home URL.
 * @return string
 */
function wfs_get_page_permalink( $page, $fallback = null ) {
  $page_id   = wfs_get_page_id( $page );
  $permalink = 0 < $page_id ? get_permalink( $page_id ) : '';

  if ( ! $permalink ) {
    $permalink = is_null( $fallback ) ? get_home_url() : $fallback;
  }

  return apply_filters( 'foodstore_get_' . $page . '_page_permalink', $permalink );
}