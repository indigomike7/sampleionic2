define([], function() {

	return function($scope, $rootScope) {

		$scope.onChangePage = function(page) {

			page = parseInt(page) || 0;

			if (page < 1 || page == $scope.currentPage || page > $scope.totalPage) {
                return;
            }

			$rootScope.$broadcast('pagination:change', page);
		};
	};
});