define([
    'global/base/BrowseController',
    'text!tpl/forum/forum-search.html'
], function(BrowseController, searchTemplate) {

    return function($scope, $injector, $state, gettext, gettextCatalog) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        var sHash = $state.params.sHash;

        $scope.searchData = JSON.parse(atob(sHash));
        $scope.listData = $.extend({}, $scope.searchData, {
            iAmountOfThread: 10,
            iPage: 1,
            isSearch: true            
        });
    };
});