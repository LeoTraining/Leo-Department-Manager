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
</style>
<table id="dept_user_table">
	<thead>
		<th>Name</th>
		<th>Email</th>
		<th>Role(s)</th>
		<th>Registration Date</th>
		<th># of logins</th>
		<th>Status</th>
	</thead>
	<tbody>
		<?php 
		global $wpdb;
		
		foreach($users as $u) :		
			$is_admin = (bool) get_user_meta($u->ID, '_is_department_head', true);
			$registration_time = reset(get_user_meta($u->ID, $wpdb->prefix . 's2member_paid_registration_times', true));
			$display_date = date('F d, Y', $registration_time); 
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
					<span style="color: #1abc9c">Department head<span><br />
					<a href="/wp-admin/admin-post.php?action=toggle_department_head&user_id=<?=$u->ID ?>">Demote to normal user</a>
				<?php else: ?>		
					Normal user<br />
					<a href="/wp-admin/admin-post.php?action=toggle_department_head&user_id=<?=$u->ID ?>">Promote to department head</a>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>