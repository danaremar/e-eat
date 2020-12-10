<?php
/** If this file is called directly, abort. */
if ( !defined( 'ABSPATH' ) ) {
    die;
}

if ( !class_exists( 'Glf_Mor_Widget' ) ) {

    /**
     * GloriaFood Menu Ordering Reservations  Widget Class
     *
     * @since 1.1.0
     */
    class Glf_Mor_Widget extends WP_Widget {

        public function __construct() {
            $widget_ops	 = array(

                    'description' => __( 'See MENU & Order  or Table Reservations', 'glf-mor-widget' ) );
            $control_ops = array();
            parent::__construct( 'glf-mor-commerce', __( 'Menu - Ordering - Reservations', 'glf-mor-widget' ), $widget_ops, $control_ops );
        }

        /**
         * Echoes the widget content.

         */
        public function widget( $args, $instance ) {
            $text = '';

            if (!isset($instance["has_ordering"]) || $instance["has_ordering"]) {
                $text .= do_shortcode( apply_filters( 'widget_text', Glf_Mor_Utils::glf_mor_get_shortcode(isset($instance["ruid"]) ? $instance["ruid"] : "",'ordering', isset($instance[ 'use_custom_css' ]) ? $instance[ 'use_custom_css' ] : 0, isset($instance[ 'class' ]) ? $instance[ 'class' ] : ''), $instance, $this ) );
            }

            if (!isset($instance["has_reservations"]) || $instance["has_reservations"]) {
                $text .= do_shortcode( apply_filters( 'widget_text', Glf_Mor_Utils::glf_mor_get_shortcode(isset($instance["ruid"]) ? $instance["ruid"] : "", 'reservations', isset($instance[ 'use_custom_css' ]) ? $instance[ 'use_custom_css' ] : 0, isset($instance[ 'class' ]) ? $instance[ 'class' ] : ''), $instance, $this ) );
            }

            echo $args[ 'before_widget' ];
            ?>
            <div class="textwidget"><?php echo!empty( $instance[ 'filter' ] ) ? wpautop( $text ) : $text; ?></div>
            <?php
            echo $args[ 'after_widget' ];
        }


        /**
         * Updates a particular instance of a widget.
         */
        public function update( $new_instance, $old_instance ) {
            $instance			 = $old_instance;
            $instance[ 'class' ] = strip_tags( $new_instance[ 'class' ] );
            if ( current_user_can( 'unfiltered_html' ) ) {
                $instance[ 'class' ] = $new_instance[ 'class' ];
            } else {
                $instance[ 'class' ] = stripslashes( wp_filter_post_kses( addslashes( $new_instance[ 'class' ] ) ) );
            }

            $instance[ 'has_ordering' ] = !empty( $new_instance[ 'has_ordering' ] );
            $instance[ 'has_reservations' ] = !empty( $new_instance[ 'has_reservations' ] );
            $instance[ 'use_custom_css' ] = !empty( $new_instance[ 'use_custom_css' ] );
            $instance[ 'ruid' ] = strip_tags($new_instance[ 'ruid' ]);
            return $instance;
        }

        /**
         * Outputs the settings update form.
         */
        public function form( $instance ) {
            $restaurants = Glf_Mor_Utils::glf_mor_get_restaurants();

            array_push($restaurants, (object) array('uid' => '123', 'name' => '123'));


            $instance = wp_parse_args( (array) $instance, array( 'has_ordering' => 1, 'has_reservations' => 1, 'use_custom_css' => 0, 'class' => "", 'ruid' => $restaurants[0]->uid) );

            ?>
<div class="clear"><br></div>
        <?php if (sizeof($restaurants) > 1) {?>
            <label for="<?php echo esc_attr( $this->get_field_id( 'ruid' ) ); ?>">
                <?php esc_html_e( 'Restaurant', 'menu-ordering-reservations' ); ?>:
            </label>
            <select name="<?php echo esc_attr( $this->get_field_name( 'ruid' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'ruid' ) ); ?>">
                <?php foreach ($restaurants as $restaurant) {?>
                    <option value="<?php echo $restaurant->uid; ?>" <?php selected($instance[ 'ruid' ], $restaurant->uid );?>><?php echo $restaurant->name; ?></option>
                <?php } ?>
            </select>
        <?php }?>

            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'has_ordering' ) ); ?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'has_ordering' ) ); ?>" <?php checked( $instance[ 'has_ordering' ]); ?>>
                <label for="<?php echo esc_attr( $this->get_field_id( 'has_ordering' ) ); ?>"><?php esc_html_e( 'See MENU & Order', 'menu-ordering-reservations' ); ?></label>
            </p>

            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'has_reservations' ) ); ?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'has_reservations' ) ); ?>" <?php checked( $instance[ 'has_reservations' ]); ?> >
                <label for="<?php echo esc_attr( $this->get_field_id( 'has_reservations' ) ); ?>"><?php esc_html_e( 'Table Reservations', 'menu-ordering-reservations' ); ?></label>
            </p>
            <hr>

            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'use_custom_css' ) ); ?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'use_custom_css' ) ); ?>" <?php checked( $instance[ 'use_custom_css' ]); ?> onclick="glfMorShowCustomCssInput('<?php echo esc_attr( $this->get_field_id( 'use_custom_css' ) ); ?>', '<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>')">
                <label for="<?php echo esc_attr( $this->get_field_id( 'use_custom_css' ) ); ?>"><?php esc_html_e( 'Use custom CSS class', 'menu-ordering-reservations' ); ?></label>
            </p>
            <p>
                <input class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'class' ) ); ?>" <?= $instance[ 'use_custom_css' ] ? '' : 'style="display: none"';?> value="<?= $instance[ 'class' ];?>"></input>
            </p>

            <?php
        }

    }

}