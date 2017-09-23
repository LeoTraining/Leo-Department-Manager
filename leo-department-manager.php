<?php


/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/gr33k01
 * @since             1.0.0
 * @package           Leo_Department_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Leo Department Manager
 * Plugin URI:        https://github.com/gr33k01/Leo-Department-Manager
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Nate Hobi
 * Author URI:        https://github.com/gr33k01
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       leo-department-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require(__DIR__ . '/vendor/autoload.php');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-leo-department-manager-activator.php
 */
function activate_leo_department_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-leo-department-manager-activator.php';
	Leo_Department_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-leo-department-manager-deactivator.php
 */
function deactivate_leo_department_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-leo-department-manager-deactivator.php';
	Leo_Department_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_leo_department_manager' );
register_deactivation_hook( __FILE__, 'deactivate_leo_department_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-leo-department-manager.php';



function megan() {
	$users = get_users();
	$user_arr = [];
	$new_departments = get_option('leo_department_manager_departments');

	foreach($users as $user) {
		$old_department = get_user_meta($user->ID, 'wp_s2member_custom_fields')[0]['dept_name'];
		$new_department = '';

		if($old_department != '') {
			// try searching lowercase first word
			foreach($new_departments as $nd) {
				$first_word_of_old_department = strtolower(explode(' ', $old_department)[0]);
				$first_word_of_new_department = strtolower(explode(' ', $nd['name'])[0]);
				if($first_word_of_new_department == $first_word_of_old_department) {
					$new_department = $nd;
				}
			}

			// cook county forest perserve
			// foreach($new_departments as $nd) {
			// 	$has_cook = false;
			// 	$has_preserve = false;
			// 	$old_department_arr = explode(' ', $old_department);

			// 	foreach($old_department_arr as $str) {
			// 		if(strtolower($str) == 'cook') {
			// 			$has_cook = true;
			// 		}

			// 		if(strtolower($str) == 'preserve') {
			// 			$has_preserve = true;
			// 		}
			// 	}

			// 	if( ($has_cook && $has_preserve) || strtolower($old_department) == 'ccfpd') {
			// 		$new_department = 'Cook County Forest Preserve';
			// 	}
			// }

			// if( strtolower($old_department) == 'worthpd') {
			// 	$new_department = 'Worth';
			// }

			// Chicago legal
			// Worth
			//rosalyn.hill@cookcountyil.gov ["old_department"]=>string(26) "Cookcounty Forest preserve"
			//IDNR
			//Oak Brook PD
			//Oak Lawn
			//CCFPD -> cook county
			//Forest Preserve District of Cooc County



		}



		array_push($user_arr, [
			'user_id' => $user->ID,
			'user_email' => $user->user_email,
			'old_department' => $old_department,
			'new_department' => $new_department
			]);
	}


	// echo '<pre>'; var_dump($user_arr); echo '</pre>'; exit();

	// $new_arr = [];

	// foreach($user_arr as $u) {
	// 	if(  ($u['old_department'] != '' && $u['new_department'] == '')  ) {
	// 			array_push($new_arr, $u);
	// 	}
	// }
	// echo '<pre>'; var_dump($new_arr); echo '</pre>'; exit();	


	foreach($user_arr as $u) {
		if($u['new_department'] != null)
			update_usermeta( $u['user_id'], 'department', $u['new_department']['id'] );
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_leo_department_manager() {
	$plugin = new Leo_Department_Manager();
	$plugin->run();
}

run_leo_department_manager();

$options = [
	['supports' => ['title']],
	'public' => true,
	'has_archive' => true
];

$project = new CPT('department', $options);
$project->menu_icon('dashicons-building');	

add_shortcode( 'manage-department-link' , 'manage_department_link' );
function manage_department_link() {
	$user = wp_get_current_user();
	$is_dept_head = (bool) get_user_meta($user->ID, '_is_department_head', true);
	$link = get_permalink(get_user_meta($user->ID, '_department', true));	
	if($is_dept_head) : 
		?><style>
			#manage-department-link {
				background-color: #FFC107;
				color: #000;
				float: right;
			}
		</style>
	<a href="<?=$link ?>" class="custom-button" id="manage-department-link">Manage My Department</a><?php 
	endif; 
}
