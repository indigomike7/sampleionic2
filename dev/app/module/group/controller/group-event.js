define([
    'global/base/BrowseController',
    'text!tpl/event/event-search.html'
], function(Ctrl, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site) {

        /**
         * check require permission
         */
        $site.requirePerm('event.can_access_event');
        //$scope.canCreate =  $site.getPerm('event.can_create_event');

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchEvents = {
            iPage: 1,
            iItemId: $scope.item.getId(),
            sModule: 'groups',
            sView: 'upcoming',
            iAmountOfevent: 10,
            sOrder: 'latest',
            iCatSearch: 'all_cate'
        };
        
        return $scope;
    };
});