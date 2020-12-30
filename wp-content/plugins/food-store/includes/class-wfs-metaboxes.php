<?php
/**
 * Metaboxes
 *
 * Metaboxes for Post Type Product
 * Metaboxes for Taxonomy Product Category
 * Metaboxes for Taxonomy Product Addon
 *
 * @package FoodStore/Classes
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Metaboxes Class.
 */
class WFS_Metaboxes {

  /**
   * Hook in methods.
   */
  public static function init() {
    add_action( 'product_addon_edit_form_fields', array(  __CLASS__, 'addon_choice' ) );
    add_action( 'product_addon_edit_form_fields', array(  __CLASS__, 'addon_price' ) );
    add_action( 'edited_product_addon', array( __CLASS__, 'save_addon_options' ) );
    add_filter( 'woocommerce_allow_marketplace_suggestions', '__return_false' );
    add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'wfs_tab' ) );
    add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'wfs_tab_content' ) );
    add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_custom_meta' ), 10, 2 );
    add_action( 'product_type_selector', array( __CLASS__, 'wfs_product_type' ) );
    add_action( 'product_type_options', array( __CLASS__, 'wfs_product_options' ) );
    add_filter( 'woocommerce_products_admin_list_table_filters', array( __CLASS__, 'product_table_filter') );
  }

  /**
   * Creating selection option field for product addon.
   * 
   * @since 1.0.0
   * @param obj Term Object
   * @return void
   */
  public static function addon_choice( $term ) {

    if ( $term->parent !== '0' )
      return;

    $choice = get_term_meta( $term->term_id, '_wfs_addon_selection_option', true ); ?>
    
    <tr class="form-field">
      <th scope="row" valign="top">
        <label for="addon_selection">
          <?php esc_html_e( 'Selection Choice', 'food-store' ); ?>
        </label>
      </th>
      <td>
        <select name="addon_selection" required="required">
          <option <?php selected( $choice, 'single' ); ?> value="single"><?php esc_html_e( 'Single', 'food-store' ); ?></option>
          <option <?php selected( $choice, 'multiple' ); ?>  value="multiple"><?php esc_html_e( 'Multiple', 'food-store' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Please choose how you want this addon to be associated with the food item.', 'food-store' ); ?></p>
      </td>
    </tr> <?php
  }

  /**
   * Creating pricing field for product addon.
   * 
   * @since 1.0.0
   * @param obj Term Object
   * @return void
   */
  public static function addon_price( $term ) {

    if ( $term->parent == 0 )
      return;
    
    $price = get_term_meta( $term->term_id, '_wfs_addon_item_price', true ); ?>
    
    <tr class="form-field">
      <th scope="row" valign="top">
        <label for="addon_price"><?php esc_html_e( 'Price', 'food-store' ); ?></label>
      </th>
      <td>
        <input type="number" step=".01" name="addon_price" size="25" value="<?php echo $price; ?>" required />
        <p class="description"><?php esc_html_e( 'Add a Price for this addon item.', 'food-store' ); ?></p>
      </td>
    </tr> <?php
  }

  /**
   * Save custom options when a addon is saved
   * 
   * @since 1.0.0
   * @param int Term ID
   * @return void
   */
  public static function save_addon_options( $term_id ) {

    if ( isset( $_POST['addon_selection'] ) ) {
      update_term_meta( $term_id, '_wfs_addon_selection_option', sanitize_text_field( $_POST['addon_selection'] ) );
    }

    if ( isset( $_POST['addon_price'] ) ) {
      update_term_meta( $term_id, '_wfs_addon_item_price', sanitize_text_field( $_POST['addon_price'] ) );
    }
  }

  /**
   * Create new product data tab for Food options
   * 
   * @since 1.0.0
   * @param arr List of Existing Tabs
   * @return void
   */
  public static function wfs_tab( $tabs ) {

    // Add custom tab for food options
    $tabs['food-options'] = array(
      'label'    => __( 'Food Options', 'food-store' ),
      'target'   => 'food_product_options',
      'class'    => '',
      'priority' => 16,
    );

    return $tabs;
  }

  /**
   * Tab content area for the new tab created for Food options
   * 
   * @since 1.0.0
   * @return void
   */
  public static function wfs_tab_content() {
    include_once dirname( __FILE__ ) . '/admin/views/html-product-data-food-options.php';
  }

  /**
   * Save custom meta fields when product is saved
   * 
   * @since 1.0.0
   * @param int Post ID
   * @param obj Post Object
   * @return void
   */
  public static function save_custom_meta( $post_id, $post ) {
    update_post_meta( $post_id, '_wfs_variation_price_label', sanitize_text_field( $_POST['_wfs_variation_price_label'] ) );
    update_post_meta( $post_id, '_wfs_food_item_type', sanitize_text_field( $_POST['_wfs_food_item_type'] ) );
    update_post_meta( $post_id, '_wfs_disable_instruction', sanitize_text_field( $_POST['_wfs_disable_instruction'] ) );
  }

  /**
   * Hide other product type like grouped, external depending
   * on the Advanced settigns chosen by administrator
   * 
   * @since 1.0.0
   * @param arr List of types
   * @return void
   */
  public static function wfs_product_type( $types ) {

    $other_types_setting = get_option( '_wfs_adv_keep_other_product_types' );
    if( empty($other_types_setting) || $other_types_setting == 'no' ):
      unset( $types['grouped'] );
      unset( $types['external'] );
    endif;

    return $types;
  }

  /**
   * Remove product options like downloadable, Virtual
   * 
   * @since 1.0.0
   * @param arr Array of options available
   * @return void
   */
  public static function wfs_product_options( $options ) {
    
    $other_types_setting = get_option( '_wfs_adv_keep_other_product_types' );
    if( empty($other_types_setting) || $other_types_setting == 'no' ):
    
      if( isset( $options[ 'virtual' ] ) ) {
        unset( $options[ 'virtual' ] );
      }
      if( isset( $options[ 'downloadable' ] ) ) {
        unset( $options[ 'downloadable' ] );
      }
    endif;
   
    return $options;
  }

  /**
   * Remove other product types from product listing filter
   * 
   * @since 1.0.0
   * @param arr Array of Existing Filters
   * @return void
   */
  public static function product_table_filter( $filters ) {

    $other_types_setting = get_option( '_wfs_adv_keep_other_product_types' );
    if( empty($other_types_setting) || $other_types_setting == 'no' ): 

      if( isset( $filters[ 'product_type' ] ) ) {
        $filters[ 'product_type' ] = array( __CLASS__, 'product_type_filter_callback' );
      }
    endif;
    
    return $filters;
  }

  /**
   * Call bac function to remove other product options 
   * from the product filters dropdown
   * 
   * @since 1.0.0
   * @param int Term ID
   * @return void
   */
  public static function product_type_filter_callback() {

    $current_product_type = isset( $_REQUEST['product_type'] ) ? wc_clean( wp_unslash( $_REQUEST['product_type'] ) ) : false;
    $output = '<select name="product_type" id="dropdown_product_type"><option value="">Filter by product type</option>';
   
    foreach ( wc_get_product_types() as $value => $label ) {
      $output .= '<option value="' . esc_attr( $value ) . '" ';
      $output .= selected( $value, $current_product_type, false );
      $output .= '>' . esc_html( $label ) . '</option>';
    }
   
    $output .= '</select>';
    echo $output;
  }
}

WFS_Metaboxes::init();