angular
	.module('leoDepartmentManager', ['angular.filter'])
		.filter('getUsersByIdArray', function() {
		  return function(users, idArray) {
		  	if (!idArray) return [];
		  	var length = users.length,
		  		idArrayLength = idArray.length,
		  		results = [];
		  	for(var i = 0; i < length; i++) {
		  		var currentItem = users[i];

		  		for(var j = 0; j < idArrayLength; j++) {
		  			if(currentItem.id == idArray[j]) {
		  				results.push(currentItem);
		  			}
		  		}
		  	}
		  	return results;
		  }
		})

		.factory('departmentFactory', ['$http', '$q', '$httpParamSerializerJQLike', function($http, $q, $httpParamSerializerJQLike) {
			var users = [],
				departments = [];				
			var loadUsers = function() {
				var deffered = $q.defer();
				if(users.length) {
					deffered.resolve(users);
				} else {
					var req = {
						method: 'POST',
						url: departmentManagerAdmin.ajaxUrl,
						data: $httpParamSerializerJQLike({ 'action': 'get_users' }),
						headers: {
						 	'Content-Type': 'application/x-www-form-urlencoded'
						}
					}

					$http(req).then(function(response) {
						users = response.data;
						deffered.resolve(response.data);					
					});
				}
				return deffered.promise;
			};

			var loadDepartments = function() {
				var deffered = $q.defer();
				if(departments.length) {
					deffered.resolve(departments);
				} else {
					var req = {
						method: 'POST',
						url: departmentManagerAdmin.ajaxUrl,
						data: $httpParamSerializerJQLike({ 'action': 'get_departments' }),
						headers: {
						 	'Content-Type': 'application/x-www-form-urlencoded'
						}
					}
					$http(req).then(function(response) {						
						departments = response.data;
						deffered.resolve(response.data);					
					});
				}
				return deffered.promise;
			};

			var getUsers = function() {
				var deffered = $q.defer();
				loadUsers().then(function(data){
					deffered.resolve(data);
				});
				return deffered.promise;
			};

			var getDepartments = function() {
				var deffered = $q.defer();
				loadDepartments().then(function(data){
					deffered.resolve(data);
				});
				return deffered.promise;
			};


			var removeDeparment = function(id) {
				var deffered = $q.defer();
				var req = {
					method: 'POST',
					url: departmentManagerAdmin.ajaxUrl,
					data: $httpParamSerializerJQLike({ 'action': 'remove_department', 'id': id }),
					headers: {
					 	'Content-Type': 'application/x-www-form-urlencoded'
					}
				}
				$http(req).then(function(response) {					
					deffered.resolve(response);					
				});
				return deffered.promise;
			};

			return {
				'getDepartments': getDepartments,
				'getUsers': getUsers,
				'removeDeparment': removeDeparment
			}
		}])

		.controller('SettingsController', ['$scope','$http', '$httpParamSerializerJQLike', 'departmentFactory',
			function($scope, $http, $httpParamSerializerJQLike, departmentFactory){
			
			$scope.departments = [];
			$scope.users = [];
			$scope.isEditing = false;
			$scope.currentlyEditingIndex = null;
			$scope.departmentSearchTerm = '';

			$scope.getDepartments = function() {
				departmentFactory.getDepartments().then(function(data){
					$scope.departments = data;
				});
			};

			$scope.addDepartment = function() {
				var id = $scope.getNewId();
				$scope.departments.push({
					id: id,
					name: '',
					departmentHeads: [],			
				});
				$scope.edit($scope.departments.length - 1);
			};

			$scope.removeDepartmentHead = function(userIdToRemove, department) {				
				for(var i = 0; i < department.departmentHeads.length; i++) {
					var currentDepartmentHead = department.departmentHeads[i];
					if(userIdToRemove == currentDepartmentHead) {
						department.departmentHeads.splice(i, 1);
					}
				}
			};

			$scope.updateDepartmentSearchTerm = function() {
				$scope.departmentSearchTerm = $scope.departmentSearchInput;
			};

			$scope.edit = function(index) {				
				if(!$scope.users.length) {
					$scope.loadingUsers = true;
					departmentFactory.getUsers().then(function(data){
						$scope.users = data;
						$scope.isEditing = true;	
						$scope.currentlyEditingIndex = index;
						$scope.loadingUsers = false;
					});
				} else {
					$scope.isEditing = true;	
					$scope.currentlyEditingIndex = index;
				}			
			};

			$scope.reload = function() {
				window.location.href = window.location.href;
			};

			$scope.remove = function(dept) {				
				if (!confirm('Are you sure you want to delete this department?')) return;
				departmentFactory.removeDeparment(dept.id).then(function(response){
					console.log(response);
					$scope.departments.splice($scope.departments.indexOf(dept), 1);
				});
			};

			$scope.getNewId = function() {
				return $scope.departments[$scope.departments.length - 1].id + 1;
			};

			$scope.getDepartments();			
		}]);