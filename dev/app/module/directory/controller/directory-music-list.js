define([
    'global/base/ListController',
    'music/model/song'
], function(ListCtrl, ItemModel) {
    return function($scope, $injector, gettextCatalog) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more songs'),
            itemModel: ItemModel,
            listById: false,
            apiService: function() {
                if (typeof($scope.$parent.searchMusicApi) != 'undefined') {
                    return $scope.$parent.searchMusicApi;
                }

                return 'song/fetch';
            },
            getQueryData: function() {
                return $scope.$parent.searchMusic;
            }
        });
    };
});
