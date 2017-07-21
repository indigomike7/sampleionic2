define([
    'global/base/BaseController',
    'global/validator'
], function(BaseController, Validator) {

    return function($scope, $injector, $state, $http2, $modal, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.formData = {
            invisible: $state.params.oldvalue == 'true' ? true : false
        };

        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $http2.post('setting/update_invisible_mode', $scope.formData).success($scope.saveSuccess).error($scope.saveError).
            finally(function() {

                $scope.isProcessing = false;
            });
        };

        $scope.saveSuccess = function(data) {

            if (data.error_code) {
                console.warn('saveSuccess', arguments);
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Privacy settings successfully updated.'));

            $scope.goBack();
        };

        $scope.saveError = function() {

            console.warn('saveError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            return true;
        };
    };
});