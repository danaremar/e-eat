<?php
/**
 * FoodStore Admin Functions
 *
 * @package  FoodStore/Admin/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Display a FoodOrder help tip.
 *
 * @since  1.0.0
 *
 * @param  string $tip  Help tip text.
 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
 * @return string
 */
function wfs_help_tip( $tip, $allow_html = false ) {
  $tip = esc_attr( $tip );
  return '<span class="foodstore-help-tip" data-tip="' . $tip . '"></span>';
}