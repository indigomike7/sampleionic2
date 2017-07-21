define([
    'directory/controller/directory-item'
], function(DirectoryItemCtrl) {
    return function($scope, $http2, $injector, gettextCatalog, $modal, $site) {
        $injector.invoke(DirectoryItemCtrl, this, {
            $scope: $scope
        });

        $scope.getActions = function() {
            var btns = [];

            if ($scope.item.bCanClaim) {
                btns.push({
                    text: gettextCatalog.getString('Claim This Business'),
                    action: $scope.claim
                });
            }

            return btns;
        };

        $scope.claim = function() {
            var onSuccess = function(data) {
                if (data.error_code) {
                    $site.debug > 2 && console.warn(api, arguments);
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                if (data.message) {
                    $modal.toast(data.message);
                }
                angular.extend($scope.item, data.aItem);
                $scope.$broadcast('itemUpdate');

                if(typeof $scope.items != 'undefined')
                    $scope.items.deleteItem($scope.item.getId());
            };

            $scope.quickPost('directory/claim', null, onSuccess);
        };
    };
});
