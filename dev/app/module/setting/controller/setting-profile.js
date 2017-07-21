define([
    'global/base/BaseController'
], function(BaseController) {

    return function($scope, $injector, $state, $http2, $modal, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {
            privacy: {},
            special: {}
        };

        $scope.fetchData = function() {

            $scope.isLoading = true;

            $http2.post('setting/form_privacy_profile').success($scope.fetchDataSuccess).error($scope.fetchDataError).
            finally(function() {

                $scope.isLoading = false;
            });
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                console.warn('fetchDataSuccess', arguments);
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;
            $scope.prepareFormData();
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchDataError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.prepareFormData = function() {

            var setting;
            for (var i = 0; i < $scope.form.privacy.length; i++) {
                setting = $scope.form.privacy[i];
                $scope.formData.privacy[setting.key] = parseInt(setting.default);
            };

            $scope.formData.special.dob_setting = parseInt($scope.form.special.dob_setting);
        };

        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $http2.post('setting/update_privacy_profile', $scope.formData).success($scope.saveSuccess).error($scope.saveError).
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

        $scope.fetchData();
    };
});