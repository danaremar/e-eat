<?php

/**
 * WCFM plugin Install
 *
 * Plugin install script which adds default pages, taxonomies, and database tables to WordPress. Runs on activation and upgrade.
 *
 * @author 		WC Lovers
 * @package 	wcfm/helpers
 * @version   1.0.3
 */
class WCFM_Install {

	public $arr = array();

	public function __construct() {
		global $WCFM, $WCFM_Query;
		if ( get_option("wcfm_page_install") == 1 ) {
			$wcfm_page_options = get_option('wcfm_page_options', array());
			if (isset($wcfm_page_options['wc_frontend_manager_page_id'])) {
				wp_update_post(array('ID' => $wcfm_page_options['wc_frontend_manager_page_id'], 'post_content' => '[wc_frontend_manager]'));
			}
			//update_option('wcfm_page_options', $wcfm_page_options);
		}
		
		if ( !get_option("wcfm_page_install") ) {
			$this->wcfm_create_pages();
			update_option("wcfm_db_version", $WCFM->version);
			update_option("wcfm_page_install", 1);
		}
		
		// Intialize Page View Analytices Tables - Version 2.2.5
		if ( !get_option( 'wcfm_table_install' ) ) {
			$this->wcfm_create_tables();
			update_option("wcfm_table_install", 1);
		}
		
		// Create Knowlwdgebase terms
		if ( !get_option( 'wcfm_knowledgebase_category_install' ) ) {
			$this->create_terms();
			update_option("wcfm_knowledgebase_category_install", 1);
		}
		
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		// Flush rules after install
		flush_rewrite_rules();
		
		if( !get_option( 'wcfm_installed' ) && apply_filters( 'wcfm_enable_setup_wizard', true ) ) {
		  set_transient( '_wcfm_activation_redirect', 1, 30 );
		}
	}
	
	/**
	 * Create a page
	 *
	 * @access public
	 * @param mixed $slug Slug for the new page
	 * @param mixed $option Option name to store the page's ID
	 * @param string $page_title (default: '') Title for the new page
	 * @param string $page_content (default: '') Content for the new page
	 * @param int $post_parent (default: 0) Parent for the new page
	 * @return void
	 */
	function wcfm_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0) {
		global $wpdb;
		$option_value = get_option($option);
		if ($option_value > 0 && get_post($option_value))
				return;
		$page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' LIMIT 1;");
		if ($page_found) :
				if (!$option_value)
						update_option($option, $page_found);
				return;
		endif;
		$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => 1,
				'post_name' => $slug,
				'post_title' => $page_title,
				'post_content' => $page_content,
				'post_parent' => $post_parent,
				'comment_status' => 'closed'
		);
		$page_id = wp_insert_post($page_data);
		update_option($option, $page_id);
	}

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 *
	 * @access public
	 * @return void
	 */
	function wcfm_create_pages() {
			global $WCFM;

			// WCFM page
			$this->wcfm_create_page(esc_sql(_x('store-manager', 'page_slug', 'wc-frontend-manager')), 'wc_frontend_manager_page_id', __('Store Manager', 'wc-frontend-manager'), '[wc_frontend_manager]');
			
			$array_pages = get_option('wcfm_page_options', array());
			$array_pages['wc_frontend_manager_page_id'] = get_option('wc_frontend_manager_page_id');

			update_option('wcfm_page_options', $array_pages);
	}
	
	/**
	 * Create WCFM Page View Analytics tables
	 * @global object $wpdb
	 * From Version 2.2.5
	 */
	function wcfm_create_tables() {
		global $wpdb;
		$collate = '';
		if ($wpdb->has_cap('collation')) {
				$collate = $wpdb->get_charset_collate();
		}
		$create_tables_query = array();
		
		// User Table Alter
		$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "users` CHANGE `user_registered` `user_registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";
		$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "users` CHANGE `user_nicename` `user_nicename` VARCHAR(250) NOT NULL DEFAULT ''";
		
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_daily_analysis` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`is_shop` tinyint(1) NOT NULL default 0,
															`is_store` tinyint(1) NOT NULL default 0,
															`is_product` tinyint(1) NOT NULL default 0,
															`product_id` bigint(20) NOT NULL default 0,
															`author_id` bigint(20) NOT NULL default 0,
															`count` bigint(20) NOT NULL default 0,
															`visited` DATE NOT NULL DEFAULT '0000-00-00',				
															PRIMARY KEY (`ID`),
															CONSTRAINT daily_analysis UNIQUE ( product_id, author_id, visited )
															) $collate;";
														
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_detailed_analysis` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`is_shop` tinyint(1) NOT NULL default 0,
															`is_store` tinyint(1) NOT NULL default 0,
															`is_product` tinyint(1) NOT NULL default 0,
															`product_id` bigint(20) NOT NULL default 0,
															`author_id` bigint(20) NOT NULL default 0,
															`referer` text NOT NULL,	
															`ip_address` VARCHAR(60) NOT NULL,
															`country` VARCHAR(30) NOT NULL,
															`state` VARCHAR(30) NOT NULL,
															`city` VARCHAR(100) NOT NULL,
															`visited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
								
		//$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "wcfm_detailed_analysis` ADD `ip_address` VARCHAR(60) AFTER `referer`";
		//$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "wcfm_detailed_analysis` ADD `country` VARCHAR(30) AFTER `ip_address`";
		//$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "wcfm_detailed_analysis` ADD `state` VARCHAR(30) AFTER `country`";
		//$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "wcfm_detailed_analysis` ADD `city` VARCHAR(100) AFTER `state`";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_messages` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`message` longtext NOT NULL,
															`author_id` bigint(20) NOT NULL default 0,
															`reply_to` bigint(20) NOT NULL default 0,
															`message_to` bigint(20) NOT NULL default -1,
															`author_is_admin` tinyint(1) NOT NULL default 0,
															`author_is_vendor` tinyint(1) NOT NULL default 0,
															`author_is_customer` tinyint(1) NOT NULL default 0,
															`is_notice` tinyint(1) NOT NULL default 0,
															`is_direct_message` tinyint(1) NOT NULL default 0,
															`is_pined` tinyint(1) NOT NULL default 0,
															`message_type` VARCHAR(100) NOT NULL,
															`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
															PRIMARY KEY (`ID`)
															) $collate;";
															
		//$create_tables_query[] = "ALTER TABLE `" . $wpdb->prefix . "wcfm_messages` ADD `message_type` VARCHAR(100) AFTER `is_pined`";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_messages_modifier` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`message` bigint(20) NOT NULL default 0,
															`is_read` tinyint(1) NOT NULL default 0,
															`read_by` bigint(20) NOT NULL default 0,
															`read_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
															`is_trashed` tinyint(1) NOT NULL default 0,
															`trashed_by` bigint(20) NOT NULL default 0,
															`trashed_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
															PRIMARY KEY (`ID`)
															) $collate;";
															
	  $create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_messages_stat` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`message` bigint(20) NOT NULL default 0,
															`is_liked` tinyint(1) NOT NULL default 0,
															`liked_by` bigint(20) NOT NULL default 0,
															`liked_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
															`is_disliked` tinyint(1) NOT NULL default 0,
															`disliked_by` bigint(20) NOT NULL default 0,
															`disliked_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_enquiries` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`enquiry` longtext NOT NULL,
															`reply` longtext NOT NULL,
															`product_id` bigint(20) NOT NULL default 0,
															`author_id` bigint(20) NOT NULL default 0,
															`vendor_id` bigint(20) NOT NULL default 0,
															`customer_id` bigint(20) NOT NULL default 0,
															`customer_name` VARCHAR(200) NOT NULL,
															`customer_email` VARCHAR(200) NOT NULL,
															`reply_by` bigint(20) NOT NULL default 0,
															`is_private` tinyint(1) NOT NULL default 0,
															`posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,	
															`replied` DATE NOT NULL DEFAULT '0000-00-00',
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_enquiries_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`enquiry_id` bigint(20) NOT NULL default 0,
															`key` VARCHAR(200) NOT NULL,
															`value` longtext NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";		
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_enquiries_response` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`enquiry_id` bigint(20) NOT NULL default 0,
															`product_id` bigint(20) NOT NULL default 0,
															`vendor_id` bigint(20) NOT NULL default 0,
															`customer_id` bigint(20) NOT NULL default 0,
															`customer_name` VARCHAR(200) NOT NULL,
															`customer_email` VARCHAR(200) NOT NULL,
															`reply` longtext NOT NULL,
															`reply_by` bigint(20) NOT NULL default 0,
															`posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,	
															PRIMARY KEY (`ID`)
															) $collate;";					
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_enquiries_response_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`enquiry_response_id` bigint(20) NOT NULL default 0,
															`key` VARCHAR(200) NOT NULL,
															`value` longtext NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";		
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_support` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`order_id` bigint(20) NOT NULL default 0,
															`item_id` bigint(20) NOT NULL default 0,
															`product_id` bigint(20) NOT NULL default 0,
															`author_id` bigint(20) NOT NULL default 0,
															`vendor_id` bigint(20) NOT NULL default 0,
															`customer_id` bigint(20) NOT NULL default 0,
															`customer_name` VARCHAR(200) NOT NULL,
															`customer_email` VARCHAR(200) NOT NULL,
															`query` longtext NOT NULL,
															`category` longtext NOT NULL,
															`priority` VARCHAR(200) NOT NULL,
															`status` VARCHAR(200) NOT NULL,
															`posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,	
															PRIMARY KEY (`ID`)
															) $collate;";
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_support_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`support_id` bigint(20) NOT NULL default 0,
															`key` VARCHAR(200) NOT NULL,
															`value` longtext NOT NULL,
															`type` VARCHAR(200) NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";	
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_support_response` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`support_id` bigint(20) NOT NULL default 0,
															`order_id` bigint(20) NOT NULL default 0,
															`item_id` bigint(20) NOT NULL default 0,
															`product_id` bigint(20) NOT NULL default 0,
															`vendor_id` bigint(20) NOT NULL default 0,
															`customer_id` bigint(20) NOT NULL default 0,
															`reply` longtext NOT NULL,
															`reply_by` bigint(20) NOT NULL default 0,
															`posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,	
															PRIMARY KEY (`ID`)
															) $collate;";	
		
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_support_response_meta` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`support_response_id` bigint(20) NOT NULL default 0,
															`key` VARCHAR(200) NOT NULL,
															`value` longtext NOT NULL,
															PRIMARY KEY (`ID`)
															) $collate;";			
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wcfm_following_followers` (
															`ID` bigint(20) NOT NULL AUTO_INCREMENT,
															`user_id` bigint(20) NOT NULL default 0,
															`user_name` VARCHAR(200) NOT NULL,
															`user_email` VARCHAR(200) NOT NULL,
															`follower_id` bigint(20) NOT NULL default 0,
															`follower_name` VARCHAR(200) NOT NULL,
															`follower_email` VARCHAR(200) NOT NULL,
															`notify` tinyint(1) NOT NULL default 1,
															`posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,	
															PRIMARY KEY (`ID`)
															) $collate;";		
															
		$create_tables_query[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcfm_fbc_offline_messages (
															id                  int                     NOT NULL    AUTO_INCREMENT,
															user_name           longtext                NOT NULL,
															user_email          longtext                NOT NULL,
															user_message        longtext                NOT NULL,
															user_info           longtext                NOT NULL,
															mail_date           date                    NOT NULL    DEFAULT '0000-00-00',
															mail_read           boolean                 NOT NULL    DEFAULT false,
															vendor_id           varchar(30)             NOT NULL    DEFAULT '',
															PRIMARY KEY (id)
															) $collate;";
															
   $create_tables_query[] =  "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcfm_fbc_chat_rows (
															message_id        varchar(30)             NOT NULL    DEFAULT '',
															conversation_id   varchar(30)             NOT NULL,
															user_id           varchar(30)             NOT NULL    DEFAULT '',
															user_name         varchar(32)                         DEFAULT NULL,
															msg               text                    NOT NULL,
															msg_time          bigint(13)  unsigned    NOT NULL,
															UNIQUE KEY message_id (message_id)
														) $collate;";
														
   $create_tables_query[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcfm_fbc_chat_sessions (
															conversation_id   varchar(30)             NOT NULL    DEFAULT '',
															user_id           varchar(30)             NOT NULL    DEFAULT '',
															evaluation        varchar(30)             NOT NULL    DEFAULT '',
															created_at        bigint(13)  unsigned    NOT NULL,
															duration          varchar(30)             NOT NULL    DEFAULT '00:00:00',
															receive_copy      boolean                 NOT NULL    DEFAULT false,
															UNIQUE KEY conversation_id (conversation_id),
															KEY created_at (created_at)
														) $collate;";
														
   $create_tables_query[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcfm_fbc_chat_visitors (
															user_id           varchar(30)             NOT NULL    DEFAULT '',
															user_type         varchar(12)             NOT NULL    DEFAULT '',
															user_name         varchar(32)                         DEFAULT NULL,
															user_ip           int(11)     unsigned                DEFAULT NULL,
															user_email        varchar(90)                         DEFAULT NULL,
															last_online       bigint(13)  unsigned                DEFAULT NULL,
															vendor_id         varchar(30)             NOT NULL    DEFAULT '',
															UNIQUE KEY user_id (user_id)
														) $collate;";

		foreach ($create_tables_query as $create_table_query) {
			$wpdb->query($create_table_query);
		}
	}
	
	public function create_terms() {
		register_taxonomy(
			'wcfm_knowledgebase_category',
			array( 'wcfm_knowledgebase' ),
			apply_filters(
				'wcfm_taxonomy_args_knowledgebase_category', array(
					'hierarchical'      => true,
					'show_ui'           => false,
					'show_in_nav_menus' => false,
					'query_var'         => is_admin(),
					'rewrite'           => false,
					'public'            => false,
				)
			)
		);
		
		$taxonomies = array(
			'wcfm_knowledgebase_category' => array(
				__( 'General', 'wc-frontend-manager' ),
				__( 'Product', 'wc-frontend-manager' ),
				__( 'Order', 'wc-frontend-manager' ),
				__( 'Coupon', 'wc-frontend-manager' ),
				__( 'Article', 'wc-frontend-manager' ),
				__( 'Shipping', 'wc-frontend-manager' ),
				__( 'Settings', 'wc-frontend-manager' ),
				__( 'Membership', 'wc-frontend-manager' ),
				__( 'Profile', 'wc-frontend-manager' ),
				__( 'Payment', 'wc-frontend-manager' ),
				__( 'Withdrawal', 'wc-frontend-manager' ),
				__( 'Refund', 'wc-frontend-manager' ),
				__( 'Review', 'wc-frontend-manager' ),
				__( 'Policy', 'wc-frontend-manager' ),
				__( 'Inquiry', 'wc-frontend-manager' ),
				__( 'Support Ticket', 'wc-frontend-manager' ),
				__( 'Invoice', 'wc-frontend-manager' ),
				__( 'Vacation', 'wc-frontend-manager' ),
				__( 'Verification', 'wc-frontend-manager' ),
				__( 'Report', 'wc-frontend-manager' ),
				__( 'Customer', 'wc-frontend-manager' ),
				__( 'Followers', 'wc-frontend-manager' ),
				__( 'Notice', 'wc-frontend-manager' ),
				__( 'Notification', 'wc-frontend-manager' ),
				__( 'Analytics', 'wc-frontend-manager' ),
				__( 'Marketing', 'wc-frontend-manager' ),
				__( 'SEO', 'wc-frontend-manager' )
			)
		);

		foreach ( $taxonomies as $taxonomy => $terms ) {
			foreach ( $terms as $term ) {
				if ( ! get_term_by( 'name', $term, $taxonomy ) ) {
					wp_insert_term( $term, $taxonomy );
				}
			}
		}
	}

}

?>