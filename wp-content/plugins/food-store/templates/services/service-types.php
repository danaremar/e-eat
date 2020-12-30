<?php
/**
 * The template for displaying product popup wfsmodal
 *
 * This template can be overridden by copying it to yourtheme/food-store
 *
 * @package FoodStore/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$enable_delivery = ( get_option( '_wfs_enable_delivery' ) == 'yes' ) ? true : false;
$enable_pickup   = ( get_option( '_wfs_enable_pickup' ) == 'yes' ) ? true : false ;

?>

<?php apply_filters( 'wfs_service_notice_area', '' ); ?>

<?php if ( $enable_delivery && $enable_pickup ) : ?>

<ul class="nav nav-tabs" id="wfsTab" role="tablist">

  <li class="nav-item">
    <a class="nav-link active" id="pickup-tab" data-toggle="tab" href="#pickup" role="tab" aria-controls="pickup" aria-selected="true">
      <?php echo wfs_get_service_label('pickup'); ?>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" id="delivery-tab" data-toggle="tab" href="#delivery" role="tab" aria-controls="delivery" aria-selected="false">
      <?php echo wfs_get_service_label('delivery'); ?>
    </a>
  </li>

</ul>
<?php endif; ?>

<!-- Message area for service related errors -->
<div class="foodstore_service_error inactive"></div>

<div class="tab-content">

  <?php if ( $enable_pickup ) : ?>
    
    <div class="tab-pane active" data-service-type="pickup" id="pickup" role="tabpanel" aria-labelledby="pickup-tab">

      <?php do_action( 'foodstore_before_service_hours', 'pickup' ); ?>
      
      <div class="wfs-service-time-wrapper">
        <?php 
        /* translators: %1s: get service label */
        printf( __( 'Select %1s Time', 'food-store' ), wfs_get_service_label('pickup') );
        wfs_render_service_hours( 'pickup' ); 
        ?>
      </div>

    </div>

  <?php endif; ?>

  <?php if ( $enable_delivery ) : ?>

    <div class="tab-pane <?php if(!$enable_pickup) echo 'active'; ?>" data-service-type="delivery" id="delivery" role="tabpanel" aria-labelledby="delivery-tab">

      <?php do_action( 'foodstore_before_service_hours', 'delivery' ); ?>
      
      <div class="wfs-service-time-wrapper">
        <?php
        /* translators: %1s: get service label */
        printf( __( 'Select %1s Time', 'food-store' ), wfs_get_service_label('delivery') );
        wfs_render_service_hours( 'delivery' ); 
        ?>
      </div>

    </div>

  <?php endif; ?>

</div>