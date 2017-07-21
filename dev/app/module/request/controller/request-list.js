define([
    'request/model/request',
    'global/base/ListController'
], function(RequestModel, ListCtrl) {
    
    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            itemModel: RequestModel,
            apiService: 'notification/friendrequested',
            listData: {
                iPage: 1,
                sView: 'all'
            }
        });
    };
});