<?php
/**
 * FoodStore Cart Totals
 *
 * This template can be overridden by copying it to yourtheme/food-store/cart/cart-totals.php
 *
 * @package     FoodStore/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
} ?>

<div class="wfs-cart-totals-container">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<div class="wfs-cart-totals-item">
		<div class="wfs-cart-totals-item-left"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></div>
		<div class="fs-text-right" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></div>
	</div>

	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<div class="wfs-cart-totals-item">
			<div class="wfs-cart-totals-item-left"><?php echo esc_html( $fee->name ); ?></div>
			<div class="fs-text-right" data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></div>
		</div>
	<?php endforeach; 

	if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
		
		$taxable_address = WC()->customer->get_taxable_address();
		$estimated_text  = '';

		if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
			/* translators: %s location. */
			$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
		}

		if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
			foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				?>
				<div class="wfs-cart-totals-item tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<div class="wfs-cart-totals-item-left"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div class="fs-text-right" data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></div>
				</div>
				<?php
			}
		
		} else { ?>

			<div class="wfs-cart-totals-item tax-total">
				<div class="wfs-cart-totals-item-left"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="fs-text-right" data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></div>
			</div>
			<?php
		}
	} ?>

	<div><hr></div>

	<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

	<div class="wfs-cart-totals-item order-total">
		<div class="wfs-cart-totals-item-left"><?php esc_html_e( 'Total', 'woocommerce' ); ?></div>
		<div class="fs-text-right" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></div>
	</div>

	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>