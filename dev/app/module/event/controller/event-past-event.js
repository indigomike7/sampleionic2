define([
    'angular',
    'global/base/BrowseController',
    'text!tpl/event/event-search.html'
], function(angular, BrowseController, searchTemplate) {
    return function($scope, $injector) {
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchEvents  =  {
            sView: 'past',
            iPage: 1,
            sSearch: '',
            iCatSearch: 'all_cate',
            iAmountOfevent: 5,
            sOrder: 'starttime'
        };
        return $scope;
    };
});