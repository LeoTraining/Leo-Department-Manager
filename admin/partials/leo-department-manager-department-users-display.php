<?php 
$meta_value = '0';

if(isset($_GET['leo_dept'])) {
	$meta_value = $_GET['leo_dept'];
}

$args = array(
	'meta_key'     => 'department',
	'meta_value'   => $meta_value,
	'meta_compare' => '==',
	);
$users = get_users($args);  
$departments = get_option('leo_department_manager_departments'); 
$is_unassigned = $_GET['leo_dept'] == 'unassigned';

if($is_unassigned) {
	$user_arr = array();
	foreach(get_users() as $u) {
		if(trim(get_user_meta($u->ID, 'department', true)) == '') {
			array_push($user_arr, $u);
		}
	}
	$users = $user_arr;
}

if(!$is_unassigned) : add_thickbox(); ?>


<div id="assign-users" style="display:none;">
    <p>Lorem Ipsum sit dolla amet.</p>
</div>

<?php endif; ?>

<div class="department-users">
	<h1>Department Users</h1>
	<em>Use this screen to quickly manage a department's users</em>

	<div class="department-users-actions">
		<select class="department-filter">
			<option value="0">Please Select a Department</option>
			<?php foreach($departments as $dept) : ?>
			<option value="<?php echo $dept['id']; ?>"
				<?php if($dept['id'] == $meta_value) echo 'selected="selected"'; ?> >
				<?php echo $dept['name']; ?>
			</option>
			<?php endforeach; ?>
			<option value="unassigned"
				<?php if($is_unassigned) echo 'selected="selected"'; ?> >
				Unassigned</option>
		</select>

		<em><?php echo count($users); ?> user(s) in current department.</em>
		<?php if($is_unassigned) : ?>
		<a class="button-primary button" data-quick-assign>Quick Assign</a>
		<?php endif; ?>

		<?php if(!$is_unassigned) : ?>					
		<a class="button-secondary button" data-unassign>Unassign From Department</a>
		<?php endif; ?>
	</div>
	<ul <?php if($is_unassigned) echo 'class="unassigned"'; ?>>
	<?php foreach($users as $user) : ?>
		<li class="department-user" data-user-id="<?php echo $user->ID; ?>">
			<?php if(!$is_unassigned) : ?>
			<input type="checkbox" />
			<?php endif; ?>
			<span class="name"><?php echo ucwords(strtolower($user->display_name)) . ' ('. strtolower($user->user_email) . ')'; ?></span>
			

			<?php if($is_unassigned) : ?>
			<div class="quick-assign">
				<lable><em>Quick Assign</em></lable>
				<select>
					<option value="0">Please Select a Department</option>
					<?php foreach($departments as $dept) : ?>
					<option value="<?php echo $dept['id']; ?>"
						<?php if($dept['id'] == $meta_value) echo 'selected="selected"'; ?> >
						<?php echo $dept['name']; ?>
					</option>
					<?php endforeach; ?>				
				</select>
			</div>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
</div>



<script type="text/javascript">
(function($){

	function updateUserDept($li, unassign = false) {
		$li.addClass('processing');

		console.log('========================================');

		var userId = $li.data('user-id');
		var userEmail = $li.find('.name').text();
		var deptId = $li.find('select').val();
		var deptName = $li.find('select').find('option[value="' + deptId+'"]').text().trim();

		if(unassign) {
			deptId = '';
			deptName = 'Unassigning from ' + $('.department-filter').find('[selected="selected"]').text();
		}

		console.log('User: ' + userEmail + ' (' + userId + ')');
		console.log('Department: ' + deptName + ' (' + deptId + ')');

		var req = {
			method: 'POST',
			url: departmentManagerAdmin.ajaxUrl,
			data: {
				action: 'update_user_department',
				userId: userId,
				deptId: deptId
			},
			complete: function(jqXHR, textStatus) {
				$li.slideUp(400, function(){
					$li.remove();
				});
				console.log(jqXHR.data);
			}
		}

		$.ajax(req);
	};
	
	function removeFromDepartment() {
		if(!$('.department-user').find('[type="checkbox"]:checked').length) {
			alert('No departments selected.');
			return;
		}

		$('.department-user').find('[type="checkbox"]:checked').each(function(){
			$li = $(this).closest('li');
			updateUserDept($li, true);
		});
	};

	function quickAssign(e) {		

		var noDepartmentsSelected = true;

		$('.quick-assign select').each(function(){
			if($(this).val() != 0) noDepartmentsSelected = false;
		});

		if(noDepartmentsSelected) {
			alert('No departments selected for quick assign.');
			return;
		} else {
			$('.quick-assign select').each(function(){
				if($(this).val() == 0) return true;

				$li = $(this).closest('li');			
				updateUserDept($li);
			});
		}
	};

	function updateFilter() {
		window.location.search += '&leo_dept=' + $(this).val();
	};

	$('.department-filter').on('change', updateFilter);
	$('[data-quick-assign]').on('click', quickAssign);
	$('[data-unassign]').on('click', removeFromDepartment);
})(jQuery);
</script>


