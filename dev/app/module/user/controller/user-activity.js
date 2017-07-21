define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $state, $injector) {

        if($state.current.data && $state.current.data.fromNav){
            $scope.fromNav = 1;
        }
       
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $scope.searchFeeds =  {
            sItemType: 'user',
            iItemId: $state.params.iUserId,
            iAmountOfFeed: 5,
            iMinId: 0,
            iMaxId: 0
        };
        
        return $scope;
    };
});