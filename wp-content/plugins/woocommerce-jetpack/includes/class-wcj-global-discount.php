<?php
/**
 * Booster for WooCommerce - Module - Global Discount
 *
 * @version 5.2.0
 * @since   2.5.7
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Global_Discount' ) ) :

class WCJ_Global_Discount extends WCJ_Module {

	private $wcj_global_options = array();

	/**
	 * Constructor.
	 *
	 * @version 5.2.0
	 * @since   2.5.7
	 * @todo    fee instead of discount
	 * @todo    regular price coefficient
	 */
	function __construct() {

		$this->id         = 'global_discount';
		$this->short_desc = __( 'Global Discount', 'woocommerce-jetpack' );
		$this->desc       = __( 'Add global discount to all products (1 discount group allowed in free version).', 'woocommerce-jetpack' );
		$this->desc_pro   = __( 'Add global discount to all products.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-shop-global-discount';
		parent::__construct();

		if ( $this->is_enabled() ) {
			$this->price_hooks_priority = wcj_get_module_price_hooks_priority( 'global_discount' );
			if ( 'yes' === wcj_get_option( 'wcj_global_discount_enabled_in_admin', 'no' ) || wcj_is_frontend() ) {
				wcj_add_change_price_hooks( $this, $this->price_hooks_priority, false );
			}

			add_action( 'admin_init', array( $this, 'regenerate_wcj_sale_products_in_cache' ) );
			add_filter( 'woocommerce_shortcode_products_query', array( $this, 'add_wcj_sale_ids_to_products_shortcode' ), 10, 3 );
		}

	}

	/**
	 * add_wcj_sale_ids_to_products_shortcode.
	 *
	 * @version 4.8.0
	 * @since   4.8.0
	 *
	 * @param $args
	 * @param $atts
	 * @param $type
	 *
	 * @return mixed
	 */
	function add_wcj_sale_ids_to_products_shortcode( $args, $atts, $type ) {
		if (
			'sale_products' != $type ||
			'yes' != wcj_get_option( 'wcj_global_discount_products_shortcode_compatibility', 'no' )
		) {
			return $args;
		}

		$prev_post__in = isset( $args['post__in'] ) ? $args['post__in'] : array();
		$args['post__in'] = array_merge( $prev_post__in, $this->get_wcj_sale_products() );
		return $args;
	}

	/**
	 * regenerate_wcj_sale_products_in_cache.
	 *
	 * @version 4.8.0
	 * @since   4.8.0
	 */
	function regenerate_wcj_sale_products_in_cache() {
		if (
			'yes' != wcj_get_option( 'wcj_global_discount_products_shortcode_compatibility', 'no' ) ||
			! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ||
			! isset( $_REQUEST['tab'] ) || 'jetpack' !== $_REQUEST['tab'] ||
			! isset( $_REQUEST['wcj-cat'] ) || 'prices_and_currencies' !== $_REQUEST['wcj-cat'] ||
			! isset( $_REQUEST['section'] ) || 'global_discount' !== $_REQUEST['section'] ||
			! isset( $_POST['save'] )
		) {
			return;
		}
		$this->clear_wcj_sale_products_from_cache();
		$this->get_wcj_sale_products();
	}

	/**
	 * get_wcj_sale_products.
	 *
	 * @version 4.8.0
	 * @since   4.8.0
	 *
	 * @return array|mixed
	 */
	function get_wcj_sale_products() {
		$transient_name = 'wcj_global_discount_sale_products';
		$sale_products  = get_transient( $transient_name );
		if ( false === $sale_products ) {
			$args         = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			);
			$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_global_discount_groups_total_number', 1 ) );
			for ( $i = 1; $i <= $total_number; $i ++ ) {
				$enabled = wcj_get_option( 'wcj_global_discount_sale_enabled_' . $i, 'yes' );
				if ( 'yes' !== $enabled ) {
					continue;
				}

				// Categories
				$include_cats = wcj_get_option( 'wcj_global_discount_sale_categories_incl_' . $i, array() );
				$exclude_cats = wcj_get_option( 'wcj_global_discount_sale_categories_excl_' . $i, array() );
				$cats         = array();
				if ( ! empty( $include_cats ) || ! empty( $exclude_cats ) ) {
					$cats = array(
						'relation' => 'AND',
					);
					if ( ! empty( $include_cats ) ) {
						$cats[] = array(
							'taxonomy'         => 'product_cat',
							'field'            => 'term_id',
							'include_children' => false,
							'terms'            => $include_cats
						);
					}
					if ( ! empty( $exclude_cats ) ) {
						$cats[] = array(
							'taxonomy'         => 'product_cat',
							'field'            => 'term_id',
							'include_children' => false,
							'terms'            => $exclude_cats,
							'operator'         => 'NOT IN'
						);
					}
				}

				// Tags
				$include_tags = wcj_get_option( 'wcj_global_discount_sale_tags_incl_' . $i, array() );
				$exclude_tags = wcj_get_option( 'wcj_global_discount_sale_tags_excl_' . $i, array() );
				$tags         = array();
				if ( ! empty( $include_tags ) || ! empty( $exclude_tags ) ) {
					$tags = array(
						'relation' => 'AND',
					);
					if ( ! empty( $include_tags ) ) {
						$tags[] = array(
							'taxonomy' => 'product_tag',
							'field'    => 'term_id',
							'terms'    => $include_tags
						);
					}
					if ( ! empty( $exclude_tags ) ) {
						$tags[] = array(
							'taxonomy' => 'product_tag',
							'field'    => 'term_id',
							'terms'    => $exclude_tags,
							'operator' => 'NOT IN'
						);
					}
				}

				// Tax Query
				if ( ! empty( $cats ) || ! empty( $tags ) ) {
					$args['tax_query'] = array(
						'relation' => 'AND'
					);
					if ( ! empty( $cats ) ) {
						$args['tax_query'][] = $cats;
					}
					if ( ! empty( $tags ) ) {
						$args['tax_query'][] = $tags;
					}
				}

				// Products
				$products_incl = wcj_get_option( 'wcj_global_discount_sale_products_incl_' . $i, array() );
				$products_excl = wcj_get_option( 'wcj_global_discount_sale_products_excl_' . $i, array() );
				if ( ! empty( $products_incl ) || ! empty( $products_excl ) ) {
					if ( ! empty( $products_incl ) ) {
						$args['post__in'] = $products_incl;
					}
					if ( ! empty( $products_excl ) ) {
						$args['post__not_in'] = $products_excl;
					}
				}

				// Scope
				$scope = wcj_get_option( 'wcj_global_discount_sale_product_scope_' . $i, 'all' );
				if ( 'all' != $scope ) {
					$wc_sale_products = wc_get_product_ids_on_sale();
					if ( 'only_on_sale' == $scope ) {
						$args['post__in'] = $wc_sale_products;
						if ( empty( $wc_sale_products ) ) {
							$args['post_type'] = 'do_not_search';
						}
					} elseif ( 'only_not_on_sale' == $scope ) {
						$args['post__not_in'] = $wc_sale_products;
					}
				}

				$query                    = new WP_Query( $args );
				$sale_products            = array_unique( $query->posts );
				$prev_group_sale_products = get_transient( $transient_name );
				if ( false !== $prev_group_sale_products ) {
					$sale_products = array_unique( array_merge( $prev_group_sale_products, $sale_products ) );
				}

				set_transient( $transient_name, $sale_products, YEAR_IN_SECONDS );
			}
		}
		return $sale_products;
	}

	/**
	 * clear_wcj_sale_products_from_cache.
	 *
	 * @version 4.8.0
	 * @since   4.8.0
	 */
	function clear_wcj_sale_products_from_cache() {
		delete_transient( 'wcj_global_discount_sale_products' );
	}

	/**
	 * change_price.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 * @todo    `WCJ_PRODUCT_GET_REGULAR_PRICE_FILTER, 'woocommerce_variation_prices_regular_price', 'woocommerce_product_variation_get_regular_price'`
	 */
	function change_price( $price, $_product ) {
		$_current_filter = current_filter();
		if ( in_array( $_current_filter, array( WCJ_PRODUCT_GET_PRICE_FILTER, 'woocommerce_variation_prices_price', 'woocommerce_product_variation_get_price' ) ) ) {
			return $this->add_global_discount( $price, $_product, 'price' );
		} elseif ( in_array( $_current_filter, array( WCJ_PRODUCT_GET_SALE_PRICE_FILTER, 'woocommerce_variation_prices_sale_price', 'woocommerce_product_variation_get_sale_price' ) ) ) {
			return $this->add_global_discount( $price, $_product, 'sale_price' );
		} else {
			return $price;
		}
	}

	/**
	 * change_price_grouped.
	 *
	 * @version 3.1.0
	 * @since   2.5.7
	 */
	function change_price_grouped( $price, $qty, $_product ) {
		if ( $_product->is_type( 'grouped' ) ) {
			foreach ( $_product->get_children() as $child_id ) {
				$the_price   = get_post_meta( $child_id, '_price', true );
				$the_product = wc_get_product( $child_id );
				$the_price   = wcj_get_product_display_price( $the_product, $the_price, 1 );
				if ( $the_price == $price ) {
					return $this->add_global_discount( $price, $the_product, 'price' );
				}
			}
		}
		return $price;
	}

	/**
	 * calculate_price.
	 *
	 * @version 3.8.0
	 * @since   2.5.7
	 */
	function calculate_price( $price, $coefficient, $group ) {
		if ( '' === $price ) {
			return $price;
		}
		$return_price = ( 'percent' === wcj_get_option( 'wcj_global_discount_sale_coefficient_type_' . $group, 'percent' ) ) ?
			( $price + $price * ( $coefficient / 100 ) ) :
			( $price + $coefficient );
		$return_price = ( $return_price >= 0 ? $return_price : 0 );
		if ( 'none' != ( $final_correction_function = wcj_get_option( 'wcj_global_discount_sale_final_correction_func_' . $group, 'none' ) ) ) {
			$final_correction_coef = wcj_get_option( 'wcj_global_discount_sale_final_correction_coef_' . $group, 1 );
			$return_price          = $final_correction_function( $return_price / $final_correction_coef ) * $final_correction_coef;
		}
		return $return_price;
	}

	/**
	 * check_if_applicable.
	 *
	 * @version 3.1.0
	 * @since   2.5.7
	 * @return  bool
	 */
	function check_if_applicable( $_product, $group ) {
		return ( 'yes' === wcj_get_option( 'wcj_global_discount_sale_enabled_' . $group, 'yes' ) && $this->is_enabled_for_product( $_product, $group ) );
	}

	/**
	 * is_enabled_for_product.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function is_enabled_for_product( $_product, $group ) {
		$product_id = wcj_get_product_id_or_variation_parent_id( $_product );
		return wcj_is_enabled_for_product( $product_id, array(
			'include_products'   => wcj_get_option( 'wcj_global_discount_sale_products_incl_'   . $group, '' ),
			'exclude_products'   => wcj_get_option( 'wcj_global_discount_sale_products_excl_'   . $group, '' ),
			'include_categories' => wcj_get_option( 'wcj_global_discount_sale_categories_incl_' . $group, '' ),
			'exclude_categories' => wcj_get_option( 'wcj_global_discount_sale_categories_excl_' . $group, '' ),
			'include_tags'       => wcj_get_option( 'wcj_global_discount_sale_tags_incl_'       . $group, '' ),
			'exclude_tags'       => wcj_get_option( 'wcj_global_discount_sale_tags_excl_'       . $group, '' ),
		) );
	}

	/**
	 * check_if_applicable_by_product_scope.
	 *
	 * @version 3.9.0
	 * @since   3.1.0
	 */
	function check_if_applicable_by_product_scope( $_product, $price, $price_type, $scope ) {
		$return = true;
		if ( 'sale_price' === $price_type ) {
			if ( 0 == $price ) {
				// The product is currently not on sale
				if ( 'only_on_sale' === $scope ) {
					$return = false;
				}
			} else {
				// The product is currently on sale
				if ( 'only_not_on_sale' === $scope ) {
					$return = false;
				}
			}
		} else { // if ( 'price' === $price_type )
			wcj_remove_change_price_hooks( $this, $this->price_hooks_priority, false );
			if ( 'only_on_sale' === $scope && 0 == $_product->get_sale_price() ) {
				$return = false;
			} elseif ( 'only_not_on_sale' === $scope && 0 != $_product->get_sale_price() ) {
				$return = false;
			}
			wcj_add_change_price_hooks( $this, $this->price_hooks_priority, false );
		}
		return $return;
	}

	/**
	 * add_global_discount.
	 *
	 * @version 3.1.0
	 * @since   2.5.7
	 */
	function add_global_discount( $price, $_product, $price_type ) {
		if ( 'price' === $price_type && '' === $price ) {
			return $price; // no changes
		}
		$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_global_discount_groups_total_number', 1 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( ! $this->check_if_applicable( $_product, $i ) ) {
				continue; // no changes by current discount group
			}
			$coefficient = wcj_get_option( 'wcj_global_discount_sale_coefficient_' . $i, 0 );
			if ( 0 != $coefficient ) {
				if ( ! $this->check_if_applicable_by_product_scope( $_product, $price, $price_type, wcj_get_option( 'wcj_global_discount_sale_product_scope_' . $i, 'all' ) ) ) {
					continue; // no changes by current discount group
				}
				if ( 'sale_price' === $price_type && 0 == $price ) {
					$price = $_product->get_regular_price();
				}
				return $this->calculate_price( $price, $coefficient, $i ); // discount applied
			}
		}
		return $price; // no changes
	}

	/**
	 * get_global_discount_options.
	 *
	 * @version 4.8.0
	 * @since   4.8.0
	 *
	 * @return array
	 */
	function get_global_discount_options() {
		$options = $this->wcj_global_options;
		if ( empty( $options ) ) {
			$total_number            = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_global_discount_groups_total_number', 1 ) );
			$options['total_number'] = $total_number;
			for ( $i = 1; $i <= $total_number; $i ++ ) {
				$options['enabled'][ $i ]     = wcj_get_option( 'wcj_global_discount_sale_enabled_' . $i, 'yes' );
				$options['type'][ $i ]        = wcj_get_option( 'wcj_global_discount_sale_coefficient_type_' . $i, 'percent' );
				$options['value'][ $i ]       = wcj_get_option( 'wcj_global_discount_sale_coefficient_' . $i, 0 );
				$options['scope'][ $i ]       = wcj_get_option( 'wcj_global_discount_sale_product_scope_' . $i, 'all' );
				$options['cats_in'][ $i ]     = wcj_get_option( 'wcj_global_discount_sale_categories_incl_' . $i, array() );
				$options['cats_ex'][ $i ]     = wcj_get_option( 'wcj_global_discount_sale_categories_excl_' . $i, array() );
				$options['tags_in'][ $i ]     = wcj_get_option( 'wcj_global_discount_sale_tags_incl_' . $i, array() );
				$options['tags_ex'][ $i ]     = wcj_get_option( 'wcj_global_discount_sale_tags_excl_' . $i, array() );
				$options['products_in'][ $i ] = wcj_get_option( 'wcj_global_discount_sale_products_incl_' . $i, array() );
				$options['products_ex'][ $i ] = wcj_get_option( 'wcj_global_discount_sale_products_excl_' . $i, array() );
			}
			$this->wcj_global_options = $options;
		}
		return $options;
	}

	/**
	 * get_variation_prices_hash.
	 *
	 * @version 4.9.0
	 * @since   2.5.7
	 */
	function get_variation_prices_hash( $price_hash, $_product, $display ) {
		$options = $this->get_global_discount_options();
		if ( is_array( $price_hash ) ) {
			$price_hash['wcj_global_discount_price_hash'] = $options;
		}
		return $price_hash;
	}

}

endif;

return new WCJ_Global_Discount();
