define(function() {

	return function($rootScope, $http2, $site) {

		var $globalData = {
			storage: {}
		};

		$globalData.set = function(key, value) {

			$globalData.storage[key] = value;

			$rootScope.$broadcast('globalData:update', key, value);
		};

		$globalData.get = function(key) {

			return $globalData.storage[key];
		};

		return $globalData;
	};
});