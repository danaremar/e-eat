<?php
/**
 * FoodStore services related functions and actions.
 *
 * @package FoodStore/Classes
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

class WFS_Services {

	public $default_type;

    /** 
     * Constructor of Services Class
     */
    public function __construct() {

        $this->default_type = wfs_get_default_service_type();
        
        $checkout_option    = get_option( '_wfs_enable_checkout_fields', 'yes' );

        add_action( 'init', array( $this, 'set_default_cookies') );
        
        if( wfs_is_service_enabled() && 'yes' == $checkout_option ) {

            add_action( 'wp_enqueue_scripts', array( $this, 'checkout_enqueue' ) );
            add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'checkout_fields' ) );
            add_action( 'woocommerce_checkout_process', array( $this, 'process_checkout_fields' ) );
        }
            
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_services_meta' ) );
        
        if( wfs_is_service_enabled() ) {
            add_action( 'woocommerce_order_details_after_order_table_items', array( $this, 'receipt_services_meta') );
            add_action( "woocommerce_email_after_order_table", array ($this, 'email_services_meta'), 10, 3 );
            add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'admin_services_meta' ) );
            add_filter( 'manage_edit-shop_order_columns', array( $this , 'wfs_order_services_column' ), 99 );
            add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wfs_order_services_data' ), 10 );
        }
    }

    /**
     * Set default cookies depending upon admin settings.
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function set_default_cookies() {

        $service_modal_option   = get_option( '_wfs_service_modal_option', 'auto' );
        $time_format            = get_option( 'time_format', true );

        if( ! isset( $_COOKIE['service_type'] ) && 'auto' == $service_modal_option ) {
            
            // Update Service Type Cookie
            setcookie( 'service_type', $this->default_type, time() + 1800, COOKIEPATH, COOKIE_DOMAIN );
        }

        if( ! isset( $_COOKIE['service_date'] ) ) {

            $service_type = isset( $_COOKIE['service_type'] ) ? $_COOKIE['service_type'] : $this->default_type;
            $current_time = current_time( 'timestamp' );
            $service_date = date_i18n( 'Y-m-d', $current_time );
            $service_date = apply_filters( 'wfs_get_auto_available_date', $service_date, $service_type );

            // Update Service Date Cookie
            setcookie( 'service_date', $service_date, time() + 1800, COOKIEPATH, COOKIE_DOMAIN );
        
        } else {
            
            $service_date = $_COOKIE['service_date'];
        }

        if( ! empty( $service_date ) && ! isset( $_COOKIE['service_time'] ) && 'auto' == $service_modal_option ) {

            $service_type   = isset( $_COOKIE['service_type'] ) ? $_COOKIE['service_type'] : $this->default_type;
            $service_times  = wfs_get_store_timing( $service_type );

            if( ! empty( $service_times ) ) {

                $service_time   = date_i18n( $time_format, $service_times[0] );
                $service_time   = apply_filters( 'wfs_get_auto_available_time', $service_time, $service_date, $service_type );

                // Update Service Time Cookie
                setcookie( 'service_time', $service_time, time() + 1800, COOKIEPATH, COOKIE_DOMAIN );
            }
        }
    }

    /**
     * Redirect based on service type scripts in checkout page.
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function checkout_enqueue() {

        if( ! apply_filters( 'wfs_checkout_show_services', true ) ) 
            return;

        global $woocommerce;

        $checkout_url   = wc_get_checkout_url();
        $pickup_url     = $checkout_url . '?type=pickup';
        $delivery_url   = $checkout_url . '?type=delivery';

        if( is_checkout() ) {

            wp_enqueue_script('jquery-ui-datepicker');

            $service_html = 'jQuery(function($) {

                $("input[name=\'wfs_service_type\']").on("change",function() {
                    if($("input[name=\'wfs_service_type\']:checked").val() == "pickup") {
                        window.location = "'.$pickup_url.'";
                    } else if($("input[name=\'wfs_service_type\']:checked").val() == "delivery") {
                        window.location = "'.$delivery_url.'";
                    }
                });
            });';

            wp_add_inline_script( 'jquery-ui-datepicker', $service_html );
        }
    }

    /**
     * Show available service type and time options in 
     * Checkout page. loaded from cookies if set already.
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function checkout_fields() {

        if( ! apply_filters( 'wfs_checkout_show_services', true ) ) 
            return;

        if( isset( $_GET['type']) && $_GET['type'] != '' ) {
            $service_type = $_GET['type'];
            setcookie( 'service_type', $service_type, time() + 1800, COOKIEPATH, COOKIE_DOMAIN );
        } else {
            $service_type = isset( $_COOKIE['service_type'] ) ? $_COOKIE['service_type'] : $this->default_type;
        }

        $service_time = isset( $_COOKIE['service_time'] ) ? $_COOKIE['service_time'] : '';
        $service_date = wfs_get_default_service_date();
        $show_service = wfs_get_available_services();

        echo '<div id="wfs_checkout_fields">';
        do_action( 'wfs_before_checkout_fields' );

        if( $show_service == 'all' ) {

            woocommerce_form_field( 'wfs_service_type', array(
                'type'          => 'radio',
                'required'      =>'true',
                'class'         => array('wfs_co_service_type'),
                'label'         => __('Service Type','food-store'),
                'default'       => $service_type,
                'checked'       => 'checked',
                'options'       => array(
                    'pickup'        => wfs_get_service_label('pickup'),
                    'delivery'      => wfs_get_service_label('delivery'),
                )
            ));
        
        } else if( $show_service == 'delivery' ) {

            woocommerce_form_field( 'wfs_service_type', array(
                'type'          => 'radio',
                'required'      =>'true',
                'class'         => array( 'wfs_co_service_type delivery_only' ),
                'label'         => __( 'Service Type', 'food-store' ),
                'default'       => 'delivery',
                'checked'       => 'checked',
                'options'       => array(
                    'delivery'      => wfs_get_service_label('delivery'),
                )
            ));
        
        } else if( $show_service == 'pickup' ) {

            woocommerce_form_field( 'wfs_service_type', array(
                'type'          => 'radio',
                'required'      =>'true',
                'class'         => array('wfs_co_service_type pickup_only' ),
                'label'         => __( 'Service Type', 'food-store' ),
                'default'       => 'pickup',
                'checked'       => 'checked',
                'options'       => array(
                    'pickup'        => wfs_get_service_label('pickup'),
                )
            ));
        }

        woocommerce_form_field( 'wfs_service_date', array(
            'type'          => 'select',
            'required'      => 'true',
            'default'       => $service_date,
            'class'         => array( 'wfs_co_service_date' ),
            'input_class'   => array( 'input-text' ),
            'label'         => __( 'Date', 'food-store' ),
            'options'       => $this->get_service_dates( $service_type )
        ));

        woocommerce_form_field( 'wfs_service_time', array(
            'type'          => 'select',
            'required'      => 'true',
            'default'       => $service_time,
            'class'         => array( 'wfs_co_service_time' ),
            'input_class'   => array( 'input-text' ),
            'label'         => __( 'Time', 'food-store' ),
            'options'       => $this->get_service_times( $service_type )
        ));

        do_action( 'wfs_after_checkout_fields' );
        echo '</div>';
    }

    /**
     * Get the list of available service dates
     *
     * @author FoodStore
     * @since 1.1
     * @return arr $date_range
     */
    public function get_service_dates( $service_type ) {

        $current_date = date_i18n( 'Y-m-d');
        $preorder_date = date_i18n( 'Y-m-d', strtotime( '+1 days' ) );

        $date_range = $formatted_date = $raw_date = [];
        $date_range = $this->create_date_range( $current_date, $preorder_date );

        $date_range = apply_filters( 'wfs_checkout_date_range', $date_range, $service_type  );

        return $date_range;
    }

    /**
     * Prepare the date range from available dates 
     *
     * @author FoodStore
     * @since 1.1
     * @return arr $range
     */
    public function create_date_range( $startDate, $endDate ) {

        $begin      = new DateTime( $startDate );
        $end        = new DateTime( $endDate );

        $interval   = new DateInterval('P1D');
        $date_range = new DatePeriod( $begin, $interval, $end );

        $range = [];

        foreach( $date_range as $date ) {

            $raw_date = $date->format( 'Y-m-d' );
            $formatted_date = $date->format( get_option('date_format') );
            $range[$raw_date] = $formatted_date;
        }

        return $range;
    }

    /**
     * Get available service time strings based on admin settings
     *
     * @author FoodStore
     * @since 1.1
     * @return arr $timings
     */
    public function get_service_times( $service_type ) {

      $get_store_hours  = wfs_get_store_timing( $service_type );
      $time_format      = wfs_get_store_time_format();

      $timings = [];

      if ( !empty( $get_store_hours ) && is_array( $get_store_hours ) ) :
        foreach( $get_store_hours as $store_time ) :
          $store_time = date( $time_format, $store_time ); 
          $timings[$store_time] = $store_time;
        endforeach;
      endif;

      $service_date = wfs_get_default_service_date();
      $timings = apply_filters( 'wfs_render_checkout_service_timings', $timings, $service_type, $service_date );

      return $timings;
    }

    /**
     * Check for Service type and Time fields when order is processed
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function process_checkout_fields() {

        if( ! apply_filters( 'wfs_checkout_show_services', true ) ) 
            return; // Return as the fields are hidden at the 1st place.

        if ( empty( $_POST['wfs_service_date'] ) || ! isset( $_POST['wfs_service_date'] ) )
            wc_add_notice( __( 'Please choose a date for your Order.', 'food-store' ), 'error' );

        if ( empty( $_POST['wfs_service_time'] ) || ! isset( $_POST['wfs_service_time'] ) )
            wc_add_notice( __( 'Please choose a time for your Order.', 'food-store' ), 'error' );
    }

    /**
     * Save service details to Order Meta
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function save_services_meta( $order_id ) {

        // Save Service Type
        $wfs_service_type = ! empty( $_POST['wfs_service_type'] ) ? sanitize_text_field( $_POST['wfs_service_type'] ) : $_COOKIE['service_type'];
        update_post_meta( $order_id, '_wfs_service_type', $wfs_service_type );

        // Save Service Date
        $wfs_service_date = ! empty( $_POST['wfs_service_date'] ) ? sanitize_text_field( $_POST['wfs_service_date'] ) : $_COOKIE['service_date'];
        update_post_meta( $order_id, '_wfs_service_date', $wfs_service_date );

        // Save Service Time
        $wfs_service_time = ! empty( $_POST['wfs_service_time'] ) ? sanitize_text_field( $_POST['wfs_service_time'] ) : $_COOKIE['service_time'];
        update_post_meta( $order_id, '_wfs_service_time', $wfs_service_time );

        // Empty the session for Service
        unset( $_COOKIE['service_type'] );
        unset( $_COOKIE['service_date'] );
        unset( $_COOKIE['service_time'] );

        // Unset Cookies
        setcookie( 'service_type', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'service_date', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'service_time', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
    }

    /**
     * Display service details in Order Receipt from order meta
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function receipt_services_meta( $order ) {

        if( ! apply_filters( 'wfs_receipt_show_services', true ) ) 
            return;

        $order_id       = version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id();
        $service_type   = get_post_meta( $order_id, '_wfs_service_type', true );
        $service_date   = get_post_meta( $order_id, '_wfs_service_date', true );
        $service_time   = get_post_meta( $order_id, '_wfs_service_time', true );

        $date_format    = get_option('date_format');

        echo '<tr>';
        echo '<td><strong>' . __( 'Service Type:', 'food-store' ) .'</strong></td>';
        echo '<td><strong>' . wfs_get_service_label( $service_type ) .'</strong></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>' . __( 'Date:', 'food-store') . '</strong></td>';
        echo '<td><strong>' . date_i18n( $date_format, strtotime( $service_date ) ) . '</strong></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>' . __( 'Time:', 'food-store' ) . '</strong></td>';
        echo '<td><strong>' . $service_time . '</strong></td>';
        echo '</tr>';
    }

    /**
     * Display service details in Email Notification from order meta
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function email_services_meta( $order, $sent_to_admin, $plain_text ) {

        if( ! apply_filters( 'wfs_email_notification_show_services', true ) ) 
            return;

        $order_id       = version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id();
        $service_type   = get_post_meta( $order_id, '_wfs_service_type', true );
        $service_date   = get_post_meta( $order_id, '_wfs_service_date', true );
        $service_time   = get_post_meta( $order_id, '_wfs_service_time', true );

        $date_format    = get_option('date_format');

        if( $plain_text ) {
            
            echo "\n";
            echo __('Service Type:','food-store'). " " . wfs_get_service_label( $service_type ) . "\n";
            echo __('Date:','food-store') . " " . date_i18n( $date_format, strtotime( $service_date ) ) . "\n";
            echo __('Time:','food-store') . " " . $service_time . "\n";
        
        } else {

            echo '<div style="margin-bottom: 40px;">';
            echo '<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: Helvetica, Roboto, Arial, sans-serif;" border="1">';
            echo '<tbody>';
            echo '<tr>';
            echo '<td><strong>' . __( 'Service Type:', 'food-store' ) .'</strong></td>';
            echo '<td><strong>' . wfs_get_service_label( $service_type ) .'</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td><strong>' . __( 'Date:', 'food-store') . '</strong></td>';
            echo '<td><strong>' . date_i18n( $date_format, strtotime( $service_date ) ) . '</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td><strong>' . __( 'Time:', 'food-store' ) . '</strong></td>';
            echo '<td><strong>' . $service_time . '</strong></td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }
    }

    /**
     * Display service details in admin order from order meta
     *
     * @author FoodStore
     * @since 1.1
     * @return void
     */
    public function admin_services_meta( $order ) {

        if( ! apply_filters( 'wfs_admin_order_detail_show_services', true ) ) 
            return;

        $order_id       = version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id();
        $service_type   = get_post_meta( $order_id, '_wfs_service_type', true );
        $service_date   = get_post_meta( $order_id, '_wfs_service_date', true );
        $service_time   = get_post_meta( $order_id, '_wfs_service_time', true );

        $date_format    = get_option('date_format');

        echo '<p>';
        echo '<strong>'.__('Service Type:','food-store').'</strong> ' . wfs_get_service_label( $service_type ) . '</br>';
        echo '<strong>'.__('Date:','food-store').'</strong> ' . date_i18n( $date_format, strtotime( $service_date ) ) . '</br>';
        echo '<strong>'.__('Time:','food-store').'</strong> ' . $service_time;
        echo '</p>';
    }

    /**
     * Setup service columns for Order Listing Page
     *
     * @author WP Scripts
     * @since 1.1
     * @param array $columns
     *
     * @return array $columns
     */
    public function wfs_order_services_column( $columns ) {

        if( ! apply_filters( 'wfs_admin_columns_show_services', true ) ) 
            return $columns;

        $columns['service_type'] = __( 'Service Type', 'food-store' );
        $columns['service_time'] = __( 'Service Time', 'food-store' );
    
        return $columns;
    }

   /** 
    * Add services data to the services columns
    * mentioned in Order Listing page
    *
    * @author WP Scripts
    * @since 1.1
    * @param array $columns
    */
    public function wfs_order_services_data( $column ) {

        if( ! apply_filters( 'wfs_admin_columns_show_services', true ) ) 
            return;
    
        global $post;

        $order_id = $post->ID;

        if ( 'service_type' === $column ) {
            $service_type = get_post_meta( $order_id, '_wfs_service_type', true );
            $service_type = !empty( $service_type ) ? $service_type : 'pickup';
            echo wfs_get_service_label($service_type);
        }

        if ( 'service_time' === $column ) {
            
            $service_time = get_post_meta( $order_id, '_wfs_service_time', true );
            
            if ( empty( $service_time ) ) {
                
                $time_format  = wfs_get_store_time_format();
                $service_time = get_the_time( 'U', $order_id );
                $service_time = date_i18n( $time_format, $service_time );
            }
            echo $service_time;
        }
    }
}

new WFS_Services();