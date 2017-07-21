define([
    'global/base/BaseController'
], function(BaseController) {
    return function($scope, $injector, $site, $ionicScrollDelegate, $timeout, $ionicSlideBoxDelegate) {
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.viewCollapse = {
            hours: false,
            founders: false,
            contact: false,
            location: false,
            description: false
        };

        $scope.toggleView = function(view) {
            $scope.viewCollapse[view] = !$scope.viewCollapse[view];
            $timeout(function() {
                $ionicScrollDelegate.resize();
            }, 200);
        };

        $scope.previousCover = function() {
            $ionicSlideBoxDelegate.$getByHandle('slide-box-cover').previous();
        };

        $scope.nextCover = function() {
            $ionicSlideBoxDelegate.$getByHandle('slide-box-cover').next();
        };

        $scope.$on('itemUpdate', function() {
            $ionicSlideBoxDelegate.$getByHandle('slide-box-cover').update();
        });
    };
});
