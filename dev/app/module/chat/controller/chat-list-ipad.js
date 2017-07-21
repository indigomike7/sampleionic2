define(function() {

	return function($scope) {

		$scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad) {
                if ($scope.chatId) {
                    $scope.showChatById($scope.chatId);
                } else if (!$scope.noItems) {
                    $scope.showChat($scope.items[0]);
                }
            }
        };
	};
});