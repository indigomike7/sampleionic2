define([
], function() {

    return function($scope, $ionicScrollDelegate, $ionicModal) {

        $scope.scrollTop = function() {
            
            $ionicScrollDelegate.$getByHandle('poll-detail').scrollTop();
        };

        $scope.initItem = function(e, obj) {

            $scope.dataReady = false;
            $scope.scrollTop();
            $scope.item = $scope.activeObj;
            $scope.loadInit();
        };

        if ($scope.activeObj) {
            $scope.initItem();
        }

        $scope.$on('poll:objChange', function() {

            $scope.initItem();
        });
    };
});