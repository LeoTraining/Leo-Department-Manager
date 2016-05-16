<?php 
$user_id = $_GET['user_id'];
if(!isset($_GET['user_id'])) {
	$user_id = wp_get_current_user()->ID;
}
$user_dept = get_user_meta($user_id, 'department', true);
$departments = get_option('leo_department_manager_departments'); ?>

<h3>Department Information</h3>
<table class="form-table">
	<tr>
		<th>
			<label for="department">Department</label>
		</th>
		<td>
			<select name="department" id="department">
				<option value="">Please select a Department</option>

				<?php foreach($departments as $dept) : ?>
				<option value="<?php echo $dept['id']; ?>" 
						<?php if($dept['id'] == $user_dept) echo 'selected="selected"'; ?>>
					<?php echo $dept['name']; ?>
				</option>
				<?php endforeach; ?>
			</select>
			<br />			
		</td>
	</tr>	
</table>