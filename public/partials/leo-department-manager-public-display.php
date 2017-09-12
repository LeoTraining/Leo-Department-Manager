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
$valid_domains = get_post_meta($post->ID, '_valid_domains', true);
$redirect = $_SERVER['HTTP_ORIGIN'] . preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);

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
	$p = sanitize_text_field($_POST['password']);
	$cp = sanitize_text_field($_POST['confirm_password']);	
	$retain_data = sprintf("&f=%s&l=%s&e=%s", urlencode($first), urlencode($last), urlencode($email));

	if($valid_domains != false) {
		if(!in_array(explode('@', $email)[1], $valid_domains)) {
			wp_redirect($redirect . '?success=0&message=' . urlencode('This email cannot be used for ' . $post->post_title) . $retain_data); exit();
		}
	}

	$uppercase = preg_match('@[A-Z]@', $p);
	$lowercase = preg_match('@[a-z]@', $p);
	$number    = preg_match('@[0-9]@', $p);	

	if($p != $cp) {
		wp_redirect($redirect . '?success=0&message=' . urlencode('Passwords do not match') . $retain_data); exit();
	}

	if(get_user_by('email', $email) !== FALSE) {
		wp_redirect($redirect . '?success=0&message=' . urlencode('This email is already in use') . $retain_data); exit();
	}

	if(!$uppercase || !$lowercase || !$number || strlen($p) < 8) {
	  wp_redirect($redirect . '?success=0&message=' . urlencode('Invalid password') . $retain_data); exit();
	}
	
	$user_id = wp_create_user( $email, $p, $email );

	if(get_class($user_id) == 'WP_Error') {
		wp_redirect($redirect . '?success=0&message=' . urlencode('There was an issue with your registration. Please contact support.')); exit();
	}


	wp_update_user(['ID' => $user_id, 'first_name' => $first, 'last_name' => $last]);	
	update_user_meta( $user_id, '_department', $post->ID);

	if((bool) get_post_meta($post->ID, '_active', true)) {
		$u = get_user_by('ID', $user_id);
		$u->add_role('s2member_level4');
		$u->remove_role('subscriber');
	}

	wp_redirect($redirect . '?success=1&message=' . urlencode('Success! Check your email for an email confirmation link')); exit();
}

get_header(); 

$current_user = wp_get_current_user();
$is_dept_head = (bool) get_user_meta($current_user->ID, '_is_department_head', true);
$is_admin = in_array('administrator', (array) $current_user->roles ); 
$matches_dept = get_user_meta($current_user->ID, '_department', true) === $post->ID; 

?>

<?php if(!is_user_logged_in()) : ?>

<style type="text/css">
.sub-header-title {
	text-align: center;
}
.register-dept-user {
	border: 1px solid #ccc;
	padding: 2em;
	box-sizing: border-box;
	max-width: 600px;
	margin: auto;
	box-shadow: 0px 5px 16px rgba(0, 0, 0, .2);
}

.register-dept-user * {
	box-sizing: border-box;
}

.register-dept-user input {
	width: 100%;	
}

[data-error-message]:not([data-error-message=""])::before {
	content: attr(data-error-message) ".";
	color: #e74c3c;
	display: block;
	margin-bottom: 1em;
}

[data-error-message*='password'] input[type="password"] {	
	border-color: #e74c3c;
}

[data-error-message*='password'] label[for*='password'] {
	color: #e74c3c;
}

[data-error-message*='email'] input[type="email"] {	
	border-color: #e74c3c;
}

[data-error-message*='email'] label[for*='email'] {
	color: #e74c3c;
}

.register-dept-user label {
	margin-top: 1em;
	display: inline-block;
}
.register-dept-user button {
	margin-top: 2em;
	font-weight: 1.5em;
}
.register-dept-user .password-hint {
	font-size: .9em;
	color: #2980b9;
}
</style>

<div id="content">
	<div class="clearfix full-width">
		
		<div class="register-dept-user">
			<?php if($_GET['success'] == null || intval($_GET['success']) == 0) : ?>
			<h3>Register for <?=$post->post_title ?></h3>

			<form method="POST" data-error-message="<?=$_GET['message']; ?>">

				<label>Name</label><br />
				<input type="text" name="first" placeholder="First" required="required" style="width: 49%;"
					value="<?=isset($_GET['f']) ? $_GET['f'] : '' ?>"/>
				<input type="text" name="last" placeholder="Last" required="required" style="width: 49%; float: right;"
					value="<?=isset($_GET['l']) ? $_GET['l'] : '' ?>"/>

				<label for="email">Email Address</label>
				<input type="email" name="email" placeholder="john.smith@example.com" required="required" 
					value="<?=isset($_GET['e']) ? $_GET['e'] : '' ?>" />

				<label for="password">Password</label>
				<input type="password" name="password" placeholder="⬤ ⬤ ⬤ ⬤ ⬤ ⬤" required="required" />
				<label class="password-hint">Password must have an uppercase letter and a number and be at least 8 characters.</label>
				

				<label for="confirm_password">Confirm Password</label>
				<input type="password" name="confirm_password" placeholder="⬤ ⬤ ⬤ ⬤ ⬤ ⬤" required="required" />

				<button type="submit">Submit</button>
			</form>
			<?php else : ?>
			<h3>Successfully registered for <?=$post->post_title ?>!</h3>
			<p>You can login now with your email and password. Contact your department head if you have any questions or issues.</p>
			<a class="custom-button medium" href="<?=site_url('/wp-admin'); ?>">Log in now</a>
			<style>
			.register-dept-user {
				margin-top: 3em;
    			margin-bottom: 14em;
			}
			</style>
			<?php endif; ?>
		</div>
		
	</div>
</div>


<?php elseif (($is_dept_head && $matches_dept) || $is_admin) : 

$users = get_users([
	'meta_key'     => '_department',
	'meta_value'   => $post->ID,
	'meta_compare' => '=',
]); ?>

<style>

input.public-signup-link {
    width: 500px !important;    
    background: #ddd;
    font-weight: 300;
    margin-top:.5em;
    letter-spacing: 1px;
    padding: 5px 9px;
}

.modal {
	position: fixed;
	top:0; bottom: 0; left: 0; right: 0;
	background-color: rgba(255, 255, 255, .8);	
	padding: 10em 3em;
}

.modal .inner {
	max-width: 700px;
	margin: auto;
	background: #fff;
	box-shadow: 0px 5px 16px rgba(0, 0, 0, .2);
	padding: 2em;
}

.modal .inner input {
	width: 100%;
	box-sizing: border-box;
	margin-bottom: 2em;	
}

.modal .inner input::placeholder {

}

span.message {
	display: block;
    text-align: center;
    background: #27ae60;
    padding: 1em;
    margin-bottom: 1em;
    color: #fff;
    border-radius: 2px;
}
</style>
<div id="content">
	<div class="clearfix full-width">
		<h3>Want to add users? <a class="custom-button" style="float: right;">Add User Manually +</a></h3>
		<label>Copy/email public sign up link to your department: </label><br />
		<input disabled="disabled" value="<?php the_permalink(); ?>" style="width: auto;" class="public-signup-link" /> <a class="custom-button" id="manage-access-btn">Manage Access</a><br />

		<small>
			<?php if($valid_domains) : ?>
			Sign ups currently allowed for emails with the following domains:&nbsp;&nbsp;&nbsp;<?php foreach($valid_domains as $key => $d) : echo $d . '&nbsp;&nbsp;&nbsp;'; endforeach;?>
			<?php else : ?>
			Sign ups are currently allow for <em>any</em> email.
			<?php endif; ?>
		</small>

		<?php require(__DIR__ .'/../../includes/partials/leo-department-manager-user-management-table.php'); ?>
	</div>
</div>

<div class="modal" id="manage-access">
	<div class="inner">
		<span class="message" style="display: none;">Successfully updated!</span>
		<h3>Manage Access</h3>
		<p>By default, the public sign up form for your dept. will allow any email address. You can restrict by domain here. For instance,
		if your whole department's email format matches name@yourdept.com, you can add yourdept.com to this list to only allow signups with that type of email.</p>
		<form method="POST">
			<?php if(!$valid_domains) : ?>
			<input type="text" placeholder="example.com" name="domain_1"/>
			<?php else : ?>
				<?php foreach($valid_domains as $key => $d) : ?>
				<input type="text" placeholder="example.com" name="domain_<?=$key + 1 ?>" value="<?=$d?>" />
				<?php endforeach; ?>	
			<?php endif; ?>
			<input type="hidden" name="manage_access" value="1" />
			<a href="#" style="float: right; text-decoration: underline;" id="add-another-domain">Add another + </a>
			<button class="custom-button">Save Changes</button> <?php if($valid_domains) : ?><a href="#" id="reset-domains" class="custom-button">Reset</a><?php endif; ?>
		</form>
	</div>
</div>

<script>
(function($){
	$('#manage-access-btn').click(function(){ $('#manage-access').fadeIn(); })
	$('#add-another-domain').click(function(e){
		e.preventDefault();
		$('<input />').attr({
			"placeholder": "example.com",
			"type": "text",
			"name": 'domain_' + (+($('#manage-access').find('input[type="text"]').length + 1))
		}).insertBefore($(this)).focus();
	});

	$('#reset-domains').click(function(e) {
		e.preventDefault();

		if(confirm('Are you sure? This will open the sign up form to all email addresses.')) {
			$('#manage-access').find('input[type="text"]').val('');
			$('#manage-access').find('form').submit();	
		}		
	});

	if(window.location.hash == '#updated-valid-domains') {
		$('#manage-access').show();
		$('#manage-access').find('.message').show();
		window.location.hash = '';
	}
})(jQuery);

</script>

<?php else: wp_redirect(site_url('/member-area/')); exit(); endif; ?>

<style type="text/css">
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
</style>

<?php
 get_footer();