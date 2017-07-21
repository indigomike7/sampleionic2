define([
    'global/base/BrowseController',
    'text!tpl/friend/friend-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $injector, gettext, gettextCatalog, $viewer) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchFriends =  {
            iUserId: $viewer.get('iUserId'),
            sSearch: '',
            iPage: 1,
            iAmountOfFriend:20,
            sAction: 'all',
            fields: 'id,title,imgIcon,friend,totalMutualFriend',
        };
        
        return $scope;
    };
});