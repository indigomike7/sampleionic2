define([
    'music/model/song'
], function(SongModel) {

    return function($scope, $state) {

        $scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad && !$state.params.iItemId && !$scope.noItems) {
                var item = $.extend({}, SongModel, $scope.items[0]);
                $scope.setActiveObj && $scope.setActiveObj(item);
            }
        };

        $scope.$on('music:viewChange', $scope.onSearch);

        $scope.$on('music:queryChange', $scope.onSearch);
    };
});