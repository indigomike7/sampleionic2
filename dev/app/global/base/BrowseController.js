define([
    'global/Base/BaseController'
], function(BaseController) {
    
    return function($scope, $rootScope, $injector, $ionicPopover) {
        
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = '';
        
        $scope.showSearch = function($event) {

            $scope.searchPopover || ($scope.searchPopover = $ionicPopover.fromTemplate($scope.searchTemplate, {
                scope: $scope
            }));

            $scope.searchPopover.show($event);
        };

        $scope.$on('$destroy', function() {

            $scope.searchPopover && $scope.searchPopover.remove();
        });
    };
});