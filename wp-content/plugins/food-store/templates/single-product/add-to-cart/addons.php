<?php
/**
 * Product Addons
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/addons.php.
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

$product_id = $product->get_id();

$cart_product = !empty( $cart_key ) ? WC()->cart->get_cart_item( $cart_key ) : array();
$cart_addons = isset( $cart_product['addons'] ) ? $cart_product['addons'] : array();

$product_disable_notes = get_post_meta( $product_id, '_wfs_disable_instruction', true );
$product_disable_notes = ( $product_disable_notes == 'yes' ) ? true : false ;

$global_enable_notes = get_option( '_wfs_enable_special_note', true );
$global_enable_notes = ( $global_enable_notes == 'yes' ) ? true : false ;

if ( !empty( $product_id ) ) {

  $addon_categories = wp_get_post_terms( $product_id, 'product_addon' );

  $addon_child_cats = array();
  $category_name_slug = '';

  if ( !empty( $addon_categories ) && is_array( $addon_categories ) ) {

    echo '<div class="wfs-item-addons-container">';

    $var = '';
    $sort_addon_categories = wfs_sort_addon_categories( $addon_categories );

    foreach( $sort_addon_categories as $addon_category ) {

      if ( $addon_category->parent !== 0 ) {

        $parent_category  = get_term_by( 'id', $addon_category->parent , 'product_addon' );
        $parent_id        = $parent_category->term_id;

        $parent_category_slug = $parent_category->slug;
        $parent_category_name = $parent_category->name;

        $category_slug  = $addon_category->slug;
        $category_name  = $addon_category->name;

        $category_price = get_term_meta( $addon_category->term_id, '_wfs_addon_item_price', true );
        $category_price = $category_price != '' ? wfs_get_addon_price( $product, $category_price ) : '0.00';

        $class  = ( $var == $parent_category_name ) ? 'same' : '';
        $var    = $parent_category_name;

        $choice = wfs_get_term_choice( $parent_id );
        $field_name  = ( $choice == 'radio' ) ? $parent_category_slug : $category_slug;

        if ( $class != 'same' ) : ?>

          <h6 class="wfs-addon-category-title"><?php echo $parent_category_name; ?></h6>

        <?php endif;

        $check_addon_in_cart = wfs_check_addon_in_cart( $field_name, $category_slug, $cart_addons );
        $selected = $check_addon_in_cart ? 'checked' : '';

        ?>
        <div class="wfs-addon-category">
          <label for="<?php echo $category_slug; ?>">
            <input id="<?php echo $category_slug; ?>" name="<?php echo $field_name; ?>" <?php echo $selected; ?> type="<?php echo $choice; ?>" value="<?php echo $category_slug; ?>" data-attrs="<?php echo $addon_category->term_id . '|' . $category_price . '|' . $choice; ?>" >
            <span><?php echo $category_name; ?></span>
          </label>
          <span><?php echo apply_filters( 'wfs_popup_addon_price', '&nbsp;+&nbsp;' . wc_price( $category_price ), $addon_category->term_id, $category_price ) ?></span>
        </div><!-- wfs-addon-category -->
        <?php
      }
    }
    echo '</div><!-- /wfs-item-addons-container -->';

  }

  if ( ! $product_disable_notes && $global_enable_notes ) { ?>

    <div class="wfs-special-instruction-wrapper">
      <p class="wfs-special-note-label"><?php _e( 'Special Note', 'food-store' ); ?></p>
      <textarea id="special_note" name="special_note" rows="3" cols="10" placeholder="<?php echo apply_filters( 'wfs_special_note_placeholder', __( 'Special notes if any.. eg: Need extra sauce..', 'food-store' ) ); ?>"></textarea>
    </div>

  <?php }
}