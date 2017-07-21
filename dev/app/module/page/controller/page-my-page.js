define([
    'page/model/page',
    'global/base/BrowseController',
    'text!tpl/page/page-search.html'
], function(PageModel, BrowseController, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site) {
        /**
         * check reqire permission
         */
        //$site.requirePerm('page.view');
        $scope.canCreate =  $site.getPerm('pages.can_add_new_pages');
        $scope.canAddPageCover =  $site.getPerm('pages.can_add_cover_photo_pages');

        /**
         * some other case
         */
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchPages = {
            sView: 'my',
            iPage: 1,
            sSearch: '',
            iCategoryId: 0,
            iLimit: 5,
            sOrder: 'latest'
        };
        
        return $scope;
    };
});
