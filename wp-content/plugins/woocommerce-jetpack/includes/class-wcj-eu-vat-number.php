<?php
/**
 * Booster for WooCommerce - Module - EU VAT Number
 *
 * @version 5.2.0
 * @since   2.3.9
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_EU_VAT_Number' ) ) :

class WCJ_EU_VAT_Number extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 5.2.0
	 * @todo    [feature] add option to add "Verify" button to frontend
	 */
	function __construct() {

		$this->id         = 'eu_vat_number';
		$this->short_desc = __( 'EU VAT Number', 'woocommerce-jetpack' );
		$this->desc       = __( 'Collect and validate EU VAT numbers on the checkout. Automatically disable VAT for valid numbers. Add all EU countries VAT standard rates to WooCommerce. Show VAT field for EU countries only (Plus). Check for IP Location Country  (Plus)', 'woocommerce-jetpack' );
		$this->desc_pro   = __( 'Collect and validate EU VAT numbers on the checkout. Automatically disable VAT for valid numbers. Add all EU countries VAT standard rates to WooCommerce.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-eu-vat-number';
		parent::__construct();

		$this->add_tools( array(
			'eu_countries_vat_rates' => array(
				'title' => __( 'EU Countries VAT Rates', 'woocommerce-jetpack' ),
				'desc'  => __( 'Add all EU countries VAT standard rates to WooCommerce.', 'woocommerce-jetpack' ),
			),
		) );

		if ( $this->is_enabled() ) {
			add_action( 'init',                                        array( $this, 'start_session' ) );
			add_filter( 'woocommerce_checkout_fields',                 array( $this, 'add_eu_vat_number_checkout_field_to_frontend' ), PHP_INT_MAX );
			add_filter( 'woocommerce_admin_billing_fields',            array( $this, 'add_billing_eu_vat_number_field_to_admin_order_display' ), PHP_INT_MAX );
			add_action( 'wp_enqueue_scripts',                          array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_wcj_validate_eu_vat_number',          array( $this, 'wcj_validate_eu_vat_number' ) );
			add_action( 'wp_ajax_nopriv_wcj_validate_eu_vat_number',   array( $this, 'wcj_validate_eu_vat_number' ) );
			add_filter( 'wp',                                          array( $this, 'maybe_exclude_vat' ), PHP_INT_MAX );
			add_filter( 'woocommerce_cart_tax_totals',                 array( $this, 'maybe_remove_tax_totals' ), 1 );
			add_filter( 'woocommerce_calculated_total',                array( $this, 'maybe_recalculate_tax_totals' ), 1, 2 );
			add_action( 'woocommerce_after_checkout_validation',       array( $this, 'checkout_validate_vat' ), PHP_INT_MAX );
			add_filter( 'woocommerce_customer_meta_fields',            array( $this, 'add_eu_vat_number_customer_meta_field' ) );
			add_filter( 'default_checkout_billing_eu_vat_number',      array( $this, 'add_default_checkout_billing_eu_vat_number' ), PHP_INT_MAX, 2 );

			if ( 'after_order_table' === wcj_get_option( 'wcj_eu_vat_number_display_position', 'after_order_table' ) ) {
				add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_eu_vat_number_to_order_display' ), PHP_INT_MAX );
				add_action( 'woocommerce_email_after_order_table',         array( $this, 'add_eu_vat_number_to_order_display' ), PHP_INT_MAX );
			} else {
				add_filter( 'woocommerce_order_formatted_billing_address',         array( $this, 'add_eu_vat_number_to_order_billing_address' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'add_eu_vat_number_to_my_account_billing_address' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_localisation_address_formats',            array( $this, 'add_eu_vat_number_to_address_formats' ) );
				add_filter( 'woocommerce_formatted_address_replacements',          array( $this, 'replace_eu_vat_number_in_address_formats' ), PHP_INT_MAX, 2 );
			}

			$this->eu_countries_vat_rates_tool = include_once( 'tools/class-wcj-eu-countries-vat-rates-tool.php' );

			// EU VAT number summary on order edit page
			if ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_add_order_edit_metabox', 'no' ) ) {
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				// "Validate VAT and remove taxes" button
				add_action( 'admin_init', array( $this, 'admin_validate_vat_and_maybe_remove_taxes' ), PHP_INT_MAX );
			}

			// Admin order edit - "Load billing address" button
			add_filter( 'woocommerce_ajax_get_customer_details', array( $this, 'add_billing_eu_vat_number_to_ajax_get_customer_details' ), PHP_INT_MAX, 3 );
		}
	}

	/**
	 * maybe_recalculate_tax_totals.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 *
	 * @see https://gist.github.com/TimBHowe/fe9418b9224d8b8cb339
	 * @param $total
	 * @param $cart
	 *
	 * @return mixed
	 */
	function maybe_recalculate_tax_totals( $total, $cart ) {
		if ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_disable_for_valid_on_cart', 'no' ) && $this->need_to_exclude_vat() ) {
			return $total - $cart->get_taxes_total();
		}
		return $total;
	}

	/**
	 * maybe_remove_tax_totals.
	 *
	 * @version 4.6.0
	 * @since   4.6.0
	 *
	 * @see https://gist.github.com/TimBHowe/fe9418b9224d8b8cb339
	 * @param $tax_totals
	 *
	 * @return array
	 */
	function maybe_remove_tax_totals( $tax_totals ) {
		if ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_disable_for_valid_on_cart', 'no' ) && $this->need_to_exclude_vat() ) {
			$tax_totals = array();
		}
		return $tax_totals;
	}

	/**
	 * admin_validate_vat_and_maybe_remove_taxes.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function admin_validate_vat_and_maybe_remove_taxes() {
		if ( isset( $_GET['validate_vat_and_maybe_remove_taxes'] ) ) {
			$order_id = $_GET['validate_vat_and_maybe_remove_taxes'];
			$order    = wc_get_order( $order_id );
			if ( $order ) {
				$vat_id = get_post_meta( $order_id, '_billing_eu_vat_number', true );
				if ( '' != $vat_id && strlen( $vat_id ) > 2 ) {
					if ( wcj_validate_vat( substr( $vat_id, 0, 2 ), substr( $vat_id, 2 ) ) ) {
						foreach ( $order->get_items( array( 'line_item', 'fee' ) ) as $item_id => $item ) {
							$item->set_taxes( false );
						}
						foreach ( $order->get_shipping_methods() as $item_id => $item ) {
							$item->set_taxes( false );
						}
						$order->update_taxes();
						$order->calculate_totals( false );
					}
				}
			}
			wp_safe_redirect( remove_query_arg( 'validate_vat_and_maybe_remove_taxes' ) );
			exit;
		}
	}

	/**
	 * add_billing_eu_vat_number_to_ajax_get_customer_details.
	 *
	 * @version 3.3.0
	 * @since   3.3.0
	 */
	function add_billing_eu_vat_number_to_ajax_get_customer_details( $data, $customer, $user_id ) {
		$data['billing']['eu_vat_number'] = get_user_meta( $user_id, 'billing_eu_vat_number', true );
		return $data;
	}

	/**
	 * add_meta_box.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 */
	function add_meta_box() {
		$screen   = ( isset( $this->meta_box_screen ) )   ? $this->meta_box_screen   : 'shop_order';
		$context  = ( isset( $this->meta_box_context ) )  ? $this->meta_box_context  : 'side';
		$priority = ( isset( $this->meta_box_priority ) ) ? $this->meta_box_priority : 'low';
		add_meta_box(
			'wc-jetpack-' . $this->id,
			__( 'Booster', 'woocommerce-jetpack' ) . ': ' . $this->short_desc,
			array( $this, 'create_meta_box' ),
			$screen,
			$context,
			$priority
		);
	}

	/**
	 * create_meta_box.
	 *
	 * @version 3.3.0
	 * @since   2.6.0
	 */
	function create_meta_box() {
		$order_id = get_the_ID();
		$_order = wc_get_order( $order_id );
		$_customer_ip_address = ( WCJ_IS_WC_VERSION_BELOW_3 ? $_order->customer_ip_address : $_order->get_customer_ip_address() );

		// Country by IP
		if ( class_exists( 'WC_Geolocation' ) ) {
			// Get the country by IP
			$location = WC_Geolocation::geolocate_ip( $_customer_ip_address );
			// Base fallback
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', wcj_get_option( 'woocommerce_default_country' ) ) );
			}
			$customer_country = ( isset( $location['country'] ) ) ? $location['country'] : '';
		} else {
			$customer_country = '';
		}

		// Customer EU VAT number
		$customer_eu_vat_number = get_post_meta( $order_id, '_billing_eu_vat_number', true );
		if ( '' == $customer_eu_vat_number ) {
			$customer_eu_vat_number = '-';
		}

		// Taxes
		$taxes = '';
		$taxes_array = $_order->get_tax_totals();
		if ( empty( $taxes_array ) ) {
			$taxes = '-';
		} else {
			foreach ( $taxes_array as $tax ) {
				$taxes .= $tax->label . ': ' . $tax->formatted_amount . '<br>';
			}
		}

		// Results table
		$table_data = array(
			array(
				__( 'Customer IP', 'woocommerce-jetpack' ),
				$_customer_ip_address
			),
			array(
				__( 'Country by IP', 'woocommerce-jetpack' ),
				wcj_get_country_flag_by_code( $customer_country ) . ' ' . wcj_get_country_name_by_code( $customer_country ) . ' [' . $customer_country . ']'
			),
			array(
				__( 'Customer EU VAT Number', 'woocommerce-jetpack' ),
				$customer_eu_vat_number
			),
			array(
				__( 'Taxes', 'woocommerce-jetpack' ),
				$taxes,
			),
		);
		echo wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'vertical' ) );
		echo '<p>' . '<a href="' . add_query_arg( 'validate_vat_and_maybe_remove_taxes', $order_id ) . '">' .
			__( 'Validate VAT and remove taxes', 'woocommerce-jetpack' ) . '</a>' . '</p>';
	}

	/**
	 * replace_eu_vat_number_in_address_formats.
	 *
	 * @version 2.5.2
	 * @since   2.5.2
	 */
	function replace_eu_vat_number_in_address_formats( $replacements, $args ) {
		$field_name = 'billing_' . $this->id;
		$replacements['{' . $field_name . '}'] = ( isset( $args[ $field_name ] ) ) ? $args[ $field_name ] : '';
		return $replacements;
	}

	/**
	 * add_eu_vat_number_to_address_formats.
	 *
	 * @version 2.5.2
	 * @since   2.5.2
	 */
	function add_eu_vat_number_to_address_formats( $address_formats ) {
		$field_name = 'billing_' . $this->id;
		$modified_address_formats = array();
		foreach ( $address_formats as $country => $address_format ) {
			$modified_address_formats[ $country ] = $address_format . "\n{" . $field_name . '}';
		}
		return $modified_address_formats;
	}

	/**
	 * add_eu_vat_number_to_my_account_billing_address.
	 *
	 * @version 2.5.2
	 * @since   2.5.2
	 */
	function add_eu_vat_number_to_my_account_billing_address( $fields, $customer_id, $name ) {
		if ( 'billing' === $name ) {
			$field_name = 'billing_' . $this->id;
			$fields[ $field_name ] = get_user_meta( $customer_id, $field_name, true );
		}
		return $fields;
	}

	/**
	 * add_eu_vat_number_to_order_billing_address.
	 *
	 * @version 2.7.0
	 * @since   2.5.2
	 */
	function add_eu_vat_number_to_order_billing_address( $fields, $_order ) {
		$field_name = 'billing_' . $this->id;
		$fields[ $field_name ] = get_post_meta( wcj_get_order_id( $_order ), '_' . $field_name, true );
		return $fields;
	}

	/**
	 * add_eu_vat_number_to_order_display.
	 *
	 * @version 3.2.2
	 * @since   2.4.7
	 */
	function add_eu_vat_number_to_order_display( $order ) {
		$order_id = wcj_get_order_id( $order );
		$html = '';
		$option_name = '_billing_' . $this->id;
		$the_eu_vat_number = get_post_meta( $order_id, $option_name, true );
		if ( '' != $the_eu_vat_number ) {
			$the_label = wcj_get_option( 'wcj_eu_vat_number_field_label', __( 'EU VAT Number', 'woocommerce-jetpack' ) );
			$html .= '<p>' . '<strong>' . $the_label . '</strong>: ' . $the_eu_vat_number . '</p>';
		}
		echo $html;
	}

	/**
	 * create_eu_countries_vat_rates_tool.
	 *
	 * @version 2.3.10
	 * @since   2.3.10
	 */
	function create_eu_countries_vat_rates_tool() {
		return $this->eu_countries_vat_rates_tool->create_eu_countries_vat_rates_tool( $this->get_tool_header_html( 'eu_countries_vat_rates' ) );
	}

	/**
	 * add_default_checkout_billing_eu_vat_number.
	 *
	 * @version 4.3.0
	 */
	function add_default_checkout_billing_eu_vat_number( $default_value, $field_key ) {
		if ( '' != ( $eu_vat_number_to_check = wcj_session_get( 'wcj_eu_vat_number_to_check' ) ) ) {
			return $eu_vat_number_to_check;
		} elseif ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( $meta = get_user_meta( $current_user->ID, 'billing_eu_vat_number', true ) ) {
				return $meta;
			}
		}
		return $default_value;
	}

	/**
	 * add_eu_vat_number_customer_meta_field.
	 */
	function add_eu_vat_number_customer_meta_field( $fields ) {
		$fields['billing']['fields']['billing_eu_vat_number'] = array(
			'label'       => wcj_get_option( 'wcj_eu_vat_number_field_label' ),
			'description' => ''
		);
		return $fields;
	}

	/**
	 * start_session.
	 *
	 * @version 4.9.0
	 */
	function start_session() {
		if ( is_admin() ) {
			return;
		}
		wcj_session_maybe_start();
		$args = array();
		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $args );
			if ( isset( $args['billing_eu_vat_number'] ) && wcj_session_get( 'wcj_eu_vat_number_to_check' ) != $args['billing_eu_vat_number'] ) {
				wcj_session_set( 'wcj_is_eu_vat_number_valid', null );
				wcj_session_set( 'wcj_eu_vat_number_to_check', null );
			}
		}
	}

	/**
	 * restrictive_loading_valid.
	 *
	 * @version 4.9.0
	 * @since   4.9.0
	 *
	 * @return bool
	 */
	function restrictive_loading_valid() {
		if ( empty( $restrictive_loading_conditions = wcj_get_option( 'wcj_eu_vat_number_restrictive_loading', array() ) ) ) {
			return true;
		}
		foreach ( $restrictive_loading_conditions as $condition ) {
			if ( $condition() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * enqueue_scripts.
	 *
	 * @version 4.9.0
	 */
	function enqueue_scripts() {
		if (
			'no' === wcj_get_option( 'wcj_eu_vat_number_validate', 'yes' ) ||
			! $this->restrictive_loading_valid()
		) {
			return;
		}
		wp_enqueue_script( 'wcj-eu-vat-number', wcj_plugin_url() . '/includes/js/wcj-eu-vat-number.js', array(), WCJ()->version, true );
		wp_localize_script( 'wcj-eu-vat-number', 'ajax_object', array(
			'ajax_url'                        => admin_url( 'admin-ajax.php' ),
			'eu_countries'                    => wcj_get_european_union_countries(),
			'show_vat_field_for_eu_only'      => wcj_get_option( 'wcj_eu_vat_number_show_vat_field_for_eu_only', 'no' ),
			'add_progress_text'               => wcj_get_option( 'wcj_eu_vat_number_add_progress_text', 'no' ),
			'progress_text_validating'        => do_shortcode( wcj_get_option( 'wcj_eu_vat_number_progress_text_validating', __( 'Validating VAT. Please wait...', 'woocommerce-jetpack' ) ) ),
			'progress_text_valid'             => do_shortcode( wcj_get_option( 'wcj_eu_vat_number_progress_text_valid', __( 'VAT is valid.', 'woocommerce-jetpack' ) ) ),
			'progress_text_not_valid'         => do_shortcode( wcj_get_option( 'wcj_eu_vat_number_progress_text_not_valid', __( 'VAT is not valid.', 'woocommerce-jetpack' ) ) ),
			'progress_text_validation_failed' => do_shortcode( wcj_get_option( 'wcj_eu_vat_number_progress_text_validation_failed', __( 'Validation failed. Please try again.', 'woocommerce-jetpack' ) ) ),
		) );
	}

	/**
	 * wcj_validate_eu_vat_number.
	 *
	 * @version 4.6.1
	 */
	function wcj_validate_eu_vat_number( $param ) {
		$param         = wp_parse_args( $param, array(
			'wcj_eu_vat_number_to_check' => '',
			'echo'                       => true
		) );
		$eu_vat_number = isset( $param['wcj_eu_vat_number_to_check'] ) && '' != $param['wcj_eu_vat_number_to_check'] ? $param['wcj_eu_vat_number_to_check'] : '';
		$eu_vat_number = empty( $eu_vat_number ) && isset( $_POST['wcj_eu_vat_number_to_check'] ) && '' != $_POST['wcj_eu_vat_number_to_check'] ? $_POST['wcj_eu_vat_number_to_check'] : $eu_vat_number;
		if ( ! empty( $eu_vat_number ) ) {
			$eu_vat_number_to_check         = substr( $eu_vat_number, 2 );
			$eu_vat_number_country_to_check = substr( $eu_vat_number, 0, 2 );
			if ( 'yes' === apply_filters( 'booster_option', 'no', wcj_get_option( 'wcj_eu_vat_number_check_ip_location_country', 'no' ) ) ) {
				$location = WC_Geolocation::geolocate_ip();
				if ( empty( $location['country'] ) ) {
					$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', wcj_get_option( 'woocommerce_default_country' ) ) );
				}
				$is_valid = ( $location['country'] === $eu_vat_number_country_to_check ) ?
					wcj_validate_vat( $eu_vat_number_country_to_check, $eu_vat_number_to_check ) :
					false;
			} else {
				$is_valid = wcj_validate_vat( $eu_vat_number_country_to_check, $eu_vat_number_to_check );
			}
		} else {
			$is_valid = null;
		}
		wcj_session_set( 'wcj_is_eu_vat_number_valid', $is_valid );
		wcj_session_set( 'wcj_eu_vat_number_to_check', $eu_vat_number );
		$response = '3';
		if ( false === $is_valid ) {
			$response = '0';
		} elseif ( true === $is_valid ) {
			$response = '1';
		} elseif ( null === $is_valid ) {
			$response = '2';
		}
		if ( $param['echo'] ) {
			wp_send_json( array( 'result' => $response ) );
		} else {
			return $response;
		}
	}

	/**
	 * need_to_exclude_vat.
	 *
	 * @version 4.7.1
	 * @since   4.6.0
	 *
	 * @return bool
	 */
	function need_to_exclude_vat() {
		if (
			(
				( function_exists( 'is_checkout' ) && is_checkout() ) ||
				( function_exists( 'is_cart' ) && is_cart() ) ||
				defined( 'WOOCOMMERCE_CHECKOUT' ) || defined( 'WOOCOMMERCE_CART' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			) &&
			! empty( WC()->customer ) &&
			'yes' === wcj_get_option( 'wcj_eu_vat_number_validate', 'yes' ) &&
			'yes' === wcj_get_option( 'wcj_eu_vat_number_disable_for_valid', 'yes' ) &&
			(
				( true === wcj_session_get( 'wcj_is_eu_vat_number_valid' ) && null !== ( $eu_vat_number = wcj_session_get( 'wcj_eu_vat_number_to_check' ) ) ) ||
				( 'yes' === wcj_get_option( 'wcj_eu_vat_number_disable_for_valid_by_user_vat', 'no' ) && is_user_logged_in() && ! empty( $eu_vat_number = get_user_meta( get_current_user_id(), 'billing_eu_vat_number', true ) ) && '1' === $this->wcj_validate_eu_vat_number( array( 'wcj_eu_vat_number_to_check' => $eu_vat_number, 'echo' => false ) ) )
			)
		) {
			$preserve_base_country_check_passed = true;
			if ( 'yes' === apply_filters( 'booster_option', 'no', wcj_get_option( 'wcj_eu_vat_number_preserve_in_base_country', 'no' ) ) ) {
				$location = wc_get_base_location();
				if ( empty( $location['country'] ) ) {
					$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', wcj_get_option( 'woocommerce_default_country' ) ) );
				}
				$selected_country = substr( $eu_vat_number, 0, 2 );
				if ( 'EL' === $selected_country ) {
					$selected_country = 'GR';
				}
				$preserve_base_country_check_passed = ( strtoupper( $location['country'] ) !== strtoupper( $selected_country ) );
			}
			if ( $preserve_base_country_check_passed ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( ! function_exists( 'WC' ) || ! empty( WC()->customer ) ) {
				return false;
			}
		}
		return false;
	}

	/**
	 * maybe_exclude_vat.
	 *
	 * @version 4.6.0
	 */
	function maybe_exclude_vat() {
		if ( $this->need_to_exclude_vat() ) {
			WC()->customer->set_is_vat_exempt( true );
		} else {
			if ( ! empty( WC()->customer ) ) {
				WC()->customer->set_is_vat_exempt( false );
			}
		}
	}

	/**
	 * checkout_validate_vat.
	 *
	 * @version 4.6.1
	 */
	function checkout_validate_vat( $_posted ) {
		if ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_validate', 'yes' ) ) {
			if (
				( 'yes' === wcj_get_option( 'wcj_eu_vat_number_field_required', 'no' ) && '' == $_posted['billing_eu_vat_number'] ) ||
				(
					'' != $_posted['billing_eu_vat_number'] ) &&
				'1' !== $this->wcj_validate_eu_vat_number( array( 'wcj_eu_vat_number_to_check' => $_posted['billing_eu_vat_number'], 'echo' => false )
				)
			) {
				wc_add_notice(
					get_option( 'wcj_eu_vat_number_not_valid_message', __( '<strong>EU VAT Number</strong> is not valid.', 'woocommerce-jetpack' ) ),
					'error'
				);
			}
		}
	}

	/**
	 * add_billing_eu_vat_number_field_to_admin_order_display.
	 *
	 * @version 4.6.0
	 */
	function add_billing_eu_vat_number_field_to_admin_order_display( $fields ) {
		$vat_number = '';

		$fields[ $this->id ] = array(
			'type'  => 'text',
			'label' => wcj_get_option( 'wcj_eu_vat_number_field_label' ),
			'show'  => true
		);

		// Try to read meta from 'vat_number' if '_billing_eu_vat_number' is empty
		if ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_read_vat_number_meta', 'no' ) ) {
			global $post;
			$order = wc_get_order( $post->ID );
			if ( is_a( $order, 'WC_Order' ) ) {
				$metas = array( '_billing_eu_vat_number', '_vat_number', '_billing_vat_number' );
				foreach ( $metas as $meta ) {
					$vat_number = get_post_meta( $order->get_id(), $meta, true );
					if ( ! empty( $vat_number ) ) {
						break;
					}
				}
				$fields[ $this->id ]['value'] = $vat_number;
			}
		}

		return $fields;
	}

	/**
	 * add_eu_vat_number_checkout_field_to_frontend.
	 *
	 * @version 3.9.0
	 */
	function add_eu_vat_number_checkout_field_to_frontend( $fields ) {
		$fields['billing'][ 'billing_' . $this->id ] = array(
			'type'              => 'text',
			'label'             => wcj_get_option( 'wcj_eu_vat_number_field_label' ),
			'description'       => wcj_get_option( 'wcj_eu_vat_number_field_description' ),
			'placeholder'       => wcj_get_option( 'wcj_eu_vat_number_field_placeholder' ),
			'required'          => ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_field_required', 'no' ) ),
			'custom_attributes' => array(),
			'clear'             => ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_field_clear', 'yes' ) ),
			'class'             => array( wcj_get_option( 'wcj_eu_vat_number_field_class', 'form-row-wide' ) ),
			'validate'          => ( 'yes' === wcj_get_option( 'wcj_eu_vat_number_validate', 'yes' ) ) ? array( 'eu-vat-number' ) : array(),
		);
		return $fields;
	}

}

endif;

return new WCJ_EU_VAT_Number();
