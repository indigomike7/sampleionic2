define([
    'friend/controller/friend-autocomplete-list'
], function(FriendAutocompleteListCtrl) {
    return function($scope, $injector) {
        $injector.invoke(FriendAutocompleteListCtrl, this, {
            $scope: $scope
        });
        
        $scope.apiService = 'message/fetchfriend';
    };
});
