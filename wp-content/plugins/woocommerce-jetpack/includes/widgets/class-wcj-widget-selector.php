<?php
/**
 * Booster for WooCommerce - Widget - Selector
 *
 * @version 3.2.4
 * @since   3.1.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Widget_Selector' ) ) :

class WCJ_Widget_Selector extends WCJ_Widget {

	/**
	 * get_data.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function get_data( $id ) {
		switch ( $id ) {
			case 'id_base':
				return 'wcj_widget_selector';
			case 'name':
				return __( 'Booster - Selector', 'woocommerce-jetpack' );
			case 'description':
				return __( 'Booster: Selector Widget', 'woocommerce-jetpack' ) . ' (' . __( 'currently for "Product Visibility by Country" module only', 'woocommerce-jetpack' ) . ')';
		}
	}

	/**
	 * get_content.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function get_content( $instance ) {
		return do_shortcode( '[wcj_selector selector_type="' . $instance['selector_type'] . '"]' );
	}

	/**
	 * get_options.
	 *
	 * @version 3.2.4
	 * @since   3.1.0
	 */
	function get_options() {
		return array(
			array(
				'title'    => __( 'Title', 'woocommerce-jetpack' ),
				'id'       => 'title',
				'default'  => '',
				'type'     => 'text',
				'class'    => 'widefat',
			),
			array(
				'title'    => __( 'Selector Type', 'woocommerce-jetpack' ),
				'id'       => 'selector_type',
				'default'  => 'country',
				'type'     => 'select',
				'options'  => array(
					'country'                   => __( 'Countries', 'woocommerce-jetpack' ),
					'product_custom_visibility' => __( 'Product custom visibility', 'woocommerce-jetpack' ),
				),
				'class'    => 'widefat',
			),
		);
	}

}

endif;

if ( ! function_exists( 'register_wcj_widget_selector' ) ) {
	/**
	 * Register WCJ_Widget_Selector widget.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function register_wcj_widget_selector() {
		register_widget( 'WCJ_Widget_Selector' );
	}
}
add_action( 'widgets_init', 'register_wcj_widget_selector' );
