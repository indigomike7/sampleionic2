define([
    'global/base/BrowseController',
    'text!tpl/event/event-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {

        /**
         * check require permission
         */
        $site.requirePerm('event.can_access_event');
        $scope.canCreate =  $site.getPerm('event.can_create_event');
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchEvents  =  {
            sView: 'my',
            iPage: 1,
            sSearch: '',
            iCatSearch: 'all_cate',
            iAmountOfevent: 5,
            sOrder: 'latest'
        };
        
        return $scope;
    };
});