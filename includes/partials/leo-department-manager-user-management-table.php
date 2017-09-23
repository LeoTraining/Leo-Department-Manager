<style>
	#dept_user_table {
		border: 1px solid #ddd;
		text-align: left;
		width: 100%;
		margin: 1em 0;
		border-collapse: collapse;
	}
	#dept_user_table td,
	#dept_user_table th {
		padding: 1em;
	}
	#dept_user_table tr:nth-of-type(even) {
		background: #eee;
	}
	#dept_user_table thead {
		border-bottom: 1px solid #ddd;
	}

	#dept_user_table .department-admin {
		background: #daf1ff !important;		
	}

	#cancel,
	#cancel:focus,
	#cancel:hover, {
		background-color: #ddd;
		color: #666;
	}
</style>
<table id="dept_user_table">
	<thead>
		<th>Name</th>
		<th>Email</th>
		<th>Role(s)</th>
		<th>Registration Date</th>
		<th># of logins</th>
		<th>Status</th>
		<th></th>
	</thead>
	<tbody>
		<?php 
		global $wpdb;
		
		foreach($users as $u) :		
			$is_admin = (bool) get_user_meta($u->ID, '_is_department_head', true);
			$registration_time = $u->user_registered;						
			$date_array = date_parse($registration_time);
			$display_date = date('F j, Y', mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year']));
			$login_count = get_user_meta($u->ID, $wpdb->prefix . 's2member_login_counter', true);
			$roles_str = '';
			$s2_options = get_option('ws_plugin__s2member_options');
			
			foreach($u->roles as $r) {
				if(strlen($roles_str) === 0) {												
					if(strpos($r, 's2member_') === FALSE) {
						$roles_str .= translate_user_role($r);
					} else {
						$role_lable_var = sprintf("%s_label", explode('_', $r)[1]);									
						$roles_str .= $s2_options[$role_lable_var];
					}					
				} else {
					if(strpos($r, 's2member_') === FALSE) {
						$roles_str .= sprintf(", %s", translate_user_role($r));
					} else {

						$role_lable = $s2_options[sprintf("%s_label", explode('_', $r)[1])];
						$roles_str .= sprintf(", %s", $role_lable);;
					}
				}
			}

			if($login_count == '') {
				$login_count = '―';
			}  ?>
		<tr style="position: relative;" <?=$is_admin ? 'class="department-admin"' : '' ?>>
			<td>
				<?php if(in_array('administrator', wp_get_current_user()->roles)) : ?>
				<a href="/wp-admin/user-edit.php?user_id=<?=$u->ID?>"><?=$u->first_name?> <?=$u->last_name?></a>
				<?php else: ?>
					<?=$u->first_name?> <?=$u->last_name?>
				<?php endif; ?>
			</td>
			<td><?=$u->user_email?></td>
			<td><?=$roles_str?></td>
			<td><?=$display_date ?></td>
			<td><?=$login_count ?></td>
			<td>
				<?php if($is_admin) : ?>					
					<span style="color: #1abc9c">Department admin<span><br />
					<a href="/wp-admin/admin-post.php?action=toggle_department_head&user_id=<?=$u->ID ?>" confirm confirm-message="Normal users cannot: <br /> - View all quiz results from department users<br /> - Invite/add other users to join department" confirm-heading="Demote <?=$u->first_name?> <?=$u->last_name?> to normal user">Demote to normal user</a>					
				<?php else: ?>		
					Normal user<br />
					<a href="/wp-admin/admin-post.php?action=toggle_department_head&user_id=<?=$u->ID ?>" confirm confirm-message="Admins can: <br /> - View all quiz results from department users<br /> - Invite/add other users to join department" confirm-heading="Promote <?=$u->first_name?> <?=$u->last_name?> to department admin">Promote to department admin</a>
				<?php endif; ?>
			</td>
			<td>
				<a href="/wp-admin/admin-post.php?action=delete_user&user_id=<?=$u->ID ?>" confirm confirm-message="Deleting a user will remove them from this department and the CourtSmart system. You will need to readd or reinvite them to have them on your roster again." confirm-heading="Delete <?=$u->first_name?> <?=$u->last_name?>" style="color: #c0392b">Delete</a>
			</td>	
		</tr>
		<?php endforeach; ?>		
	</tbody>
</table>

<?php if(!is_admin()) : ?>
	<div class="modal" id="confirm-modal">
		<div class="inner">
			<span class="modal-close-button">&times;</span>
			<h3 id="modal-title"></h3>
			<p id="modal-explination"></p>
			<a href="#" id="cancel" class="custom-button">Cancel</a>
			<a href="#" id="confirm" class="custom-button" style="float: right;">Yes</a>
		</div>
	</div>
<script type="text/javascript">
(function($){
	$('[confirm]').click(function(e) {
		e.preventDefault();
		var href = $(this).attr('href'),
			headingText = $(this).attr('confirm-heading'),
			text = $(this).text();
			message = $(this).attr('confirm-message');

		$('#confirm').attr('href', href).text('Yes, ' + text);
		$('#modal-title').text(headingText);
		$('#modal-explination').html(message);
		$('#confirm-modal').fadeIn();
	});

	$('#cancel').click(function(e){
		e.preventDefault();
		$(this).closest('.modal').fadeOut();
	});
})(jQuery);
</script>
<?php endif; ?>