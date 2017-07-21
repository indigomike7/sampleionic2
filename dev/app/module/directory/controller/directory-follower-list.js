define([
    'global/base/ListController',
    'user/model/user'
], function(ListCtrl, ItemModel) {
    return function($scope, $injector, gettextCatalog) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            apiService: 'directory/fetch_followers',
            noMoreText: gettextCatalog.getString('No more followers'),
            itemModel: ItemModel,
            listById: false,
            getQueryData: function() {
                return $scope.$parent.listData;
            },
        });

        $scope.$watch('item', function(newVal, oldVal) {
            if (typeof(newVal.iTotalFollow) != 'undefined' && typeof(oldVal.iTotalFollow) != 'undefined' && newVal.iTotalFollow != oldVal.iTotalFollow) {
                $scope.doResetQuery();
            }
        }, true);
    };
});
