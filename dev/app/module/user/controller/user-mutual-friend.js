define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $injector, $state) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
       
        // options to get friends list
        $scope.searchFriends = {
            sSearch: '',
            iUserId: $state.params.iUserId,
            iPage: 1,
            iAmountOfFriend:20,
            sAction: 'mutual',
            fields: 'id,title,imgIcon,friend,totalMutualFriend',
        };
        
        return $scope;
    };
});