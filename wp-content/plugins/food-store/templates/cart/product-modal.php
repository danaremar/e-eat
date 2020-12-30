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
<div class="wfsmodal micromodal-slide" id="wfsModal" aria-hidden="true">
  <div class="wfsmodal-dialog" tabindex="-1" data-micromodal-close>
    <div class="wfsmodal-container" role="dialog" aria-labelledby="wfsModal-title">
      
      <header class="wfsmodal-header">
        <h5 class="wfsmodal-title" id="wfsModal-title"></h5>
        <button type="button" class="modal__close" aria-label="Close" data-micromodal-close></button>
      </header>

      <div class="wfsmodal-body" id="wfsModal-content"></div>

      <footer class="wfsmodal-footer">
        <div class="wfs-modal-actions">
          
          <div class="wfs-modal-count">
            
            <div class="wfs-modal-minus">
              <input type="button" value="-" class="wfs-qty-btn wfs-qtyminus">
            </div>

            <div class="wfs-modal-quantity">
              <input type="text" name="wfs-quantity" value="1" class="wfs-qty-input">
            </div>

            <div class="wfs-modal-plus">
              <input type="button" value="+" class="wfs-qty-btn wfs-qtyplus">
            </div>
          </div>

          <div class="wfs-modal-add-to-cart">
            <a data-item-qty="" data-product-id="" data-product-type="" data-variation-id="" data-cart-action="" data-item-key="" class="wfs-product-add-to-cart">
              <span class="wfs-cart-action-text"></span>
              <span class="wfs-live-item-price"></span>
            </a>
          </div>
        </div>
      </footer>

    </div>
  </div>
</div>