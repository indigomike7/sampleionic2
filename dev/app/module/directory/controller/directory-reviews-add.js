define([
    'global/base/BaseController',
], function(BaseController) {
    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.formData = {
            iBusinessId: $state.params.id,
            iRateValue: 0
        };

        $scope.onSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $http2.post('directory/add_review', $scope.formData).success($scope.onSaveSuccess).error($scope.onSaveError).finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.onSaveSuccess = function(data) {
            if (data.error_code) {
                $site.debug > 2 && console.warn('onSave', arguments);
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.goBack();
        };

        $scope.onSaveError = function() {
            $site.debug > 2 && console.warn('onSave', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.setMaxRate = function(num) {
            var array = [];
            for (i = 0; i < num; i++) {
                array.push(i);
            }
            return array;
        };

        $scope.setRateValue = function(value) {
            if ($scope.isProcessing) {
                return;
            }

            $scope.formData.iRateValue = value;
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.formData.iRateValue) {
                bAlert && $modal.alert(gettextCatalog.getString('Rate is required'));
                return false;
            }

            if (!$scope.formData.sContent) {
                bAlert && $modal.alert(gettextCatalog.getString('Review content is required'));
                return false;
            }

            return true;
        };
    };
});
