<?php
$users = get_users([
	'meta_key'     => '_department',
	'meta_value'   => $post->ID,
	'meta_compare' => '=',
]);
$users_arr = [];

foreach($users as $u) {
	$is_admin = (bool) get_user_meta($u->ID, '_is_department_head', true);

	if($is_admin) {
		array_unshift($users_arr, $u);
	} else {
		$users_arr[] = $u;
	}
}
$users = $users_arr; ?>
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
	display: none;
	position: fixed;
	top:0; bottom: 0; left: 0; right: 0;
	background-color: rgba(255, 255, 255, .8);	
	padding: 10em 3em;
    z-index: 1000000000;
}

.modal .inner {
	max-width: 700px;
	margin: auto;
	background: #fff;
	box-shadow: 0px 5px 16px rgba(0, 0, 0, .2);
	padding: 2em;
	position: relative;
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

.modal .inner .register-dept-user {
	margin: 0;
	padding: 0;
	border: 0;
	max-width: none;
	box-shadow: none;
}

.modal-close-button {
	cursor: pointer;
	position: absolute;
	right: -40px;
	top: -40px;
	font-size: 2em;
}

</style>

<div id="content">
	<div class="clearfix full-width">
		<h3>Want to add users? <a class="custom-button" href="#add-user" style="float: right;" id="add-user-manually">Add User Manually +</a></h3>
		<label>Copy/email public sign up link to your department: </label><br />
		<input disabled="disabled" value="<?php the_permalink(); ?>" style="width: auto;" class="public-signup-link" id="public-signup-link" /> 
		<a class="custom-button" id="copy-signup-link">Copy signup link</a>
		<!--<a class="custom-button" id="manage-access-btn">Manage Access</a><br />-->

		<!--<small>
			<?php if($valid_domains) : ?>
			Sign ups currently allowed for emails with the following domains:&nbsp;&nbsp;&nbsp;<?php foreach($valid_domains as $key => $d) : echo $d . '&nbsp;&nbsp;&nbsp;'; endforeach;?>
			<?php else : ?>
			Sign ups are currently allow for <em>any</em> email.
			<?php endif; ?>
		</small>-->

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

<div class="modal" id="add-user">
	<div class="inner">
		<span class="modal-close-button">&times;</span>
		<span class="message" style="display: none;">Successfully added user!</span>		
		<?php require(__DIR__ . '/leo-department-manager-add-user-form.php'); ?>
	</div>
</div>

<script>
(function($){
	$('#manage-access-btn').click(function(){ $('#manage-access').fadeIn(); });
	$('#add-user-manually').click(function(){ $('#add-user').fadeIn(); });
	$('#close-user-form').click(function() { $('#add-user').fadeOut(); window.location.hash = '' });
	$('#add-another-domain').click(function(e){
		e.preventDefault();
		$('<input />').attr({
			"placeholder": "example.com",
			"type": "text",
			"name": 'domain_' + (+($('#manage-access').find('input[type="text"]').length + 1))
		}).insertBefore($(this)).focus();
	});

	function copy(selector) {
		var $temp = $("<div style='position: fixed; opacity: 0'>");
		$("body").append($temp);
		$temp
			.attr("contenteditable", true)
			.html($(selector).val())
			.select()
			.on("focus", function() {
				document.execCommand("selectAll", false, null);
			})
			.focus();
		document.execCommand("copy");
		$temp.remove();
	}

	$('#copy-signup-link').click(function(e){	
		copy('#public-signup-link');

		var $t = $(this);
		$t.css({'width': $(this).width(true), 'textAlign' : 'center'})
		$t.text('Copied!');

		window.setTimeout(function(){
			$t.text('COPY SIGNUP LINK');
		}, 1000);
	});

	$('#reset-domains').click(function(e) {
		e.preventDefault();

		if(confirm('Are you sure? This will open the sign up form to all email addresses.')) {
			$('#manage-access').find('input[type="text"]').val('');
			$('#manage-access').find('form').submit();	
		}		
	});

	$('.modal-close-button').click(function(){
		$(this).closest('.modal').fadeOut();
	});

	if(window.location.hash == '#updated-valid-domains') {
		$('#manage-access').show();
		$('#manage-access').find('.message').show();
		window.location.hash = '';
	}

	if(window.location.hash == '#add-user' && window.location.href.indexOf('success=1') == -1) {
		$('#add-user').show();			
	}
})(jQuery);

</script>