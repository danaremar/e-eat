<?php
/**
 * FoodStore Category Start
 *
 * This template can be overridden by copying it to yourtheme/food-store/wfs-category-start.php.
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Apply Filter to make the Food item area width flexible
$classes = apply_filters( 'wfs_sidebar_start_classes', '' );

?>

<div id="wfs-sticky-sidebar" class="<?php echo $classes; ?>">
  <div class="wfs-sidebar-menu">
    <ul>