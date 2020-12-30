<?php
/**
 * Booster for WooCommerce Invoice
 *
 * @version 5.3.6
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Invoice' ) ) :

class WCJ_Invoice {

	public $order_id;
	public $invoice_type;

	/**
	 * Constructor.
	 */
	function __construct( $order_id, $invoice_type ) {
		$this->order_id     = $order_id;
		$this->invoice_type = $invoice_type;
	}

	/**
	 * is_created.
	 */
	function is_created() {
		return ( '' != get_post_meta( $this->order_id, '_wcj_invoicing_' . $this->invoice_type . '_date', true ) );
	}

	/**
	 * delete.
	 *
	 * @version 5.3.6
	 * @todo    removed update_option( $option_name, ( $the_invoice_counter - 1 ) ); It was causing the issue on the count.
	 */
	function delete() {
		update_post_meta( $this->order_id, '_wcj_invoicing_' . $this->invoice_type . '_number_id', 0 );
		update_post_meta( $this->order_id, '_wcj_invoicing_' . $this->invoice_type . '_date', '' );
		if ( 'yes' === wcj_get_option( 'wcj_invoicing_' . $this->invoice_type . '_sequential_enabled', 'no' ) ) {
			$option_name = 'wcj_invoicing_' . $this->invoice_type . '_numbering_counter';
			$the_invoice_counter = wcj_get_option( $option_name, 1 );
		}
	}

	/**
	 * create.
	 *
	 * @version 5.3.6
	 * @todo    use mysql transaction enabled (as in "wcj_order_number_use_mysql_transaction_enabled")
	 * @todo    used get_option instead wcj_get_option to get current numbering_counter. 
	 */
	function create( $date = '' ) {
		$order_id = $this->order_id;
		$invoice_type = $this->invoice_type;
		if ( 'yes' === wcj_get_option( 'wcj_invoicing_' . $invoice_type . '_skip_zero_total', 'no' ) ) {
			$_order = wc_get_order( $order_id );
			if ( 0 == $_order->get_total() ) {
				return;
			}
		}
		if ( 'yes' === wcj_get_option( 'wcj_invoicing_' . $invoice_type . '_sequential_enabled', 'no' ) ) {
			$the_invoice_number = get_option( 'wcj_invoicing_' . $invoice_type . '_numbering_counter', 1 );
			update_option( 'wcj_invoicing_' . $invoice_type . '_numbering_counter', ( $the_invoice_number + 1 ) );
		} else {
			$the_invoice_number = $order_id;
		}
		$the_date = ( '' == $date ) ? current_time( 'timestamp' ) : $date;
		update_post_meta( $order_id, '_wcj_invoicing_' . $invoice_type . '_number_id', $the_invoice_number );
		update_post_meta( $order_id, '_wcj_invoicing_' . $invoice_type . '_date', $the_date );
	}

	/**
	 * get_file_name.
	 *
	 * @version 3.5.0
	 */
	function get_file_name() {
		$_file_name = sanitize_file_name( do_shortcode( wcj_get_option( 'wcj_invoicing_' . $this->invoice_type . '_file_name', '' ) ) );
		if ( '' === $_file_name ) {
			$_file_name = $this->invoice_type . '-' . $this->order_id;
		}
		return apply_filters( 'wcj_get_' . $this->invoice_type . '_file_name', $_file_name . '.pdf', $this->order_id );
	}

	/**
	 * get_invoice_date.
	 */
	function get_invoice_date() {
		$the_date = get_post_meta( $this->order_id, '_wcj_invoicing_' . $this->invoice_type . '_date', true );
		return apply_filters( 'wcj_get_' . $this->invoice_type . '_date', $the_date, $this->order_id );
	}

	/**
	 * get_invoice_number.
	 *
	 * @version 3.2.2
	 */
	function get_invoice_number() {
		$replaced_values = array(
			'%prefix%'  => wcj_get_option( 'wcj_invoicing_' . $this->invoice_type . '_numbering_prefix', '' ),
			'%counter%' => sprintf( '%0' . wcj_get_option( 'wcj_invoicing_' . $this->invoice_type . '_numbering_counter_width', 0 ) . 'd',
				get_post_meta( $this->order_id, '_wcj_invoicing_' . $this->invoice_type . '_number_id', true ) ),
			'%suffix%'  => wcj_get_option( 'wcj_invoicing_' . $this->invoice_type . '_numbering_suffix', '' ),
		);
		return apply_filters( 'wcj_get_' . $this->invoice_type . '_number',
			do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values,
				get_option( 'wcj_invoicing_' . $this->invoice_type . '_numbering_template', '%prefix%%counter%%suffix%' ) ) ),
			$this->order_id
		);
	}

}

endif;
