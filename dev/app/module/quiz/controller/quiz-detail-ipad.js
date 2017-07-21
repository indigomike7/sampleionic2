define([
], function() {

    return function($scope, $ionicScrollDelegate, $ionicModal) {

        $scope.scrollTop = function() {
            
            $ionicScrollDelegate.$getByHandle('quiz-detail').scrollTop();
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

        $scope.$on('quiz:objChange', function() {

            $scope.initItem();
        });
    };
});