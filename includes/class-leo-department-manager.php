<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/gr33k01
 * @since      1.0.0
 *
 * @package    Leo_Department_Manager
 * @subpackage Leo_Department_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Leo_Department_Manager
 * @subpackage Leo_Department_Manager/includes
 * @author     Nate Hobi <nate.hobi@gmail.com>
 */

class Leo_Department_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Leo_Department_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'leo-department-manager';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Leo_Department_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Leo_Department_Manager_i18n. Defines internationalization functionality.
	 * - Leo_Department_Manager_Admin. Defines all hooks for the admin area.
	 * - Leo_Department_Manager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-leo-department-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-leo-department-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-leo-department-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-leo-department-manager-public.php';

		$this->loader = new Leo_Department_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Leo_Department_Manager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Leo_Department_Manager_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Leo_Department_Manager_Admin( $this->get_plugin_name(), $this->get_version() );		

		// Display admin page
		// $this->loader->add_action( 'admin_menu', $plugin_admin, 'display_admin_page' );

		// Scripts and styles
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Register settings
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		// Additional profile fields
		$this->loader->add_action( 'user_new_form', $plugin_admin, 'modify_user_fields' );
		$this->loader->add_action( 'show_user_profile', $plugin_admin, 'modify_user_fields' );
		$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'modify_user_fields' );
		$this->loader->add_action( 'personal_options_update', $plugin_admin, 'save_user_fields' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'save_user_fields' );
		$this->loader->add_action( 'user_register', $plugin_admin, 'save_user_fields' );

		// Ajax functions
		$this->loader->add_action( 'wp_ajax_get_departments', $plugin_admin, 'get_departments' );
		$this->loader->add_action( 'wp_ajax_get_users', $plugin_admin, 'get_users' );
		$this->loader->add_action( 'wp_ajax_remove_department', $plugin_admin, 'remove_department' );
		$this->loader->add_action( 'wp_ajax_update_user_department', $plugin_admin, 'update_user_department' );

		// Admin post functions
		$this->loader->add_action('admin_post_toggle_active_department', $plugin_admin, 'toggle_active_department');
		$this->loader->add_action('admin_post_toggle_department_head', $plugin_admin, 'toggle_department_head');		
		$this->loader->add_action('admin_post_nopriv_toggle_department_head', $plugin_admin, 'toggle_department_head');		
		$this->loader->add_action('admin_post_delete_user', $plugin_admin, 'delete_user');		
		
		// Custom Post Type List Column stuff
		$this->loader->add_filter('manage_department_posts_columns', $plugin_admin, 'post_columns');		
		$this->loader->add_action('manage_department_posts_custom_column', $plugin_admin, 'custom_post_column_types', 10, 2);

		$this->loader->add_action('add_meta_boxes_department', $plugin_admin, 'department_edit_markup');
		$this->loader->add_action('save_post', $plugin_admin, 'handle_save');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Leo_Department_Manager_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'single_template', $plugin_public, 'department_single_template' );
		$this->loader->add_action( 'archive_template', $plugin_public, 'department_list_template' );
		 
		// Admin post functions
		$this->loader->add_action('admin_post_create_new_dept_user', $plugin_public, 'create_new_dept_user');
		$this->loader->add_action('admin_post_nopriv_create_new_dept_user', $plugin_public, 'create_new_dept_user');		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Leo_Department_Manager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
