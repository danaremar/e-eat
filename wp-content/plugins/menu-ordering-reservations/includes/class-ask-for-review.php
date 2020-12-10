<?php
/** If this file is called directly, abort. */
if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('Glf_Ask_For_Review')) {

    /**
     * GloriaFood Admin Notice Ask For Review Class
     *
     * @since 1.0.0
     */
    class Glf_Ask_For_Review {

        const DEBUG = false;
        const QA_TESTING = false;

        const ORDERS_MILESTONE_1 = 10;
        const ORDERS_MILESTONE_2 = 100;
        const ORDERS_MILESTONE_3 = 500;
        const CONNECTIVITY_LAST7_DAYS = 0.7;
        const MIN_REVIEW_DAYS = 30;
        const MAX_REVIEW_DAYS = 90;

        const DB_OPTION_ASK_FOR_REVIEW = 'glf_option_ask_for_review';

        private $restaurants = null;
        private $default_option_ask_for_review = array(
            'already_done_review' => false,
            'last_review_check' => '',
            '30_days_review' => false,
            'qa_testing' => '',
        );
        public $db_option_ask_for_review = null;

        public function __construct($restaurants = null) {

            if (defined('DOING_AJAX') && DOING_AJAX) {
                $this->glf_register_ajax_calls();
                return;
            }

            global $pagenow;
            if (
                $pagenow !== 'admin.php' ||
                !$_GET['page'] ||
                (strpos($_GET['page'], 'glf-admin') === FALSE && strpos($_GET['page'], 'glf-publishing') === FALSE)
            ) {
                return;
            }

            if (is_array($restaurants) && !empty($restaurants)) {
                $this->restaurants = $restaurants[0];
                $this->glf_register_actions();
                $this->glf_get_database_option();
                if (!$this->db_option_ask_for_review['already_done_review']) {
                    $this->glf_ask_for_review();
                }
            }
        }

        private function glf_register_actions() {
            add_action('ask_for_review_message', array($this, 'ask_for_review_message'));
        }

        private function glf_register_ajax_calls() {
            add_action('wp_ajax_glf_action_ask_for_review_user_response', array($this, 'glf_action_ask_for_review_user_response'));
        }

        public function glf_action_ask_for_review_user_response() {
            $this->glf_get_database_option();
            $this->db_option_ask_for_review['already_done_review'] = true;
            Glf_Mor_Utils::glf_database_option_operation('update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review);
            echo json_encode(array('message' => 'done'));
            exit;
        }

        private function glf_get_database_option() {
            //Glf_Mor_Utils::glf_database_option_operation( 'delete', self::DB_OPTION_ASK_FOR_REVIEW );
            $db_option = Glf_Mor_Utils::glf_database_option_operation('get', self::DB_OPTION_ASK_FOR_REVIEW, '');
            if (empty($db_option)) {
                Glf_Mor_Utils::glf_database_option_operation('add', self::DB_OPTION_ASK_FOR_REVIEW, $this->default_option_ask_for_review);
                $db_option = Glf_Mor_Utils::glf_database_option_operation('get', self::DB_OPTION_ASK_FOR_REVIEW, '');

            }
            $this->db_option_ask_for_review = $db_option;
        }

        /**
         *
         * Checking to see if conditions are met to display the
         * 'ask for review' notice
         *
         */
        private function glf_ask_for_review() {

            $this->debug_and_qa_testing();

            $message_number = (self::QA_TESTING) ? 0 : $this->glf_get_message_number();
            if ($message_number !== 0) {
                Glf_Mor_Utils::glf_database_option_operation('update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review);
                $this->glf_ask_for_review_render_notice($message_number);
            }
        }

        /**
         *
         * Get the message number
         * @return int
         */
        private function glf_get_message_number() {
            $notice_number = 0;
            $days_since_last_review = $this->get_days_since_last_review();

            if ($this->restaurants->last7_connectivity >= self::CONNECTIVITY_LAST7_DAYS) {
                if (
                    $this->restaurants->last28_orders >= self::ORDERS_MILESTONE_1 &&
                    empty($this->db_option_ask_for_review['last_review_check'])
                ) {
                    $notice_number = 1;
                    $this->db_option_ask_for_review['last_review_check'] = date('d-m-Y');
                } else if (
                    $this->restaurants->total_orders >= self::ORDERS_MILESTONE_2 &&
                    $days_since_last_review >= self::MIN_REVIEW_DAYS &&
                    !$this->db_option_ask_for_review['30_days_review']
                ) {
                    $notice_number = ($this->restaurants->total_orders >= self::ORDERS_MILESTONE_3 ? 3 : 2);
                    $this->db_option_ask_for_review['30_days_review'] = true;
                    $this->db_option_ask_for_review['last_review_check'] = date('d-m-Y');
                } else if (
                    $this->restaurants->total_orders >= self::ORDERS_MILESTONE_3 &&
                    $days_since_last_review >= self::MAX_REVIEW_DAYS
                ) {
                    $notice_number = 3;
                    $this->db_option_ask_for_review['last_review_check'] = date('d-m-Y');
                }
            }
            return $notice_number;
        }

        /**
         *
         * Method used for DEBUG and QA_TESTING purposes
         *
         */
        private function debug_and_qa_testing() {
            if (self::DEBUG) {
                var_dump('Glf_Ask_For_Review');
                var_dump('last28_orders=' . $this->restaurants->last28_orders);
                var_dump('total_orders=' . $this->restaurants->total_orders);
                var_dump('last7_connectivity=' . $this->restaurants->last7_connectivity);
                var_dump($this->db_option_ask_for_review);
            }

            if (self::QA_TESTING) {
                if (empty($this->db_option_ask_for_review['qa_testing'])) {
                    $messageNumber = 1;
                } else {
                    $messageNumber = $this->db_option_ask_for_review['qa_testing'] + 1;
                }
                $this->db_option_ask_for_review['qa_testing'] = $messageNumber;

                if ($messageNumber > 3) {
                    $messageNumber = 3;
                }
                $this->glf_ask_for_review_render_notice($messageNumber);
                Glf_Mor_Utils::glf_database_option_operation('update', self::DB_OPTION_ASK_FOR_REVIEW, $this->db_option_ask_for_review);
                return;
            }
        }

        /**
         *
         * Get the number of days that have passsed since last review
         * @param int $result
         */
        private function get_days_since_last_review() {
            $last_date = $this->db_option_ask_for_review['last_review_check'];
            $current_date = date('d-m-Y');
            $result = strtotime($current_date) - strtotime($last_date);

            return round($result / 86400);
        }

        /**
         *
         * Display the Ask For Review notice
         * @param int $notice_message
         */
        private function glf_ask_for_review_render_notice($message_number) {
            ?>
            <div class="glf-notice-wrapper notice notice-info is-dismissible">
                <?php do_action('ask_for_review_message', $message_number); ?>

                <p><a href="https://wordpress.org/plugins/menu-ordering-reservations/#reviews" target="_blank"
                      rel="noopener noreferrer"><?php _e('Sure thing', 'menu-ordering-reservations'); ?></a></p>
                <p class="glf-notice-dismiss"
                   data-type="later"><?php _e('Maybe later', 'menu-ordering-reservations'); ?></p>
                <p class="glf-notice-dismiss"
                   data-type="done"><?php _e('I already did', 'menu-ordering-reservations'); ?></p>

                <script type="application/javascript">
                    document.addEventListener("DOMContentLoaded", function (event) {
                        if (document.readyState === 'interactive') {
                            let noticeDismiss = jQuery(document).find('.glf-notice-dismiss'),
                                glf_ajaxRequest = '';

                            noticeDismiss.on('click', function (e) {
                                e.preventDefault();
                                noticeDismiss.off('click');
                                let buttonDismiss = noticeDismiss.parent().find('button.notice-dismiss');

                                if (jQuery(this).attr('data-type') === 'done') {
                                    glf_ajax_ask_for_reviews({action: 'glf_action_ask_for_review_user_response'});
                                }

                                if (buttonDismiss.length > 0) {
                                    buttonDismiss.trigger('click');
                                }

                            });

                            function glf_ajax_ask_for_reviews(data) {
                                if (typeof window.ajaxurl !== 'undefined') {
                                    glf_ajaxRequest = jQuery.ajax({
                                        url: window.ajaxurl,
                                        type: "POST",
                                        data: data,
                                        dataType: "json",
                                        success: function (data) {
                                        },
                                        error: function (xhr, status, error) {
                                            console.log('Status[' + status + '] Error[' + error + ']');
                                        }
                                    });
                                }
                            }
                        }
                    });
                </script>
                <style>
                    .glf-notice-wrapper {
                        display: flex;
                        flex-direction: column;
                        align-items: flex-start;
                    }

                    .glf-notice-dismiss {
                        cursor: pointer;
                        text-decoration: underline;
                    }

                    .glf-notice-dismiss:hover {
                        text-decoration: underline;
                        color: #0073aa;
                    }
                </style>
            </div>
            <?php
        }

        public function ask_for_review_message($message_number) {

            $messages = array(
                "1" => array(
                    "title" => "Nicely done! You received " . $this->restaurants->last28_orders . " orders in the last 30 days! You're off to a great start.",
                    "message" => "If you have a moment, we would really appreciate if you could support our plugin by giving us a 5-star rating on WP, so we can continue releasing new updates.",
                ),
                "2" => array(
                    "title" => "Hooray! You've received more than 100 orders! That's a great milestone!",
                    "message" => "Since the plugin has proved useful to you, would you mind helping us spread the word about it and giving us a 5-star rating? We would greatly appreciate it.",
                ),
                "3" => array(
                    "title" => "Wow! You've received " . $this->restaurants->total_orders . " orders! That's a remarkable achievement!",
                    "message" => "If you have a moment, we would really appreciate if you could support our plugin by giving us a 5-star rating on WP.",
                ),
            );
            ?>
            <p><?php echo _e($messages[$message_number]['title'], "menu-ordering-reservations"); ?></p>
            <p><?php echo _e($messages[$message_number]['message'], "menu-ordering-reservations"); ?></p>
            <?php
        }

    }

}