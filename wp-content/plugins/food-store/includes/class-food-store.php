<?php
/**
 * FoodStore setup
 *
 * @package FoodStore
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main FoodStore Class.
 *
 * @class FoodStore
 */
final class FoodStore {

	/**
	 * FoodStore version.
	 *
	 * @var string
	 */
	public $version = '1.3.3';

	/**
	 * The single instance of the class.
	 *
	 * @var FoodStore
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main FoodStore Instance.
	 *
	 * Ensures only one instance of FoodStore is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return FoodStore - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * FoodStore Constructor.
	 */
	public function __construct() {
		if ( WFS_Dependencies::is_woocommerce_active() ) {
    		$this->define_constants();
      		$this->includes();
      		$this->init_hooks();
      		do_action( 'food_store_loaded' );
    	} else {
    		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
    	}
	}

	/**
	 * Define WFS Constants.
	 */
	private function define_constants() {

		$this->define( 'WFS_ABSPATH', dirname( WFS_PLUGIN_FILE ) . '/' );
		$this->define( 'WFS_PLUGIN_BASENAME', plugin_basename( WFS_PLUGIN_FILE ) );
		$this->define( 'WFS_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * When WP has loaded all plugins, trigger the `wfs_loaded` hook.
	 *
	 * This ensures `wfs_loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 *
	 * @since 1.0
	 */
	public function on_plugins_loaded() {
		do_action( 'wfs_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {

		register_activation_hook( WFS_PLUGIN_FILE, array( 'WFS_Install', 'install' ) );
		register_deactivation_hook( WFS_PLUGIN_FILE, array( 'WFS_Uninstall', 'deactivate' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( 'WFS_Shortcodes', 'init' ) );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );

		add_filter( 'plugin_action_links_' . WFS_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text') );
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		/**
		 * Core classes.
		 */
		include_once WFS_ABSPATH . 'includes/helper/wfs-update-functions.php';
		include_once WFS_ABSPATH . 'includes/wfs-core-functions.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-taxonomies.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-metaboxes.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-install.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-uninstall.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-ajax.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-shortcodes.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-services.php';

		if ( $this->is_request( 'admin' ) ) {
			$this->backend_includes();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}
	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {

		include_once WFS_ABSPATH . 'includes/class-wfs-frontend.php';
		include_once WFS_ABSPATH . 'includes/class-wfs-frontend-scripts.php';
		include_once WFS_ABSPATH . 'includes/wfs-template-hooks.php';
	}

	/**
	 * Include required admin files.
	 */
	public function backend_includes() {
		include_once WFS_ABSPATH . 'includes/admin/class-wfs-admin-settings.php';
		include_once WFS_ABSPATH . 'includes/admin/class-wfs-admin.php';
	}

	/**
	 * Function used to Init FoodStore Template Functions.
	 * This makes them pluggable by plugins and themes.
	 */
	public function include_template_functions() {
		include_once WFS_ABSPATH . 'includes/wfs-template-functions.php';
	}

	/**
	 * Init FoodStore when WordPress Initialises.
	 */
	public function init() {

		// Before init action.
		do_action( 'before_wfs_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// Init action.
		do_action( 'wfs_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'food-store',
			false,
			dirname( plugin_basename( WFS_PLUGIN_FILE ) ). '/languages/'
		);
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WFS_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WFS_PLUGIN_FILE ) );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
     * Test if we're on WPR's admin page
     *
     * @return bool
     */
  	public function is_plugin_page() {
    	
    	$current_screen = get_current_screen();

    	if (!empty($current_screen->id) && $current_screen->id == 'toplevel_page_wfs-settings') {
    		return true;
    	} else {
    		return false;
    	}
    }  

	/**
   	 * Show action links on the plugin screen.
     *
     * @param mixed $links Plugin Action links.
     * @return array
     */
  	public function plugin_action_links( $links ) {
    	$action_links = array(
      		'settings' => '<a href="' . admin_url( 'admin.php?page=wfs-settings' ) . '" aria-label="' . esc_attr__( 'View FoodStore settings', 'food-store' ) . '">' . esc_html__( 'Settings', 'food-store' ) . '</a>',
    	);
    	return array_merge( $action_links, $links );
  	}

  	/**
     * Add links to plugin's description in plugins table
     *
     * @param array  $links  Initial list of links.
     * @param string $file   Basename of current plugin.
     *
     * @return array
     */
  	public function plugin_meta_links( $links, $file ) {

  		if( $file !== WFS_PLUGIN_BASENAME ) 
  			return $links;

  		$support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/food-store/" title="' . __('Support', 'food-store') . '">' . __('Support', 'food-store') . '</a>';
	    $kb_link = '<a target="_blank" href="http://food-store.wpscripts.in/knowledge-base/" title="' . __('Knowledge Base', 'food-store') . '">' . __('Knowledge Base', 'food-store') . '</a>';
	    $rate_link = '<a target="_blank" href="https://wordpress.org/support/plugin/food-store/reviews/#new-post" title="' . __('Rate Plugin', 'food-store') . '">' . __('Rate Our Plugin ★★★★★', 'food-store') . '</a>';
	    $donate_link = '<a target="_blank" href="https://www.buymeacoffee.com/wpscripts" title="' . __('Donation', 'food-store') . '">' . __('Buy Us a Coffee ☕', 'food-store') . '</a>';

	    $links[] = $support_link;
	    $links[] = $kb_link;
	    $links[] = $rate_link;
	    $links[] = $donate_link;

	    return $links;
  	}

  	/**
     * Add powered by text in admin footer
     *
     * @param string  $text  Default footer text.
     * @return string
     */
  	public function admin_footer_text($text) {
	    
	    if (!$this->is_plugin_page()) {
	      return $text;
	    }

	    $text = '<i>Food Store v' . $this->version . '. Please <a target="_blank" href="https://wordpress.org/support/plugin/food-store/reviews/#new-post" title="Rate the plugin">rate the plugin <span>★★★★★</span></a> to help us spread the word. Thank you from the Team WP Scripts!</i>';
	    return $text;
  	}

	/**
 	 * Display admin notice
  	 */
	public function admin_notices() {

		/* translators: %s WC download URL link. */
		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Food Store requires %s to be installed &amp; active!', 'food-store' ), '<a href="'.admin_url( 'plugin-install.php?s=WooCommerce&tab=search&type=term', 'admin' ).'">WooCommerce</a>' ) . '</strong></p></div>';
	}
}