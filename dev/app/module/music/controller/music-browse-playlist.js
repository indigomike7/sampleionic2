define([
    'global/base/BaseController'
], function(BaseController) {
    return function($scope, $injector, $ionicTabsDelegate) {
        
        if (ionic.Platform.isIPad()) {
            $ionicTabsDelegate.$getByHandle('music-home-tabs').select(2); // active 'More' tab
        }

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });
        
        $scope.searchMusic  =  {
            sSearch: '',
            sView: 'all',
            iLimit: 20,
            iPage: 1,
            sOrder: 'latest'
        };
        
        return $scope;
    };
});