<?php
/**
 * The template for displaying empty cart with message
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$empty_cart_image = plugins_url( 'assets/images/empty-cart.svg', WFS_PLUGIN_FILE );

?>

<div class="wfs-empty-cart-container">
	<div class="wfs-empty-cart-image">
		<img src="<?php echo apply_filters( 'wfs_empty_cart_image', $empty_cart_image ); ?>">
	</div>
	<div class="wfs-empty-cart-text">
		<?php echo wfs_empty_cart_message(); ?>
	</div>
</div>