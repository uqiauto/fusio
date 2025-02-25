'use strict';

angular.module('fusioApp.routes', ['ngRoute', 'ui.bootstrap'])

.config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/routes', {
		templateUrl: 'app/routes/index.html',
		controller: 'RoutesCtrl'
	});
}])

.controller('RoutesCtrl', ['$scope', '$http', '$modal', '$routeParams', function($scope, $http, $modal){

	$scope.response = null;
	$scope.search = '';

	$scope.load = function(){
		var search = encodeURIComponent($scope.search);

		$http.get(fusio_url + 'backend/routes?search=' + search).success(function(data){
			$scope.totalItems = data.totalItems;
			$scope.startIndex = 0;
			$scope.routes = data.entry;
		});
	};

	$scope.pageChanged = function(){
		var startIndex = ($scope.startIndex - 1) * 16;
		var search = encodeURIComponent($scope.search);

		$http.get(fusio_url + 'backend/routes?startIndex=' + startIndex + '&search=' + search).success(function(data){
			$scope.totalItems = data.totalItems;
			$scope.routes = data.entry;
		});
	};

	$scope.doSearch = function(search){
		var search = encodeURIComponent(search);
		$http.get(fusio_url + 'backend/routes?search=' + search).success(function(data){
			$scope.totalItems = data.totalItems;
			$scope.startIndex = 0;
			$scope.routes = data.entry;
		});
	};

	$scope.openCreateDialog = function(){
		var modalInstance = $modal.open({
			size: 'lg',
			backdrop: 'static',
			templateUrl: 'app/routes/create.html',
			controller: 'RoutesCreateCtrl'
		});

		modalInstance.result.then(function(response){
			$scope.response = response;
			$scope.load();
		}, function(){
		});
	};

	$scope.openUpdateDialog = function(route){
		var modalInstance = $modal.open({
			size: 'lg',
			backdrop: 'static',
			templateUrl: 'app/routes/update.html',
			controller: 'RoutesUpdateCtrl',
			resolve: {
				route: function(){
					return route;
				}
			}
		});

		modalInstance.result.then(function(response){
			$scope.response = response;
			$scope.load();
		}, function(){
		});
	};

	$scope.openDeleteDialog = function(route){
		var modalInstance = $modal.open({
			size: 'lg',
			backdrop: 'static',
			templateUrl: 'app/routes/delete.html',
			controller: 'RoutesDeleteCtrl',
			resolve: {
				route: function(){
					return route;
				}
			}
		});

		modalInstance.result.then(function(response){
			$scope.response = response;
			$scope.load();
		}, function(){
		});
	};

	$scope.closeResponse = function(){
		$scope.response = null;
	};

	$scope.load();

}])

.controller('RoutesCreateCtrl', ['$scope', '$http', '$modalInstance', function($scope, $http, $modalInstance){

	$scope.route = {
		methods: 'GET|POST|PUT|DELETE',
		path: '',
		config: []
	};

	$scope.methods = ['GET', 'POST', 'PUT', 'DELETE'];
	$scope.schemas = [];
	$scope.actions = [];

	$scope.statuuus = [{
		key: 4,
		value: "Development"
	},{
		key: 1,
		value: "Production"
	},{
		key: 2,
		value: "Deprecated"
	},{
		key: 3,
		value: "Closed"
	}];

	$scope.create = function(route){
		$http.post(fusio_url + 'backend/routes', route)
			.success(function(data){
				$scope.response = data;
				if (data.success === true) {
					$modalInstance.close(data);
				}
			})
			.error(function(data){
				$scope.response = data;
			});
	};

	$http.get(fusio_url + 'backend/action')
		.success(function(data){
			$scope.actions = data.entry;
		});

	$http.get(fusio_url + 'backend/schema')
		.success(function(data){
			$scope.schemas = data.entry;
		});

	$scope.close = function(){
		$modalInstance.dismiss('cancel');
	};

	$scope.closeResponse = function(){
		$scope.response = null;
	};

	$scope.addVersion = function(){
		var versions = [];
		for (var i = 0; i < $scope.route.config.length; i++) {
			var version = $scope.route.config[i];
			version.active = false;

			versions.push(version);
		}

		versions.push($scope.newVersion());

		$scope.route.config = versions;
	};

	$scope.removeVersion = function(version){
		var versions = [];
		for (var i = 0; i < $scope.route.config.length; i++) {
			if ($scope.route.config[i].name != version.name) {
				versions.push($scope.route.config[i]);
			}
		}
		$scope.route.config = versions;
	};

	$scope.newVersion = function(){
		var version = {
			name: "" + ($scope.getLatestVersion() + 1),
			active: true,
			status: 4,
			methods: []
		};

		for(var i = 0; i < $scope.methods.length; i++) {
			version.methods.push({
				name: $scope.methods[i]
			});
		}

		return version;
	};

	$scope.getLatestVersion = function(){
		var version = 0;
		for (var i = 0; i < $scope.route.config.length; i++) {
			var ver = parseInt($scope.route.config[i].name);
			if (ver > version) {
				version = ver;
			}
		}
		return version;
	};

	$scope.addVersion();
	
}])

.controller('RoutesUpdateCtrl', ['$scope', '$http', '$modalInstance', 'route', function($scope, $http, $modalInstance, route){

	$scope.route = route;

	$scope.methods = ['GET', 'POST', 'PUT', 'DELETE'];
	$scope.schemas = [];
	$scope.actions = [];

	$scope.statuuus = [{
		key: 4,
		value: "Development"
	},{
		key: 1,
		value: "Production"
	},{
		key: 2,
		value: "Deprecated"
	},{
		key: 3,
		value: "Closed"
	}];
	
	$scope.update = function(route){
		$http.put(fusio_url + 'backend/routes/' + route.id, route)
			.success(function(data){
				$scope.response = data;
				if (data.success === true) {
					$modalInstance.close(data);
				}
			})
			.error(function(data){
				$scope.response = data;
			});
	};

	$http.get(fusio_url + 'backend/routes/' + route.id)
		.success(function(data){
			$scope.route = data;
		});

	$http.get(fusio_url + 'backend/action')
		.success(function(data){
			$scope.actions = data.entry;
		});

	$http.get(fusio_url + 'backend/schema')
		.success(function(data){
			$scope.schemas = data.entry;
		});

	$scope.close = function(){
		$modalInstance.dismiss('cancel');
	};

	$scope.closeResponse = function(){
		$scope.response = null;
	};

	$scope.addVersion = function(){
		var versions = [];
		for (var i = 0; i < $scope.route.config.length; i++) {
			var version = $scope.route.config[i];
			version.active = false;

			versions.push(version);
		}

		versions.push($scope.newVersion());

		$scope.route.config = versions;
	};

	$scope.removeVersion = function(version){
		var versions = [];
		for (var i = 0; i < $scope.route.config.length; i++) {
			if ($scope.route.config[i].name != version.name) {
				versions.push($scope.route.config[i]);
			}
		}
		$scope.route.config = versions;
	};

	$scope.newVersion = function(){
		var version = {
			name: "" + ($scope.getLatestVersion() + 1),
			active: true,
			status: 4,
			methods: []
		};

		for(var i = 0; i < $scope.methods.length; i++) {
			version.methods.push({
				name: $scope.methods[i]
			});
		}

		return version;
	};

	$scope.getLatestVersion = function(){
		var version = 0;
		for (var i = 0; i < $scope.route.config.length; i++) {
			var ver = parseInt($scope.route.config[i].name);
			if (ver > version) {
				version = ver;
			}
		}
		return version;
	};

}])

.controller('RoutesDeleteCtrl', ['$scope', '$http', '$modalInstance', 'route', function($scope, $http, $modalInstance, route){

	$scope.route = route;

	$scope.delete = function(route){
		$http.delete(fusio_url + 'backend/routes/' + route.id)
			.success(function(data){
				$scope.response = data;
				if (data.success === true) {
					$modalInstance.close(data);
				}
			})
			.error(function(data){
				$scope.response = data;
			});
	};

	$scope.close = function(){
		$modalInstance.dismiss('cancel');
	};

	$scope.closeResponse = function(){
		$scope.response = null;
	};

}]);

