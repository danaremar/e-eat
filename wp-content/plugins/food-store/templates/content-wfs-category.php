<?php
/**
 * The template for displaying foodstore category within loops
 *
 * This template can be overridden by copying it to yourtheme/food-store/content-wfs_cat.php.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

<li class="wfs-category-menu-li <?php echo $category->slug ?>">
  
  <?php
  
  /**
   * wfs_before_subcategory hook.
   *
   */
  do_action( 'wfs_before_subcategory', $category );

  /**
   * wfs_before_subcategory_title hook.
   */
  do_action( 'wfs_before_subcategory_title', $category );

  /**
   * wfs_shop_loop_subcategory_title hook.
   */
  do_action( 'wfs_subcategory_title', $category );

  /**
   * wfs_after_subcategory_title hook.
   */
  do_action( 'wfs_after_subcategory_title', $category );

  /**
   * wfs_after_subcategory hook.
   */
  do_action( 'wfs_after_subcategory', $category );
  
  ?>

</li>