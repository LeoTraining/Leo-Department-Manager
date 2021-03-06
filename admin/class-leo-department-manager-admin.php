<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @see       https://github.com/gr33k01
 * @since      1.0.0
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     Nate Hobi <nate.hobi@gmail.com>
 */
class Leo_Department_Manager_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the ID of this plugin
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the current version of this plugin
     */
    private $version;

    /**
     * The options prefix to be used in this plugin.
     *
     * @since  	1.0.0
     *
     * @var string Option prefix of this plugin
     */
    private $option_prefix = 'leo_department_manager';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name the name of this plugin
     * @param string $version     the version of this plugin
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /*
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Leo_Department_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Leo_Department_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if ('leo_department_manager_admin' == $_GET['page']) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/leo-department-manager-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /*
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Leo_Department_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Leo_Department_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // Just in case
        wp_deregister_script('angular');
        wp_deregister_script('lodash');

        // Register scripts
        wp_register_script('angular',
                            plugin_dir_url(__FILE__).'../bower_components/angular/angular.min.js',
                            array());

        wp_register_script('angular-filter',
            plugin_dir_url(__FILE__).'../bower_components/angular-filter/dist/angular-filter.min.js',
            array('angular'));

        wp_register_script($this->plugin_name, plugin_dir_url(__FILE__).'js/leo-department-manager-admin.js',
                            array('angular', 'angular-filter'),
                            $this->version,
                            false);

        // Localize scripts
        $ajax_url = get_site_url().'/wp-admin/admin-ajax.php';
        wp_localize_script($this->plugin_name,
            'departmentManagerAdmin',
            array(
                'ajaxUrl' => $ajax_url, )
            );

        // Enqueue scripts
        wp_enqueue_script($this->plugin_name);
    }

    /**
     * Ajax function for retrieving all departments.
     *
     * @since    1.0.0
     */
    public function get_departments()
    {
        header('Content-Type: application/json');
        echo json_encode(get_option('leo_department_manager_departments'));
        wp_die();
    }

    /**
     * Ajax function for retrieving all users.
     *
     * @since    1.0.0
     */
    public function get_users()
    {
        $users = get_users();
        $formated_users = array();

        foreach ($users as $user) {
            array_push($formated_users, array(
                'name' => ucwords($user->data->display_name),
                'id' => $user->id,
                'email' => strtolower($user->data->user_email),
                ));
        }

        header('Content-Type: application/json');
        echo json_encode($formated_users);
        wp_die();
    }

    public function update_user_department()
    {
        header('Content-Type: text/plain');

        try {
            $user_id = $_POST['userId'];
            $dept_id = $_POST['deptId'];
            update_usermeta($user_id, 'department', $dept_id);

            if ('' == $_POST['deptId']) {
                echo 'Successfully unassigned user from department.';
            } else {
                echo 'Successfully updated user department ID to '.get_user_meta($user_id, 'department', true);
            }
        } catch (Excpetion $e) {
            echo 'Error updating user department: '.$e->getMessage();
        }

        wp_die();
    }

    /**
     * Ajax function for removing a department based on index.
     *
     * @since    1.0.0
     */
    public function remove_department()
    {
        header('Content-Type: text/plain');

        try {
            $option = 'leo_department_manager_departments';
            $departments = get_option($option);

            $id = $_POST['id'];
            $index = $this->search_array($departments, 'id', $id)[0];

            $message = 'Sucsessfully deleted '.$departments[$index]['name'].' ('.$departments[$index]['id'].')';

            // Remove department and update option
            unset($departments[$index]);
            update_option($option, $departments);

            echo $message;
        } catch (Excpetion $e) {
            echo 'Error removing department: '.$e->getMessage();
        }

        wp_die();
    }

    private function search_array($array, $key_to_search, $value_to_search_for)
    {
        $return_arr = array();

        foreach ($array as $key => $value) {
            if ($value[$key_to_search] == $value_to_search_for) {
                array_push($return_arr, $key);
            }
        }

        if (count($return_arr) > 0) {
            return $return_arr;
        }

        return null;
    }

    /**
     * Displays the admin page.
     *
     * @since    1.0.0
     */
    public function display_admin_page()
    {
        add_menu_page(
            'Departments',
            'Departments',
            'manage_options',
            'leo_department_manager_admin',
            array($this, 'show_page'),
            'dashicons-building',
            '50.0'
            );

        add_submenu_page(
            'leo_department_manager_admin',
            'Department Users',
            'Department Users',
            'manage_options',
            'leo_department_manager_department_users',
            array($this, 'show_department_users_page')
            );
    }

    /**
     * Register settings.
     *
     * @since    1.0.0
     */
    public function register_settings()
    {
        // Adds a General section
        add_settings_section(
            $this->option_prefix.'_general',
            __('General', 'leo-department-manager'),
            array($this, $this->option_prefix.'_general_cb'),
            $this->plugin_name
            );

        // Adds 'Departments' field in General secion
        add_settings_field(
            $this->option_prefix.'_departments',
            __('Departments', 'leo-department-manager'),
            array($this, $this->option_prefix.'_departments_cb'),
            $this->plugin_name,
            $this->option_prefix.'_general',
            array('label_for' => $this->option_prefix.'_departments')
            );

        // Registers the 'Departments' field
        register_setting($this->plugin_name, $this->option_prefix.'_departments', array($this, $this->option_prefix.'_sanitize_departments'));
    }

    /**
     * General section callback.
     *
     * @since    1.0.0
     */
    public function leo_department_manager_general_cb()
    {
        echo '<hr />';
    }

    /**
     * Includes the field display.
     *
     * @since    1.0.0
     */
    public function leo_department_manager_departments_cb()
    {
        include __DIR__.'/partials/leo-department-manager-departments-field-display.php';
    }

    /**
     * Sanitizes the department setting.
     *
     * @since    1.0.0
     */
    public function leo_department_manager_sanitize_departments($departments)
    {
        // If json value
        if ('string' == gettype($departments)) {
            $depts = json_decode($departments, true);
            foreach ($depts as $key => $value) {
                if ('' == trim($value['name'])) {
                    unset($depts[$key]);
                }
                $value['name'] = trim(str_replace('Police Department', '', $value['name']));
                $value['name'] = trim(str_replace('Department', '', $value['name']));
                $value['name'] = trim(str_replace('Police', '', $value['name']));
            }

            return array_values($depts);
        }
        // We need to reset array indicies for proper JSON output later
        return array_values($departments);
    }

    /**
     * Includes the admin area display.
     *
     * @since    1.0.0
     */
    public function show_page()
    {
        include __DIR__.'/partials/leo-department-manager-admin-display.php';
    }

    /**
     * Includes the admin area department users display.
     *
     * @since    1.0.0
     */
    public function show_department_users_page()
    {
        include __DIR__.'/partials/leo-department-manager-department-users-display.php';
    }

    /**
     * Includes the admin area display.
     *
     * @since    1.0.0
     */
    public function show_quiz_results_page()
    {
        include __DIR__.'/partials/leo-department-manager-quiz-reults-display.php';
    }

    /**
     * Loads additional custom user fields.
     *
     * @since    1.0.0
     */
    public function modify_user_fields($profile_fields)
    {
        $user_id = $_GET['user_id'];
        $is_new_user = null == $user_id;

        if (!isset($_GET['user_id'])) {
            $user_id = wp_get_current_user()->ID;
        }

        $q = new WP_Query([
            'post_type' => 'department',
            'posts_per_page' => -1,
        ]);
        $user_dept = get_user_meta($user_id, '_department', true);
        $ptbid = get_user_meta($user_id, 'ptbid', true);
        $is_deptartment_head = (bool) get_user_meta($user_id, '_is_department_head', true);
        $departments = $q->posts;

        include __DIR__.'/partials/leo-department-manager-profile-display.php';
    }

    /**
     * Saves custom user meta.
     *
     * @since    1.0.0
     */
    public function save_user_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        update_usermeta($user_id, '_department', sanitize_text_field($_POST['department']));
        update_usermeta($user_id, '_is_department_head', 'on' == $_POST['is_department_head']);
        update_usermeta($user_id, 'ptbid', sanitize_text_field($_POST['ptbid']));
    }

    public function sort_departments($departments)
    {
        usort($departments, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return array_values($departments);
    }

    public function post_columns($columns)
    {
        $columns['is_active'] = 'Active?';
        $columns['officer_count'] = 'Officer count';

        return $columns;
    }

    private function get_dept_users($dept_id)
    {
        $users = get_users([
            'meta_key' => '_department',
            'meta_value' => $dept_id,
            'meta_compare' => '=',
        ]);

        $users_arr = [];

        foreach ($users as $u) {
            $is_admin = (bool) get_user_meta($u->ID, '_is_department_head', true);

            if ($is_admin) {
                array_unshift($users_arr, $u);
            } else {
                $users_arr[] = $u;
            }
        }

        return $users_arr;
    }

    public function custom_post_column_types($column, $post_id)
    {
        if ('officer_count' === $column) {
            echo count($this->get_dept_users($post_id));
        }

        if ('is_active' === $column) {
            $is_active = get_post_meta($post_id, '_active', true);
            $dept_name = get_the_title($post_id);
            include __DIR__.'/partials/leo-department-manager-active-column.php';
        }
    }

    /*
     * After inserting all the new departments as a post type, run this function
     */
    public function upgrade()
    {
        $q = new WP_Query(['post_type' => 'department']);

        if (0 !== count($q->posts)) {
            return;
        }

        $oldDepartments = get_option('leo_department_manager_departments');
        $departmentHeads = [];

        foreach ($oldDepartments as $od) {
            foreach ($od['departmentHeads'] as $h) {
                $departmentHeads[] = intval($h);
            }

            $post_id = wp_insert_post(['post_title' => $od['name'], 'post_type' => 'department', 'post_status' => 'publish']);
            update_post_meta($post_id, '_active', true);
        }

        $od_arr = [];
        foreach ($oldDepartments as $od) {
            $od_arr[$od['id']] = $od['name'];
        }

        $users = get_users();
        echo '<pre>';
        foreach ($users as $u) {
            $oldDeptId = get_user_meta($u->ID, 'department', true);

            if ('' == $oldDeptId) {
                continue;
            }

            $newDept = get_page_by_title($od_arr[intval($oldDeptId)], 'OBJECT', 'department');
            update_usermeta($u->ID, '_department', $newDept->ID);

            if (in_array($u->ID, $departmentHeads)) {
                update_usermeta($u->ID, '_is_department_head', true);
            }

            echo 'Updated '.$u->user_email.' to deptartment '.$newDept->post_title.'. <br />';
        }

        echo '</pre>';
        exit();
    }

    public function toggle_active_department()
    {
        $id = $_GET['dept_id'];

        $should_toggle = apply_filters('should_toggle_department', $id);

        if (!$should_toggle) {
            setcookie('showJobRunningMessage');
            wp_redirect($_SERVER['HTTP_REFERER']);
            exit();
        }

        $is_active = (bool) get_post_meta($id, '_active', true);
        $users = $this->get_dept_users($id);

        update_post_meta($id, '_active', !$is_active);

        foreach ($users as $u) {
            $activate = !$is_active;
            if ($activate) {
                $u->remove_role('subscriber');
                $u->add_role('s2member_level4');
            } else {
                $u->remove_role('s2member_level4');
                $u->add_role('subscriber');
            }
        }

        do_action('active_department_updated', $id, !$is_active, $this->get_dept_users($id));
        setcookie('showSyncingWithMailchimpMessage');

        wp_redirect($_SERVER['HTTP_REFERER']);
        exit();
    }

    public function show_job_running_message()
    {
        ?>
<div class="notice notice-error is-dismissible" id="job-running-message" style="display:none;">
	<p>Still syncing this department's users with MailChimp from the last time you hit that button. Easy killer.</p>
</div>
<div class="notice notice-info is-dismissible" id="syncinc-with-mailchimp-message" style="display:none;">
	<p>Currently syncing this department users with MailChimp.</p>
</div>
<script type="text/javascript">
	(function($){
		var errorCookieName = 'showJobRunningMessage';
        var infoCookieName = 'showSyncingWithMailchimpMessage';

		function deleteCookie( name ) {
			document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}

        if(document.cookie.indexOf(errorCookieName) > -1) {
			$('#job-running-message').removeAttr('style');
			deleteCookie(errorCookieName);
		}

		if(document.cookie.indexOf(infoCookieName) > -1) {
			$('#syncinc-with-mailchimp-message').removeAttr('style');
			deleteCookie(infoCookieName);
		}
	})(jQuery);
</script>
		<?php
    }

    public function toggle_department_head()
    {
        $user_id = $_GET['user_id'];
        $is_dept_head = (bool) get_user_meta($user_id, '_is_department_head', true);
        update_usermeta($user_id, '_is_department_head', !$is_dept_head);
        do_action('toggled_department_head', $user_id, !$is_dept_head);
        wp_redirect($_SERVER['HTTP_REFERER']);
        exit();
    }

    public function department_edit_markup($post_type, $post)
    {
        add_meta_box('department_users', 'Department Users', [$this, 'department_users_metabox'], 'department', 'normal');
        add_meta_box('toggle_all_user_roles', 'Activate / Deactivate', [$this, 'activate_deactivate_metabox'], 'department', 'normal');
        add_meta_box('general_metabox', 'General', [$this, 'general_metabox'], 'department', 'normal');
    }

    public function handle_save($id, $post, $update)
    {
        if ('department' !== get_post_type($id) || !isset($_POST['organization_id'])) {
            return;
        }

        $org_id = sanitize_text_field($_POST['organization_id']);
        $mtu = sanitize_text_field($_POST['mtu']);
        update_post_meta($id, 'organization_id', $org_id);
        update_post_meta($id, 'mtu', $mtu);
    }

    public function general_metabox()
    {
        $mtu = get_post_meta($_GET['post'], 'mtu', true);
        $organization_id = get_post_meta($_GET['post'], 'organization_id', true); ?>	
	<table class="form-table">
		<tr>
			<th>
				<label for="organization_id">Organization ID</label>
			</th>
			<td>
				<input type="text" name="organization_id" id="organization_id" value="<?=$organization_id; ?>" />
				<br />			
			</td>
		</tr>	
	</table>
	<table class="form-table">
		<tr>
			<th>
				<label for="mtu">MTU</label>
			</th>
			<td>
				<input type="text" name="mtu" id="mtu" value="<?=$mtu; ?>" />
				<br />			
			</td>
		</tr>	
	</table>
<?php
    }

    public function activate_deactivate_metabox($post, $metabox)
    {
        $post_id = $post->ID;
        $is_active = get_post_meta($post_id, '_active', true);
        $dept_name = get_the_title($post_id);
        include __DIR__.'/partials/leo-department-manager-active-column.php';
    }

    public function department_users_metabox($post, $metabox)
    {
        $users = $this->get_dept_users($post->ID);
        require __DIR__.'/../includes/partials/leo-department-manager-user-management-table.php'; ?><style>
			#edit-slug-box,
			#postdivrich,
			#gdd_page_redirect,
			#ws-plugin--s2member-security {
				display: none;
			}
		</style><?php
    }

    public function delete_user()
    {
        $userToDelete = $_GET['user_id'];
        $currentUser = wp_get_current_user();

        $isDeptHead = (bool) get_user_meta($currentUser->ID, '_is_department_head', true);
        $isSuperAdmin = in_array('administrator', $currentUser->roles);

        $userToDeleteBelongsToDepartmentHead =
            get_user_meta($userToDelete, '_department', true) ==
            get_user_meta($currentUser->ID, '_department', true);

        if (($isDeptHead && $userToDeleteBelongsToDepartmentHead) || $isSuperAdmin) {
            wp_delete_user($userToDelete);
            wp_redirect($_SERVER['HTTP_REFERER'].'#Successfully%20deleted%20user.');
            exit();
        } else {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        }
    }
}
