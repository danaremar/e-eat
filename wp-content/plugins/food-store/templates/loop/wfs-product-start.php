<?php
/**
 * FoodStore Product Start
 *
 * This template can be overridden by copying it to yourtheme/food-store/wfs-product-start.php.
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Apply Filter to make the Food item area width flexible
$classes = apply_filters( 'wfs_products_start_classes', '' );

?>

<div id="wfs-food-items" class="<?php echo $classes; ?>">