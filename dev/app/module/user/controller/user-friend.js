define([
    'global/base/BrowseController',
    'text!tpl/friend/friend-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $state, $injector, gettext, gettextCatalog) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
       
        // options to get friends list
        $scope.searchFriends = {
            sSearch: '',
            iUserId: $state.params.iUserId,
            iPage: 1,
            iAmountOfFriend:20,
            sAction: 'all',
            fields: 'id,title,friend,imgIcon,mutualFriendCount',
        };
        
        return $scope;
    };
});