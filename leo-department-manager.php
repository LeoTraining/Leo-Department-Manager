<?php


/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @see              https://github.com/gr33k01
 * @since             1.0.0
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
if (!defined('WPINC')) {
    die;
}

require __DIR__.'/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-leo-department-manager-activator.php.
 */
function activate_leo_department_manager()
{
    require_once plugin_dir_path(__FILE__).'includes/class-leo-department-manager-activator.php';
    Leo_Department_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-leo-department-manager-deactivator.php.
 */
function deactivate_leo_department_manager()
{
    require_once plugin_dir_path(__FILE__).'includes/class-leo-department-manager-deactivator.php';
    Leo_Department_Manager_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_leo_department_manager');
register_deactivation_hook(__FILE__, 'deactivate_leo_department_manager');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__).'includes/class-leo-department-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_leo_department_manager()
{
    $plugin = new Leo_Department_Manager();
    $plugin->run();
}

run_leo_department_manager();

$options = [
    ['supports' => ['title']],
    'public' => true,
    'has_archive' => true,
];

$project = new CPT('department', $options);
$project->menu_icon('dashicons-building');

add_shortcode('manage-department-link', 'manage_department_link');
function manage_department_link()
{
    $user = wp_get_current_user();
    $is_dept_head = (bool) get_user_meta($user->ID, '_is_department_head', true);
    $link = get_permalink(get_user_meta($user->ID, '_department', true));
    if ($is_dept_head) :
        ?><style>
			#manage-department-link {
				background-color: #FFC107;
				color: #000;
				float: right;
			}
		</style>
	<a href="<?=$link; ?>" class="custom-button" id="manage-department-link">Manage My Department</a><?php 
    endif;
}

add_shortcode('manage-department-link-url', 'manage_department_link_url');
function manage_department_link_url()
{
    $user = wp_get_current_user();
    $is_dept_head = (bool) get_user_meta($user->ID, '_is_department_head', true);

    if ('' != get_user_meta($user->ID, '_department', true) && $is_dept_head) {
        echo get_permalink(get_user_meta($user->ID, '_department', true));
    }
}

function change_password_text()
{
    ?><style>
	#pass-strength-result,.indicator-hint {
		display: none !important;
	}
	</style>
    <script type="text/javascript">
		if(document.title.indexOf("Reset Password") > -1) {
			document.getElementById('wp-submit').value = 'Set Password';    	    	
		}    	
    </script> <?php
}
add_action('login_footer', 'change_password_text', 10);
