define([
    'directory/controller/directory-item'
], function(DirectoryItemCtrl) {
    return function($scope, $http2, $injector, gettextCatalog, $modal) {
        $injector.invoke(DirectoryItemCtrl, this, {
            $scope: $scope
        });

        $scope.getActions = function() {
            var btns = [];

            btns.push({
                text: gettextCatalog.getString('Unfavourite'),
                action: $scope.unfavourite
            });

            return btns;
        };

        $scope.unfavourite = function() {
            var api = 'directory/unfavourite';

            var _sendData = {
                iBusinessId: $scope.item.getId()
            };

            var onSuccess = function(data) {
                if (data.error_code) {
                    $site.debug > 2 && console.warn(api, arguments);
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                if (data.message) {
                    $modal.toast(data.message);
                }

                $scope.items.deleteItem($scope.item.getId());
            };

            var onError = function() {
                $site.debug > 2 && console.warn(api, arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post(api, _sendData).success(onSuccess).error(onError);
        };
    };
});
