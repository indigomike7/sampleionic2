define([
    'global/base/BrowseController',
    'text!tpl/blog/blog-search-dir.html'
], function(Ctrl, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site) {

        $site.requirePerm('blog.view_blogs');

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.searchTemplate = searchTemplate;
        
        $scope.blogListData = {
            iAmountOfBlog: 10,
            iCategoryId: 0,
            iGroup: 1,
            sOrder: 'latest',
            sSearch: '',
            iItemId: $scope.item.getId(),
            sModule: 'groups',
            sView: 'all'
        };

        $scope.notFoundText = 'No blogs found'

        return $scope;
    };
});