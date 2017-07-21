define([
    'forum/model/thread',
    'global/base/ListController'
], function(ThreadModel, ListCtrl) {

    return function($scope, $injector, gettext, gettextCatalog) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            notFoundText: gettextCatalog.getString('No threads found.'),
            noMoreText: gettextCatalog.getString('No more threads'),
            itemModel: ThreadModel,
            apiService: 'forum/detail',
            getQueryData: function() {
                return $scope.$parent.listData;
            }
        });

        $scope.mapItems = function(data) {

            var aThread = data.aThread || [];

            return aThread.map(function(item) {
                return $.extend({}, $scope.itemModel, item);
            });
        };
    };
});