<?php
/**
 * Booster for WooCommerce - Price by Country - Local
 *
 * @version 4.0.0
 * @author  Pluggabl LLC.
 * @todo    (maybe) remove this and leave only standard meta box option (i.e. only `'meta_box' === wcj_get_option( 'wcj_price_by_country_local_options_style', 'inline' )`)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Price_by_Country_Local' ) ) :

class WCJ_Price_by_Country_Local {

	/**
	 * Constructor.
	 *
	 * @version 2.3.9
	 */
	function __construct() {
		add_action( 'woocommerce_ajax_save_product_variations',         array( $this, 'save_custom_meta_box_on_product_edit_ajax' ), PHP_INT_MAX, 1 );
		add_action( 'save_post_product',                                array( $this, 'save_custom_meta_box_on_product_edit' ), PHP_INT_MAX, 2 );
		add_action( 'woocommerce_product_options_pricing',              array( $this, 'add_simple_pricing' ), PHP_INT_MAX, 0 );
		add_action( 'woocommerce_product_after_variable_attributes',    array( $this, 'add_variable_pricing' ), PHP_INT_MAX, 3 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_hidden_save' ), PHP_INT_MAX, 0 );
		add_action( 'woocommerce_product_after_variable_attributes',    array( $this, 'add_hidden_save' ), PHP_INT_MAX, 0 );
	}

	/**
	 * add_simple_pricing.
	 */
	function add_simple_pricing() {
		$this->create_custom_meta_box( 'simple' );
	}

	/**
	 * add_variable_pricing.
	 */
	function add_variable_pricing( $loop, $variation_data, $variation ) {
		$this->create_custom_meta_box( 'variable', $variation->ID );
	}

	/**
	 * create_custom_meta_box.
	 */
	function create_custom_meta_box( $simple_or_variable, $product_id = 0 ) {

		$current_post_id = ( $product_id == 0) ? get_the_ID() : $product_id;
		$the_product = wc_get_product( $current_post_id );
		if ( ! $the_product ) {
			return;
		}

		$total_country_groups_number = $this->get_total_country_groups_number();

		// Start html
		$html = '';

		if ( $the_product->is_type( 'variation' ) ) {
			$html .= $this->get_all_options_html( $simple_or_variable, $current_post_id, $total_country_groups_number, '_' . $current_post_id );
		} else {
			$html .= $this->get_all_options_html( $simple_or_variable, $current_post_id, $total_country_groups_number );
		}

		// Output
		echo $html;
	}

	/**
	 * add_hidden_save.
	 */
	function add_hidden_save() {
		$meta_box_id = 'price_by_country';
		echo '<input type="hidden" name="woojetpack_' . $meta_box_id . '_save_post" value="woojetpack_' . $meta_box_id . '_save_post">';
	}

	/**
	 * get_total_country_groups_number.
	 */
	function get_total_country_groups_number() {
		return apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_price_by_country_total_groups_number', 1 ) );
	}

	/**
	 * get_prices_options.
	 */
	function get_prices_options() {

		$meta_box_id = 'price_by_country';
		$this->scope = 'local';

		$options = array(

			array(
				'id'      => 'wcj_' . $meta_box_id . '_regular_price_' . $this->scope . '_',
				'title'   => __( 'Regular Price', 'woocommerce' ),
				'type'    => 'text',
				'default' => 0,
			),

			array(
				'id'      => 'wcj_' . $meta_box_id . '_sale_price_' . $this->scope . '_',
				'title'   => __( 'Sale Price', 'woocommerce' ),
				'type'    => 'text',
				'default' => 0,
			),

			array(
				'id'      => 'wcj_' . $meta_box_id . '_make_empty_price_' . $this->scope . '_',
				'title'   => __( 'Make empty price', 'woocommerce-jetpack' ),
				'type'    => 'checkbox',
				'default' => 'off',
			),

		);
		return $options;
	}

	/**
	 * Save options.
	 */
	function save_options( $post_id, $total_options_groups, $variation_id_addon = '' ) {
		$options = $this->get_prices_options();
		for ( $i = 1; $i <= $total_options_groups; $i++ ) {
			foreach ( $options as $option ) {
				if ( isset( $_POST[ $option['id'] . $i . $variation_id_addon ] ) ) {
					update_post_meta( $post_id, '_' . $option['id'] . $i, $_POST[ $option['id'] . $i . $variation_id_addon ] );
				}
				elseif ( 'checkbox' === $option['type'] ) {
					update_post_meta( $post_id, '_' . $option['id'] . $i, 'off' );
				}
			}
		}
	}

	/**
	 * Save Custom Meta Box on Product Edit - variations "Save Changes" button (ajax).
	 *
	 * @version 2.3.9
	 * @since   2.3.9
	 */
	function save_custom_meta_box_on_product_edit_ajax( $product_id ) {
		return $this->save_custom_meta_box_on_product_edit( $product_id, /* 'ajax' */ null );
	}

	/**
	 * Save Custom Meta Box on Product Edit.
	 */
	function save_custom_meta_box_on_product_edit( $post_id, $post ) {

		$meta_box_id = 'price_by_country';

		// Check that we are saving with custom meta box displayed.
		if ( ! isset( $_POST[ 'woojetpack_' . $meta_box_id . '_save_post' ] ) )
			return;

		$the_product = wc_get_product( $post_id );
		if ( ! $the_product )
			return;

		$total_options_groups = $this->get_total_country_groups_number();

		if ( $the_product->is_type( 'variable' ) ) {
			$variations = $the_product->get_available_variations();
			if ( empty( $variations ) ) {
				return;
			}
			foreach ( $variations as $variation ) {
				$this->save_options( $variation['variation_id'], $total_options_groups, '_' . $variation['variation_id'] );
			}
		}
		else {
			$this->save_options( $post_id, $total_options_groups );
		}
	}

	/**
	 * get_option_field_html.
	 */
	function get_option_field_html( $current_post_id, $option_id, $option, $variation_id_addon = '' ) {
		$html = '';
		$option_value = get_post_meta( $current_post_id, '_' . $option_id, true );
		$option_id .= $variation_id_addon;
		$html .= wcj_get_option_html( $option['type'], $option_id,  $option_value, '', 'short' );
		return $html;
	}

	/**
	 * get_all_options_html.
	 *
	 * @version 4.0.0
	 */
	function get_all_options_html( $simple_or_variable, $current_post_id, $total_number, $variation_id_addon = '' ) {
		$html = '';
		$options = $this->get_prices_options();
		for ( $i = 1; $i <= $total_number; $i++ ) {

			if ( 'variable' == $simple_or_variable ) {
				$html .= '<div>';
			}

			$countries = '';
			switch ( wcj_get_option( 'wcj_price_by_country_selection', 'comma_list' ) ) {
				case 'comma_list':
					$countries .= wcj_get_option( 'wcj_price_by_country_exchange_rate_countries_group_' . $i, '' );
					break;
				case 'multiselect':
					$countries .= ( '' != ( $group = wcj_get_option( 'wcj_price_by_country_countries_group_' . $i, '' ) ) ? implode( ',', $group ) : '' );
					break;
				case 'chosen_select':
					$countries .= ( '' != ( $group = wcj_get_option( 'wcj_price_by_country_countries_group_chosen_select_' . $i, '' ) ) ? implode( ',', $group ) : '' );
					break;
			}
			$admin_title = wcj_get_option( 'wcj_price_by_country_countries_group_admin_title_' . $i, __( 'Group', 'woocommerce-jetpack' ) . ' #' . $i );
			$html .= '<details style="float: left; border-top: 1px dashed #cccccc; width: 100%; padding-top: 10px;">' .
				'<summary style="font-weight:bold;">' . $admin_title . '</summary><p>' . $countries . '</p>' .
			'</details>';

			foreach ( $options as $option ) {
				$option_id = $option['id'] . $i;
				if ( 'simple' == $simple_or_variable ) {
					$html .= '<p class="form-field ' . $option_id . $variation_id_addon . '_field">';
				} else {
					$column_position = 'full';
					if ( 'checkbox' != $option['type'] ) {
						$column_position = ( false !== strpos( $option['id'], '_regular_price_' ) ) ? 'first' : 'last';
					}
					$html .= '<p class="form-row form-row-' . $column_position . '">';
				}
				$group_currency_code = wcj_get_option( 'wcj_price_by_country_exchange_rate_currency_group_' . $i );
				$currency_code_html = ( 'checkbox' != $option['type'] ) ? ' (' . get_woocommerce_currency_symbol( $group_currency_code ) . ')' : '';
				$html .= '<label for="' . $option_id . $variation_id_addon . '">' . $option['title'] . $currency_code_html . '</label>';
				$html .= $this->get_option_field_html( $current_post_id, $option_id, $option, $variation_id_addon );
				$html .= '</p>';
			}

			if ( 'variable' == $simple_or_variable ) {
				$html .= '</div>';
			}
		}
		return $html;
	}

}

endif;

return new WCJ_Price_by_Country_Local();
