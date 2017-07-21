define([
    'forum/model/post',
    'global/base/ListController'
], function(PostModel, ListCtrl) {

    return function($scope, $injector, gettext, gettextCatalog) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            notFoundText: gettextCatalog.getString('No posts found.'),
            noMoreText: gettextCatalog.getString('No more posts'),
            itemModel: PostModel,
            apiService: 'forum/search',
            getQueryData: function() {
                return $scope.$parent.listData;
            }
        });

        $scope.mapItems = function(data) {

            var aPost = data.aPost || [];

            return aPost.map(function(item) {
                return $.extend({}, $scope.itemModel, item);
            });
        };
    };
});