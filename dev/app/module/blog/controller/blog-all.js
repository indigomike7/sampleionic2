define([
    'blog/model/blog',
    'global/base/BrowseController',
    'text!tpl/blog/blog-search-dir.html'
], function(BlogModel, BrowseController, searchTemplate) {

    return function($scope, $injector, $http2, $site, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $site.requirePerm('blog.view_blogs');
        $scope.canAddBlog = $site.getPerm('blog.add_new_blog');

        $scope.blogListData = {
            iAmountOfBlog: 10,
            iCategoryId: 0,
            iPage: 1,
            sOrder: 'latest',
            sSearch: '',
            sView: 'all'
        };

    };
});
