<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/food-store/single-product/price.php.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $product;

?>
<p class="<?php echo esc_attr( apply_filters( 'wfs-food-item-price', 'price' ) ); ?>"><?php echo $product->get_price_html(); ?></p>