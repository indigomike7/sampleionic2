define([
    'music/model/playlist'
], function(PlaylistModel) {

	return function($scope, $state) {

		$scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad && !$state.params.iItemId && !$scope.noItems) {
                var item = $.extend({}, PlaylistModel, $scope.items[0]);
                $scope.setActiveObj(item);
            }
        };

		$scope.$on('music:viewChange', $scope.onSearch);

        $scope.$on('music:queryChange', $scope.onSearch);
	};
});