define([
    'global/base/BrowseController',
    'text!tpl/message/conversation-search-dir.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $injector, $state) {
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchConversations = {
            iAmountOfMessage: 10,
            iPage: 1,
            filter: 'sent',
        };
    };
});