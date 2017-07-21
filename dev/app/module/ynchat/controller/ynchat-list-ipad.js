define(function() {

	return function($scope) {

		$scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad) {
                if ($scope.ynchatId) {
                    $scope.setObjById($scope.ynchatId);
                } else if (!$scope.noItems) {
                    $scope.setObj($scope.items[0]);
                }
            }
        };
	};
});