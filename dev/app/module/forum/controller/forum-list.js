define([
    'forum/model/forum',
    'global/base/ListController'
], function(ForumModel, ListCtrl) {

    return function($scope, $injector, gettext, gettextCatalog) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            notFoundText: gettextCatalog.getString('No forums found.'),
            noMoreText: gettextCatalog.getString('No more forums'),
            enableLoadMore: false,
            itemModel: ForumModel,
            apiService: 'forum/fetch',
            getQueryData: function() {
                return {};
            }
        });

        $scope.mapItems = function(data) {

            return (data || []).map(function(item) {
                return $.extend({}, $scope.itemModel, item, {
                    aSubForum: (item.aSubForum || []).map(function(sub) {
                        return $.extend({}, $scope.itemModel, sub);
                    })
                });
            });
        };
    };
});