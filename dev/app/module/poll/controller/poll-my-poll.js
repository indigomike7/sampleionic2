define([
    'global/base/BrowseController',
    'text!tpl/poll/poll-search.html'
], function(Ctrl, searchTemplate) {
    return function($scope, $injector, gettext, gettextCatalog, $site) {
        /**
         * check require permission
         */
        $site.requirePerm('poll.can_access_polls');
        $scope.canCreatePoll =  $site.getPerm('poll.can_create_poll');

        /**
         * extend list controller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        /**
         * get search template
         */
        $scope.searchTemplate = searchTemplate;
        
        /**
         * search query data.
         * this options is required for manage poll-list-controllers
         */
        $scope.searchPolls = {
            iPage: 1,
            iAmountOfPoll: 10,
            sSearch: '',
            sView: 'my',
            sOrder: 'latest',
            fields: 'id,title,desc,stats,user,canEdit,canVote'
        };
        
        return $scope;
    };
});