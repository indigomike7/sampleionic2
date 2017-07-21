define([
    'global/base/ItemController'
], function(ItemController) {
    return function($scope, $injector, gettext, gettextCatalog, $modal, $site, $http2, $rootScope) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.onConfirm = function() {

            if ($scope.isProcessingConfirm) {
                return;
            }

            $scope.isProcessingConfirm = true;

            var postData = {
                iUserId: $scope.item.getId()
            };

            $http2.post('friend/confirm', postData)
            .success($scope.confirmSuccess)
            .error($scope.confirmError)
            .finally(function() {
                $scope.isProcessingConfirm = false;
            });
        };

        $scope.confirmSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.isConfirmed = true;
        };

        $scope.confirmError = function() {

            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.onDeny = function() {

            if ($scope.isProcessingDeny) {
                return;
            }

            $scope.isProcessingDeny = true;

            var postData = {
                iUserId: $scope.item.getId()
            };

            $http2.post('friend/deny', postData)
            .success($scope.denySuccess)
            .error($scope.denyError)
            .finally(function() {
                $scope.isProcessingDeny = false;
            });
        };

        $scope.denySuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.isDenied = true;
        };

        $scope.denyError = function() {

            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
    };
});