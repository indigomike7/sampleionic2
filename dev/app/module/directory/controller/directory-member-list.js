define([
    'global/base/ListController',
    'user/model/user'
], function(ListCtrl, ItemModel) {
    return function($scope, $injector, gettextCatalog) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            apiService: 'directory/fetch_members',
            noMoreText: gettextCatalog.getString('No more members'),
            itemModel: ItemModel,
            listById: false,
            getQueryData: function() {
                return $scope.$parent.listData;
            },
        });

        $scope.$watch('item', function(newVal, oldVal) {
            if (typeof(newVal.iTotalMember) != 'undefined' && typeof(oldVal.iTotalMember) != 'undefined' && newVal.iTotalMember != oldVal.iTotalMember) {
                $scope.doResetQuery();
            }
        }, true);
    };
});
