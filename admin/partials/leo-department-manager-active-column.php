<style type="text/css">
.dept_manager_toggle:focus {
	outline: none;
	box-shadow: none;
}
</style>

<a 
	href="/wp-admin/admin-post.php?action=toggle_active_department&dept_id=<?=$post_id?>" 
	class="dept_manager_toggle" 
	onclick="return confirm('This will set all <?= $dept_name ?> officers to the <?= $is_active ? 'Subscriber' : 'CourtSmart' ?> role. If you only want to affect a few users, modify the user directly.');"
>		
	<img 
		src="<?= plugins_url('/leo-department-manager/public/images/switch-' . ($is_active ? 'on' : 'off') . '.png') ?>" 
		width="50"
	/>
</a>