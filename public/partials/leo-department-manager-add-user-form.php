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
	display: block;
}
</style>

<div class="register-dept-user">

<?php if($_GET['success'] == null || intval($_GET['success']) == 0 || is_user_logged_in()) : ?>
	<h3>Register for <?=$post->post_title ?></h3>

	<form method="POST" 
		<?php if(intval($_GET['success']) == 0) : ?>
		data-error-message="<?=$_GET['message']; ?>"
	<?php endif; ?>
		>

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
	
	<p><?=$_GET['message']; ?></p>

	<?php if(!is_user_logged_in()) : ?>
	<a class="custom-button medium" href="<?=site_url('/wp-admin'); ?>">Log in now</a>
	<?php else : ?>
	<a class="custom-button medium" id="close-user-form">Done</a>
	<?php endif;?>
	<style>
	.register-dept-user {
		margin-top: 3em;
		margin-bottom: 14em;
	}
	</style>
<?php endif; ?>

</div>	