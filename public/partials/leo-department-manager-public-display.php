 <?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/gr33k01
 * @since      1.0.0
 *
 * @package    Leo_Department_Manager
 * @subpackage Leo_Department_Manager/public/partials
 */

global $post;


if(!class_exists('c_ws_plugin__s2member_email_configs')) {
	include plugin_dir_path(__FILE__) . '../../../s2member/src/includes/classes/email-configs.inc.php';
}

$valid_domains = get_post_meta($post->ID, '_valid_domains', true);
$redirect = $_SERVER['HTTP_ORIGIN'] . preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$current_user = wp_get_current_user();
$is_dept_head = (bool) get_user_meta($current_user->ID, '_is_department_head', true);
$is_admin = in_array('administrator', (array) $current_user->roles ); 
$matches_dept = intval(get_user_meta($current_user->ID, '_department', true)) === intval($post->ID); 

if($_SERVER['REQUEST_METHOD'] == 'POST') {	

	if(intval($_POST['manage_access']) == 1) {
		unset($_POST['manage_access']);

		$vd = [];

		foreach($_POST as $key => $value) {
			if(sanitize_text_field($value) != '') {
				$vd[] = sanitize_text_field($value);	
			}			
		}		

		update_post_meta($post->ID, '_valid_domains', $vd);
		wp_redirect($redirect . '#updated-valid-domains'); exit();
	}

	$first = sanitize_text_field($_POST['first']);
	$last = sanitize_text_field($_POST['last']);
	$email = sanitize_text_field($_POST['email']);

	$retain_data = sprintf("&f=%s&l=%s&e=%s", urlencode($first), urlencode($last), urlencode($email));

	if($valid_domains != false && !is_user_logged_in()) {
		if(!in_array(explode('@', $email)[1], $valid_domains)) {
			wp_redirect($redirect . '?success=0&message=' . urlencode('This email cannot be used for ' . $post->post_title) . $retain_data); exit();
		}
	}

	if(get_user_by('email', $email) !== FALSE) {
		wp_redirect($redirect . '?success=0&message=' . urlencode('This email is already in use') . $retain_data); exit();
	}

	$user_id = wp_create_user( $email, wp_generate_password(), $email );

	if(get_class($user_id) == 'WP_Error') {
		wp_redirect($redirect . '?success=0&message=' . urlencode('There was an issue with your registration. Please contact support.')); exit();
	}

	c_ws_plugin__s2member_email_configs::new_user_notification($user_id, $p);

	wp_update_user(['ID' => $user_id, 'first_name' => $first, 'last_name' => $last]);	
	update_user_meta( $user_id, '_department', $post->ID);


	$is_paid = false;

	if((bool) get_post_meta($post->ID, '_active', true)) {
		$u = get_user_by('ID', $user_id);
		$u->add_role('s2member_level4');
		$u->remove_role('subscriber');
		$is_paid = true;
	}

	do_action('leo_user_added', $user, $is_paid);

	wp_redirect($redirect . '?success=1&message=' . urlencode("Success! A confirmation email has been sent to $email.")); exit();	
}

get_header(); 

if( ! is_user_logged_in() ) {

	?><div id="content">
		<div class="clearfix full-width">
			<?php require(__DIR__ . '/leo-department-manager-add-user-form.php'); ?>
		</div>
	</div><?php

} elseif ( ( $is_dept_head && $matches_dept ) || $is_admin ) {

	require(__DIR__ . '/leo-department-manager-department-head-view.php');

} else {

	wp_redirect(site_url('/member-area/')); exit();

} 

?><style type="text/css">
.breadcrumbs,
#breadcrumbs {
	display: none;
}
#dept_user_table {
	margin: 3em 0;
}
input::-webkit-input-placeholder {
  color: #eee;
}
input::-moz-placeholder {
  color: #eee;
}
input:-ms-input-placeholder {
  color: #eee;
}
input:-moz-placeholder {
  color: #eee;
}
</style><?php

 get_footer();