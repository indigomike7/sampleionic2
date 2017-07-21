define([
    'global/base/BrowseController',
    'text!tpl/directory/directory-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {
        /**
         * check require permission
         */
        $site.requirePerm('directory.can_view_business');
        $scope.canAddItem = $site.getPerm('directory.can_create_business') || $site.getPerm('directory.can_create_business_for_claiming');

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.listData = {
            iLimit: 10,
            iPage: 1,
            sView: 'myfavoritebusinesses',
            sSearch: '',
            sOrder: 'newest'
        };
    };
});
