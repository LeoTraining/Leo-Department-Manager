<?php 
	$f_id = $this->option_prefix . '_departments';
?>

<div class="settings" ng-controller="SettingsController as settings">
	<input type="hidden" name="<?php echo $f_id; ?>" id="<?php echo $f_id; ?>" value="{{departments}}"/>

	<div class="department-list" ng-show="!isEditing && !loadingUsers">
		<h2>Departments (Total: {{departments.length}})</h2>
		<input type="text" ng-model="departmentSearchTerm" placeholder="Type to search departments"/>
		<ul>
			<li ng-repeat="dept in (filteredDepartments = (departments | fuzzyBy: 'name' : departmentSearchTerm | orderBy: 'name' | limitTo: 10))">
				{{dept.name}} 
				<a ng-click="edit(departments.indexOf(dept))">Edit</a>
				<a ng-click="remove(dept)" class="remove">Remove</a>
			</li>
		</ul>	

		<div class="buttons" ng-show="!isEditing && !loadingUsers">
			<a class="button button-secondary" ng-click="addDepartment()">New Department</a>
			<em>Showing {{filteredDepartments.length}} of {{departments.length}}</em>
		</div>
	</div>


	
	<div class="loading" ng-show="loadingUsers">		
		<h4><i class="spinner is-active"></i>Just a sec...</h4>
	</div>

	<div class="department-editor" ng-show="isEditing">	
		<h2>Edit Department</h2>			
		<div class="setting-column">
			<label>Department Name <span class="req">*</span></label>
			<input type="text" placeholder="Enter name here..." ng-model="departments[currentlyEditingIndex].name" />
		</div>

		<div class="setting-column">		
			<label>Department Heads</label>
			<input type="text" placeholder="Search for a user..." ng-model="userSearchTerm" />

			<select multiple ng-model="departments[currentlyEditingIndex].departmentHeads">
				<option ng-repeat="user in users | fuzzy: userSearchTerm" value="{{user.id}}">{{user.name}} ({{user.email}})</option>
			</select>			

			<div class="selected" ng-show="departments[currentlyEditingIndex].departmentHeads.length">
				<em>Selected:</em>
				<ul>
					<li ng-repeat="user in users | getUsersByIdArray: departments[currentlyEditingIndex].departmentHeads">
						<a ng-click="removeDepartmentHead(user.id, departments[currentlyEditingIndex])" class="remove-department-head">&times;</a> 
						{{user.name}} ({{user.email}})
					</li>
				</ul>
			</div>
		</div>

		<div class="setting-column">
			<a ng-click="reload()" class="button button-secondary">Cancel</a>			
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" ng-disabled="!departments[currentlyEditingIndex].name">
		</div>
	</div>
</div>