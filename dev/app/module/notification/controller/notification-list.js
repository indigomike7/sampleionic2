define([
    'notification/model/notification',
    'global/base/ListController'
], function(Model, Ctrl) {
    
    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more notifications'),
            itemModel: Model,
            apiService: 'notification/fetch',
            listById: true,
            listData: {
                iMinId: 0,
                iMaxId: 0,
                iLimit: 20
            }
        });
    };
});