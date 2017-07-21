define([
    'global/base/BaseController'
], function(BaseController) {

    return function($scope, $injector, $state, $http2, $modal, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.formData = {};

        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $http2.post('setting/update_password', $scope.formData).success($scope.saveSuccess).error($scope.saveError).
            finally(function() {

                $scope.isProcessing = false;
            });
        };

        $scope.saveSuccess = function(data) {

            if (data.error_code) {
                console.warn('saveSuccess', arguments);
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Password successfully updated.'));

            $scope.goBack();
        };

        $scope.saveError = function() {

            console.warn('saveError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.old_password) {
                bAlert && $modal.alert(gettextCatalog.getString('Missing old password.'));
                return false;
            }

            if (!$scope.formData.new_password) {
                bAlert && $modal.alert(gettextCatalog.getString('Missing new password.'));
                return false;
            }

            if (!$scope.formData.confirm_password) {
                bAlert && $modal.alert(gettextCatalog.getString('Confirm your new password.'));
                return false;
            }

            if ($scope.formData.confirm_password != $scope.formData.new_password) {
                bAlert && $modal.alert(gettextCatalog.getString('Your confirmed password does not match your new password.'));
                return false;
            }

            return true;
        };
    };
});