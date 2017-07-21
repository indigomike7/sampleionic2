define([
    'global/base/BrowseController',
    'text!tpl/forum/forum-search.html'
], function(BrowseController, searchTemplate) {

    return function($scope, $injector, $location, gettext, gettextCatalog) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.searchData = {
            sSearchType: 'thread'
        };
    };
});