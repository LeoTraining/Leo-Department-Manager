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

get_header(); 

$current_user = wp_get_current_user();
$is_dept_head = (bool) get_user_meta($current_user->ID, '_is_department_head', true);
$is_admin = in_array('administrator', (array) $current_user->roles ); 
$matches_dept = get_user_meta($current_user->ID, '_department', true) === $post->ID; 

?>

<?php if(!is_user_logged_in()) : ?>

<div id="content">
	<div class="clearfix full-width">
		<h3>Register for <?=$post->post_title ?></h3>

		<form method="POST" action="/wp-admin/admin-post.php">
			<label>Email Address</label>
			<input type="email" name="email" />
			<label>Password</label>
			<input type="passowrd" name="password" />
			<input type="hidden" name="action" value="create_new_dept_user" />			
			
			<button type="submit">Submit</button>
		</form>
	</div>
</div>


<?php elseif (($is_dept_head && $matches_dept) || $is_admin) : 


$users = get_users([
	'meta_key'     => '_department',
	'meta_value'   => $post->ID,
	'meta_compare' => '=',
]); ?>

<div id="content">
	<div class="clearfix full-width">
		<?php require(__DIR__ .'/../../includes/partials/leo-department-manager-user-management-table.php'); ?>
	</div>
</div>



<?php else: wp_redirect(site_url('/member-area/')); exit(); endif; ?>

<style type="text/css">
.breadcrumbs,
#breadcrumbs {
	display: none;
}
#dept_user_table {
	margin: 3em 0;
}
</style>

<?php
 get_footer();