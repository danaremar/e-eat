<?php
/**
* This class contains some utilities needed for the plugin.
*
* @since     1.1.0

*/
class Glf_Mor_Utils{
    public static function glf_mor_get_shortcode($ruid, $type, $useCustomCss=false, $class="") {
        $code = '[';

        $code .= $type == 'reservations' ? 'restaurant-reservations' : 'restaurant-menu-and-ordering';
        $code .= ' ruid="' . $ruid . '"';

        if($useCustomCss) {
            $code .= ' class="' . $class . '"';
        }

        $code .= ']';
        return $code;
    }
    public static function glf_check_glf_wordpress_options(){
        $glf_wordpress_options = get_option( 'glf_wordpress_options' );
        if( !$glf_wordpress_options ) {

        }
    }
    public static function glf_custom_css_check_and_set_defaults( $default_css ){
        $restaurant_data_obj = get_option('glf_mor_restaurant_data');
        if( is_object($restaurant_data_obj) && !isset($restaurant_data_obj->restaurants)){
            return;
        }
        /*
         * Checking to see if WordPress options exist.
         * If it doesn't exist we create a new Object and add our custom_css key
         * */
        $glf_wordpress_options = get_option( 'glf_wordpress_options' );
        if( !$glf_wordpress_options ) {
            $glf_wordpress_options = new stdClass();
            /*
             * Backward compatibility check
             * Use the 'glf_mor_restaurant_data->location_custom_css' data if it exists
             * */
            $glf_wordpress_options->custom_css_by_location = ( isset($restaurant_data_obj->location_custom_css) ? $restaurant_data_obj->location_custom_css : array() );
        }
        // use the new $glf_wordpress_options->custom_css_by_location to update the default value
        $update_location_custom_css = array();
        foreach ($restaurant_data_obj->restaurants as $restaurant) {
            if( !isset( $glf_wordpress_options->custom_css_by_location[$restaurant->uid] ) ) {
                $custom_css = $default_css;
            } else {
                $custom_css = $glf_wordpress_options->custom_css_by_location[$restaurant->uid];
            }
            $update_location_custom_css[$restaurant->uid] = $custom_css;
        }
        $glf_wordpress_options->custom_css_by_location = $update_location_custom_css;
        update_option('glf_wordpress_options', $glf_wordpress_options);
    }

    /**
     *
     * Get all locations or just one location custom_css
     * @param string $location_uid
     * @return array|string|null
     */
    public static function glf_get_locations_custom_css( $location_uid='' ){
        $glf_wordpress_options = get_option( 'glf_wordpress_options' );
        if( !$glf_wordpress_options ){
            return null;
        }
        $location_uid_custom_css = ( isset($glf_wordpress_options->custom_css_by_location) ? $glf_wordpress_options->custom_css_by_location : '' );
        if( is_array($location_uid_custom_css) ){
            if( !empty( $location_uid ) ){
                $location_uid_custom_css = $location_uid_custom_css[$location_uid];
            }
        }
        return $location_uid_custom_css;
    }

    /**
     *
     * Set all locations or just one location custom_css
     * @param array $custom_css
     * @param string $location_ruid
     */
    public static function glf_set_locations_custom_css( $custom_css, $location_uid ='' ){
        $glf_wordpress_options = get_option( 'glf_wordpress_options' );
        if( is_array($custom_css) ){
            if(!empty($location_uid)){
                $glf_wordpress_options->custom_css_by_location[$location_uid ] = $custom_css;
            }
            else{
                $glf_wordpress_options->custom_css_by_location = $custom_css;
            }
        }
        update_option('glf_wordpress_options', $glf_wordpress_options);
    }

    public static function  glf_mor_get_restaurants() {
        $restaurant_data_obj = get_option('glf_mor_restaurant_data');
        return isset($restaurant_data_obj->restaurants) ? $restaurant_data_obj->restaurants : null;
    }

    public static function glf_database_option_operation($action, $option_name, $value='' ){
        $result = '';
        if ($action === 'get') {
            $result = get_option($option_name, false);
        } else if ($action === 'delete') {
            $result = delete_option($option_name);
        } else {
            $action = ($action === 'update' && !get_option($option_name)) ? 'add' : $action;
            $function_name = $action . '_option';
            if (function_exists($function_name)) {
                $result = $function_name($option_name, $value);
            }
        }

        return $result;
    }
}

?>