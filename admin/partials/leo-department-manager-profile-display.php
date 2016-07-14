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
				<option value="<?php echo $dept['id']; ?>" 
						<?php if($dept['id'] == $user_dept && !$is_new_user) echo 'selected="selected"'; ?>>
					<?php echo $dept['name']; ?>
				</option>
				<?php endforeach; ?>
			</select>
			<br />			
		</td>
	</tr>	
</table>