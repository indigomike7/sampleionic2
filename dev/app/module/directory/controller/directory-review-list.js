define([
    'global/base/ListController',
    'directory/model/directory-review'
], function(ListCtrl, ItemModel) {
    return function($scope, $injector, gettextCatalog) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            apiService: 'directory/fetch_review',
            noMoreText: gettextCatalog.getString('No more reviews'),
            itemModel: ItemModel,
            listById: false,
            getQueryData: function() {
                return $scope.$parent.listData;
            },
        });
    };
});
