<?php
/**
 * Booster for WooCommerce - Module - Product Bulk Meta Editor
 *
 * @version 5.2.0
 * @since   2.8.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Product_Bulk_Meta_Editor' ) ) :

class WCJ_Product_Bulk_Meta_Editor extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 5.2.0
	 * @since   2.8.0
	 */
	function __construct() {

		$this->id         = 'product_bulk_meta_editor';
		$this->short_desc = __( 'Product Bulk Meta Editor', 'woocommerce-jetpack' );
		$this->desc       = __( 'Set products meta with bulk editor (Variations available in Plus).', 'woocommerce-jetpack' );
		$this->desc_pro   = __( 'Set products meta with bulk editor.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-product-bulk-meta-editor';
		parent::__construct();

		$this->add_tools( array(
			'product_bulk_meta_editor' => array(
				'title' => __( 'Product Bulk Meta Editor', 'woocommerce-jetpack' ),
				'desc'  => __( 'Product Bulk Meta Editor Tool.', 'woocommerce-jetpack' ),
			),
		) );
	}

	/**
	 * create_product_bulk_meta_editor_tool.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 * @todo    (maybe) real permalink instead of `/?p=`
	 * @todo    (maybe) `wc_add_notice`
	 * @todo    (maybe) tab order (go through input fields in meta table)
	 * @todo    (maybe) "save" on enter key ("wrong form" issue)
	 * @todo    (maybe) all "submit" inputs as single "name" but with different "value"
	 * @todo    (maybe) products by category; tag; custom taxonomy
	 * @todo    (maybe) "delete all meta" button (for single product) - needs confirmation
	 * @todo    (maybe) checkboxes for each product
	 * @todo    (maybe) sortable columns in meta table
	 * @todo    (maybe) multiple meta keys
	 */
	function create_product_bulk_meta_editor_tool() {
		// Actions
		$result = $this->perform_actions();
		// Preparing products data
		$_products = wcj_get_products( array(), 'any', 512, ( 'yes' === apply_filters( 'booster_option', 'no', wcj_get_option( 'wcj_product_bulk_meta_editor_add_variations', 'no' ) ) ) );
		$selected_products = isset( $_POST['wcj_product_bulk_meta_editor_products'] ) ? $_POST['wcj_product_bulk_meta_editor_products'] : array();
		// Output
		echo $this->get_tool_html( $result['meta_name'], $result['result_message'], $_products, $selected_products, $result['set_meta'] );
	}

	/**
	 * perform_actions.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 * @todo    break this into separate functions
	 */
	function perform_actions() {
		$meta_name      = '';
		$set_meta       = '';
		$result_message = '';
		if (
			isset( $_POST['wcj_product_bulk_meta_editor_save_single'] ) && 0 != $_POST['wcj_product_bulk_meta_editor_save_single'] &&
			isset( $_POST['wcj_product_bulk_meta_editor_meta'] ) && '' !== $_POST['wcj_product_bulk_meta_editor_meta']
		) {
			$meta_name = $_POST['wcj_product_bulk_meta_editor_meta'];
			$success = 0;
			$fail = 0;
			$_product_id = $_POST['wcj_product_bulk_meta_editor_save_single'];
			if ( update_post_meta( $_product_id, $meta_name, $_POST[ 'wcj_product_bulk_meta_editor_id_' . $_product_id ] ) ) {
				$success++;
			} else {
				$fail++;
			}
			$result_message = $this->get_result_message( $success, $fail );
		} elseif (
			isset( $_POST['wcj_product_bulk_meta_editor_delete_single'] ) && 0 != $_POST['wcj_product_bulk_meta_editor_delete_single'] &&
			isset( $_POST['wcj_product_bulk_meta_editor_meta'] ) && '' !== $_POST['wcj_product_bulk_meta_editor_meta']
		) {
			$meta_name = $_POST['wcj_product_bulk_meta_editor_meta'];
			$success = 0;
			$fail = 0;
			$_product_id = $_POST['wcj_product_bulk_meta_editor_delete_single'];
			if ( delete_post_meta( $_product_id, $meta_name ) ) {
				$success++;
			} else {
				$fail++;
			}
			$result_message = $this->get_result_message( $success, $fail );
		} elseif (
			isset( $_POST['wcj_product_bulk_meta_editor_save_all'] ) &&
			isset( $_POST['wcj_product_bulk_meta_editor_meta'] ) && '' !== $_POST['wcj_product_bulk_meta_editor_meta']
		) {
			$meta_name = $_POST['wcj_product_bulk_meta_editor_meta'];
			$key_start_length = strlen( 'wcj_product_bulk_meta_editor_id_' );
			$success = 0;
			$fail = 0;
			foreach ( $_POST as $key => $value ) {
				if ( false !== strpos( $key, 'wcj_product_bulk_meta_editor_id_' ) ) {
					$product_id = substr( $key, $key_start_length - strlen( $key ) );
					if ( update_post_meta( $product_id, $meta_name, $value ) ) {
						$success++;
					} else {
						$fail++;
					}
				}
			}
			$result_message = $this->get_result_message( $success, $fail );
		} elseif (
			isset( $_POST['wcj_product_bulk_meta_editor_delete_all'] ) &&
			isset( $_POST['wcj_product_bulk_meta_editor_meta'] ) && '' !== $_POST['wcj_product_bulk_meta_editor_meta']
		) {
			$meta_name = $_POST['wcj_product_bulk_meta_editor_meta'];
			$key_start_length = strlen( 'wcj_product_bulk_meta_editor_id_' );
			$success = 0;
			$fail = 0;
			foreach ( $_POST as $key => $value ) {
				if ( false !== strpos( $key, 'wcj_product_bulk_meta_editor_id_' ) ) {
					$product_id = substr( $key, $key_start_length - strlen( $key ) );
					if ( delete_post_meta( $product_id, $meta_name ) ) {
						$success++;
					} else {
						$fail++;
					}
				}
			}
			$result_message = $this->get_result_message( $success, $fail );
		} elseif (
			isset( $_POST['wcj_product_bulk_meta_editor_set'] ) &&
			isset( $_POST['wcj_product_bulk_meta_editor_set_meta'] ) &&
			isset( $_POST['wcj_product_bulk_meta_editor_meta'] ) && '' !== $_POST['wcj_product_bulk_meta_editor_meta']
		) {
			$meta_name = $_POST['wcj_product_bulk_meta_editor_meta'];
			$set_meta = $_POST['wcj_product_bulk_meta_editor_set_meta'];
			$key_start_length = strlen( 'wcj_product_bulk_meta_editor_id_' );
			$success = 0;
			$fail = 0;
			foreach ( $_POST as $key => $value ) {
				if ( false !== strpos( $key, 'wcj_product_bulk_meta_editor_id_' ) ) {
					$product_id = substr( $key, $key_start_length - strlen( $key ) );
					if ( update_post_meta( $product_id, $meta_name, $set_meta ) ) {
						$success++;
					} else {
						$fail++;
					}
				}
			}
			$result_message = $this->get_result_message( $success, $fail );
		} elseif ( isset( $_POST['wcj_product_bulk_meta_editor_show'] ) ) {
			if ( '' === $_POST['wcj_product_bulk_meta_editor_show_meta'] ) {
				$result_message = '<p><div class="error"><p>' . __( 'Please enter meta key.', 'woocommerce-jetpack' ) . '</p></div></p>';
			}
			$meta_name = $_POST['wcj_product_bulk_meta_editor_show_meta'];
		}
		return array(
			'meta_name'      => $meta_name,
			'set_meta'       => $set_meta,
			'result_message' => $result_message,
		);
	}

	/**
	 * get_result_message.
	 *
	 * @version 3.0.0
	 * @since   2.8.0
	 */
	function get_result_message( $success, $fail ) {
		$result_message = '';
		if ( $success > 0 ) {
			$result_message .= '<p><div class="notice notice-success is-dismissible"><p>' . sprintf( __( 'Meta for <strong>%d</strong> product(s) was updated.', 'woocommerce-jetpack' ), $success ) . '</p></div></p>';
		}
		if ( $fail > 0 ) {
			$result_message .= '<p><div class="notice notice-warning is-dismissible"><p>' . sprintf( __( 'Meta for <strong>%d</strong> product(s) was not updated.', 'woocommerce-jetpack' ), $fail ) . '</p></div></p>';
		}
		return $result_message;
	}

	/**
	 * get_tool_html.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function get_tool_html( $meta_name, $result_message, $_products, $selected_products, $set_meta ) {
		$html = '';
		$html .= '<div class="wrap">';
		$html .= $this->get_tool_header_html( 'product_bulk_meta_editor' );
		$html .= $result_message;
		$html .= '<form method="post" action="">';
		$html .= $this->get_html_meta_key_input( $meta_name );
		$html .= $this->get_html_products_select( $_products, $selected_products );
		if ( '' != $meta_name ) {
			$html .= $this->get_html_meta_table( $meta_name, $_products, $selected_products, $set_meta );
		}
		$html .= '</form>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * get_html_meta_key_input.
	 *
	 * @version 3.0.0
	 * @since   2.8.0
	 */
	function get_html_meta_key_input( $meta_name ) {
		$meta_html = '';
		$meta_html .= '<p>';
		$meta_html .= '<label for="wcj_product_bulk_meta_editor_show_meta">';
		$meta_html .= __( 'Meta key', 'woocommerce-jetpack' );
		if ( '' == $meta_name ) {
			$meta_html .= ', ' . sprintf( __( 'for example %s', 'woocommerce-jetpack' ), '<code>_sku</code>' );
		}
		$meta_html .= '</label>';
		$meta_html .= '<input required class="widefat" type="text" id="wcj_product_bulk_meta_editor_show_meta" name="wcj_product_bulk_meta_editor_show_meta" value="' . $meta_name . '">';
		$meta_html .= '</p>';
		$meta_html .= '<p>';
		$meta_html .= '<button class="button-primary" type="submit" name="wcj_product_bulk_meta_editor_show" value="show">' . __( 'Show', 'woocommerce-jetpack' ) . '</button>';
		$meta_html .= '</p>';
		$table_data = array();
		$table_data[] = array(
			__( 'Meta', 'woocommerce-jetpack' ),
		);
		$table_data[] = array(
			$meta_html,
		);
		return '<p>' . wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) ) . '</p>';
	}

	/**
	 * get_html_products_select.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function get_html_products_select( $_products, $selected_products ) {
		$products_html = '';
		$products_html .= '<select name="wcj_product_bulk_meta_editor_products[]" multiple style="height:300px;width:100%;">';
		foreach ( $_products as $product_id => $product_title ) {
			$selected = ( empty( $selected_products ) || in_array( $product_id, $selected_products ) ? ' selected' : '' );
			$products_html .= '<option' . $selected . ' value="' . $product_id . '">' . $product_title . '</option>';
		}
		$products_html .= '</select>';
		$tip = '<p style="font-style:italic;color:gray;">' . '* ' .
			__( 'Hold <strong>Control</strong> key to select multiple products. Press <strong>Control</strong> + <strong>A</strong> to select all products.', 'woocommerce-jetpack' ) .
		'</p>';
		$table_data = array();
		$table_data[] = array(
			__( 'Products', 'woocommerce-jetpack' ),
		);
		$table_data[] = array(
			$products_html . $tip,
		);
		return '<p>' . wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) ) . '</p>';
	}

	/**
	 * get_html_meta_table.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function get_html_meta_table( $meta_name, $_products, $selected_products, $set_meta ) {
		$html = '';
		$js_confirmation = ' onclick="return confirm(\'' . __( 'Are you sure?', 'woocommerce-jetpack' ) . '\')"';
		$html .= $this->get_html_meta_table_set_single_value( $set_meta, $js_confirmation );
		$html .= $this->get_html_meta_table_buttons( $meta_name, $js_confirmation );
		$html .= $this->get_html_meta_table_content( $meta_name, $_products, $selected_products, $js_confirmation );
		return $html;
	}

	/**
	 * get_html_meta_table_set_single_value.
	 *
	 * @version 3.0.0
	 * @since   2.8.0
	 */
	function get_html_meta_table_set_single_value( $set_meta, $js_confirmation ) {
		$single_value_html = '<p>' .
			'<label for="wcj_product_bulk_meta_editor_set_meta">' . __( 'Value', 'woocommerce-jetpack' ) . '</label>' .
			'<input type="text" class="widefat" id="wcj_product_bulk_meta_editor_set_meta" name="wcj_product_bulk_meta_editor_set_meta" value="' . $set_meta . '">' .
		'</p>' .
		'<p>' .
			'<button class="button-primary" type="submit" name="wcj_product_bulk_meta_editor_set" value="set"' . $js_confirmation . '>' .
				__( 'Set', 'woocommerce-jetpack' ) . '</button>' .
		'</p>';
		$table_data = array();
		$table_data[] = array(
			__( 'Set Meta for All Products', 'woocommerce-jetpack' ),
		);
		$table_data[] = array(
			$single_value_html,
		);
		return '<p>' . wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) ) . '</p>';
	}

	/**
	 * get_html_meta_table_buttons.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function get_html_meta_table_buttons( $meta_name, $js_confirmation ) {
		$html = '';
		$html .= '<p>';
		$html .= '<input class="button-primary" type="submit" name="wcj_product_bulk_meta_editor_save_all" value="' . __( 'Save all', 'woocommerce-jetpack' ) . '"' .
			$js_confirmation . '>';
		$html .= ' ';
		$html .= '<input class="button-primary" type="submit" name="wcj_product_bulk_meta_editor_delete_all" value="' . __( 'Delete all', 'woocommerce-jetpack' ) . '"' .
			$js_confirmation . '>';
		$html .= '</p>';
		$html .= '<input type="hidden" name="wcj_product_bulk_meta_editor_meta" value="' . $meta_name . '">';
		return $html;
	}

	/**
	 * get_html_meta_table_content.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function get_html_meta_table_content( $meta_name, $_products, $selected_products, $js_confirmation ) {
		$table_data = array();
		$table_headings = array(
			__( 'Product', 'woocommerce-jetpack' ),
			__( 'Meta', 'woocommerce-jetpack' ) . ': <code>' . $meta_name . '</code>',
			'',
		);
		$additional_columns = wcj_get_option( 'wcj_product_bulk_meta_editor_additional_columns', '' );
		$table_headings = $this->maybe_add_additional_columns_headings( $table_headings, $additional_columns );
		$table_data[] = $table_headings;
		foreach ( $_products as $product_id => $product_title ) {
			if ( ! in_array( $product_id, $selected_products ) ) {
				continue;
			}
			if ( ! metadata_exists( 'post', $product_id, $meta_name ) && 'yes' === wcj_get_option( 'wcj_product_bulk_meta_editor_check_if_exists', 'no' ) ) {
				$_post_meta = '<em>' . 'N/A' . '</em>';
				$save_button = '';
				$delete_button = '';
			} else {
				$_post_meta = get_post_meta( $product_id, $meta_name, true );
				if ( is_array( $_post_meta ) || is_object( $_post_meta ) ) {
					$_post_meta  = print_r( $_post_meta, true );
				} else {
					$placeholder = ( ! metadata_exists( 'post', $product_id, $meta_name ) ? ' placeholder="N/A"' : '' );
					$_post_meta  = '<input' . $placeholder . ' style="width:100%;" type="text" name="wcj_product_bulk_meta_editor_id_' . $product_id . '" value="' .
						$_post_meta . '">';
				}
				$save_button = '<button class="button-primary" type="submit" name="wcj_product_bulk_meta_editor_save_single" value="' . $product_id . '">'
					. __( 'Save', 'woocommerce-jetpack' ) . '</button>';
				$delete_button = ( metadata_exists( 'post', $product_id, $meta_name ) ?
					'<button class="button-primary" type="submit" name="wcj_product_bulk_meta_editor_delete_single" value="' . $product_id . '"' . $js_confirmation . '>'
						. __( 'Delete', 'woocommerce-jetpack' ) . '</button>' : '' );
			}
			$row = array(
				'<a href="/?p=' . $product_id . '"> ' . $product_title . '</a>',
				$_post_meta,
				$save_button . ' ' . $delete_button,
			);
			$row = $this->maybe_add_additional_columns_content( $row, $additional_columns, $product_id );
			$table_data[] = $row;
		}
		return '<p>' . wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) ) . '</p>';
	}

	/**
	 * maybe_add_additional_columns_headings.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function maybe_add_additional_columns_headings( $table_headings, $additional_columns ) {
		if ( ! empty( $additional_columns ) ) {
			if ( in_array( 'product_id', $additional_columns ) ) {
				$table_headings[] = __( 'Product ID', 'woocommerce-jetpack' );
			}
			if ( in_array( 'product_status', $additional_columns ) ) {
				$table_headings[] = __( 'Product status', 'woocommerce-jetpack' );
			}
			if ( in_array( 'product_all_meta_keys', $additional_columns ) ) {
				$table_headings[] = __( 'Meta keys', 'woocommerce-jetpack' );
			}
		}
		return $table_headings;
	}

	/**
	 * maybe_add_additional_columns_content.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function maybe_add_additional_columns_content( $row, $additional_columns, $product_id ) {
		if ( ! empty( $additional_columns ) ) {
			if ( in_array( 'product_id', $additional_columns ) ) {
				$row[] = $product_id;
			}
			if ( in_array( 'product_status', $additional_columns ) ) {
				$row[] = get_post_status( $product_id );
			}
			if ( in_array( 'product_all_meta_keys', $additional_columns ) ) {
				$row[] = '<details style="color:gray;">' .
					'<summary><em>' . __( 'Show all', 'woocommerce-jetpack' ) . '</em></summary>' .
					'<p>' . implode( '<br>', array_keys( get_post_meta( $product_id ) ) ) . '</p>' .
				'</details>';
			}
		}
		return $row;
	}

}

endif;

return new WCJ_Product_Bulk_Meta_Editor();
