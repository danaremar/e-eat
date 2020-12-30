<?php
/**
 *
 * @author      WP Scripts <@wpscripts>
 * @package 	FoodStore
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Food Store - Online Food Delivery & Pickup
 * Description: Food Store is complete online food ordering platform with all your favourite WooCommerce functionalities.
 * Version: 1.3.3
 * Author: WP Scripts
 * Text Domain: food-store
 * Domain Path: /languages/
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.8
 *
 * Copyright: 2020 Automatic | 2020 WP Scripts
 * License: GPL v2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( 'ABSPATH' ) || exit;

// Define FOOD_STORE_PLUGIN_FILE.
if ( ! defined( 'WFS_PLUGIN_FILE' ) ) {
  define( 'WFS_PLUGIN_FILE', __FILE__ );
}

// include dependencies file
if ( ! class_exists( 'WFS_Dependencies' ) ) {
  include_once dirname( __FILE__) . '/includes/class-food-store-dependencies.php';
}

// Include the main FoodStore class.
if ( ! class_exists( 'FoodStore', false ) ) {
  include_once dirname( WFS_PLUGIN_FILE ) . '/includes/class-food-store.php';
}

/**
 * Returns the main instance of WFC.
 *
 * @since  1.0
 * @return FoodStore
 */
function WFS() {
  return FoodStore::instance();
}

// Global for backwards compatibility.
$GLOBALS['food-store'] = WFS();