<?php
/**
 * The template for displaying foodstore search bar
 *
 * This template can be overridden by copying it to yourtheme/food-store/content-search.php.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>
<!-- Search bar start -->
<div class="wfs-search-container">
<input class="wfs-food-search" type="text" placeholder="<?php echo apply_filters( 'wfs_search_items_placeholder', __( 'Search dishes..', 'food-store') ); ?>" name="wfs-search">
<i class="wfs-icon-search"></i>
</div>
<!-- Search bar end -->