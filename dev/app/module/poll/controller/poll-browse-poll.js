define([
    'global/base/BrowseController',
    'text!tpl/poll/poll-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, gettext, gettextCatalog, $site) {
        /**
         * check require permission
         */
        $site.requirePerm('poll.can_access_polls');
        $scope.canCreatePoll =  $site.getPerm('poll.can_create_poll');

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        
        $scope.searchTemplate = searchTemplate;
        
        $scope.searchPolls = {
            iPage: 1,
            iAmountOfPoll: 10,
            sSearch: '',
            sView: 'all',
            sOrder: 'latest'
        };
        
        return $scope;
    };
});