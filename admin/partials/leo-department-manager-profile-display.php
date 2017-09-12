<?php 
?>

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
				<option value="<?php echo $dept->ID; ?>" 
						<?php if($dept->ID == $user_dept && !$is_new_user) echo 'selected="selected"'; ?>>
					<?php echo $dept->post_title; ?>
				</option>
				<?php endforeach; ?>
			</select>
			<br />			
		</td>
	</tr>	
</table>

<table class="form-table">
	<tr>
		<th>
			<label for="department">Is Department Head</label>
		</th>
		<td>
			<input type="checkbox" name="is_department_head" <?php echo ($is_deptartment_head ? 'checked="checked"' : ''); ?> />
		</td>
	</tr>	
</table>