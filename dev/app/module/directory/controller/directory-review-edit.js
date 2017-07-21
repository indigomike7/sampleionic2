define([
    'global/base/BaseController',
], function(BaseController) {
    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog) {
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.formData = {
            iReviewId: $state.params.reviewId
        };

        $scope.getForm = function() {
            var onSuccess = function(data) {
                if (data.error_code) {
                    $site.debug > 2 && console.warn('getForm', arguments);
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }

                $.extend($scope.formData, data.aItem);
                $scope.dataReady = true;
            };

            var onError = function() {
                $site.debug > 2 && console.warn('getForm', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.goBack();
            };

            $http2.post('directory/form_edit_review', $scope.formData).success(onSuccess).error(onError);
        };

        $scope.onSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $http2.post('directory/edit_review', $scope.formData).success($scope.onSaveSuccess).error($scope.onSaveError).finally(function() {
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
            $site.debug > 2 && console.warn('getForm', arguments);
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

        $scope.getForm();
    };
});
