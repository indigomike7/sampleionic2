define([
    'blog/controller/blog-all'
], function(BlogAllCtrl) {

    return function($scope, $injector, $state) {

        $injector.invoke(BlogAllCtrl, this, {
            $scope: $scope
        });

        $scope.blogListData = {
            iAmountOfBlog: 10,
            iCategoryId: 0,
            iPage: 1,
            sOrder: 'latest',
            sSearch: '',
            sView: 'my'
        };
    };
});