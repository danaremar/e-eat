<?php
/**
 * The template for displaying product short description
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
global $post;

$short_description = apply_filters( 'wfs_short_description', $post->post_excerpt );

if ( ! $short_description ) {
  return;
}

?>
<div class="wfs-food-item-description">
  	<?php echo $short_description; ?>
</div>