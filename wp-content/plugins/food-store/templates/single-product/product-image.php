<?php
/**
 * The template for displaying product image
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $product;

$post_thumbnail_id 	= $product->get_image_id();
$thumbnail_src      = wp_get_attachment_image_src( $post_thumbnail_id, 'woocommerce_thumbnail' );
$thumbnail_src 		= isset( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';
$product_food_type  = get_post_meta( $product->get_id(), '_wfs_food_item_type', true );

$image_option		= get_option('_wfs_listing_item_image_display');
if( 'small' === $image_option ) {
	$height_n_width = '65';
} else if( 'medium' === $image_option ) {
	$height_n_width = '105';
}
?>

<?php if( $image_option !== 'hide' ) : ?>

	<div class="wfs-food-item-image-container">
		<?php if( !empty( $product_food_type ) ) : ?>
			<div class="wfs-food-item-type <?php echo $product_food_type; ?>">
		        <div></div>
		    </div>
		<?php endif; ?>

		<?php if ( $thumbnail_src ) : ?>

			<?php 

			$lazy_loading = get_option( '_wfs_enable_lazy_loading', 'yes' );
			if( 'yes' === $lazy_loading ) {
				$lazy_load_class = 'wfs-lazy-load';
				$image_attr = 'data-src="' . $thumbnail_src . '"';
			} else {
				$lazy_load_class = '';
				$image_attr = 'src="' . $thumbnail_src . '"';
			}

			?>
	  		
	  		<img class="<?php echo $lazy_load_class; ?>" alt="<?php echo $product->get_name(); ?>" <?php echo $image_attr; ?> style="width: <?php echo $height_n_width . 'px'; ?>;" />
	  	
	  	<?php endif; ?>
	</div>
<?php endif; ?>