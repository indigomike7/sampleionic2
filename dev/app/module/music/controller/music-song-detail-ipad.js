define(function() {

    return function($scope, $ionicScrollDelegate, $timeout) {

        $scope.scrollTop = function() {

            $timeout(function() {
                $ionicScrollDelegate.$getByHandle('music-song-detail').scrollTop();
            }, 200);
        };

        $scope.initItem = function(e, obj) {

            $scope.dataReady = false;
            $scope.isLoading = true;
            $scope.scrollTop();
            $scope.item = $scope.activeObj;
            $scope.loadInit();
        };

        if ($scope.initObj) {
            $scope.activeObj = $scope.initObj;
            $scope.initItem();
        }

        $scope.$on('music:objChange', $scope.initItem);
    };
});