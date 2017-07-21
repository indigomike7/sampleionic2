define(function() {

	return function($scope) {

		$scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad) {
                if ($scope.cometchatId) {
                    $scope.showCometchatById($scope.cometchatId);
                } else if (!$scope.noItems) {
                    $scope.showCometchat($scope.items[0]);
                }
            }
        };
	};
});