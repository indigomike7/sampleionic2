define([
    'page/model/page',
    'global/base/BrowseController',
    'text!tpl/page/page-search.html'
], function(PageModel, BrowseController, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site) {
        /**
         * check reqire permission
         */
        $site.requirePerm('pages.can_view_browse_pages');
        $scope.canCreate =  $site.getPerm('pages.can_add_new_pages');
        $scope.canAddPageCover =  $site.getPerm('pages.can_add_cover_photo_pages');

        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchPages = {
            sView: 'all',
            iPage: 1,
            sSearch: '',
            iCategoryId: "",
            iLimit: 20,
            sOrder: 'latest'
        };
        
        return $scope;
    };
});
