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
?>

<!-- Food Store Modal -->
<div class="wfsmodal micromodal-slide" id="wfsServiceModal" aria-hidden="true">
  <div class="wfsmodal-dialog" tabindex="-1" data-micromodal-close>
    <div class="wfsmodal-container" role="dialog" aria-labelledby="wfsServiceModal-title">
      
      <header class="wfsmodal-header">
        <h5 class="wfsmodal-title" id="wfsServiceModal-title">
          <?php _e( 'Your Order Settings', 'food-store' ); ?>
        </h5>
        <button type="button" class="modal__close" aria-label="Close" data-micromodal-close></button>
      </header>

      <div class="wfsmodal-body" id="wfsServiceModal-content">
        <div class="wfs-service-modal-container">
          <?php wfs_get_template( 'services/service-types.php' ); ?>
          <button type="button" class="wfs-update-service" data-add-item=''>
            <?php _e( 'Update', 'food-store' ); ?>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>