define([
    'global/base/BaseController',
    'global/helper'
], function(BaseController, Helper) {

    return function($scope, $injector, $state, $http2, $modal, $rootScope, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {};

        $scope.fetchData = function() {

            $scope.isLoading = true;

            $http2.post('setting/form_full_name').success($scope.fetchDataSuccess).error($scope.fetchDataError).
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
            $scope.form.canChangeFullName = ($scope.form.perms.total_times_can_change_own_full_name == 0 || $scope.form.total_full_name_change < $scope.form.perms.total_times_can_change_own_full_name);
            $scope.formData.full_name = Helper.unescapeHTML($scope.form.full_name);
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchDataError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $http2.post('setting/update_full_name', $scope.formData).success($scope.saveSuccess).error($scope.saveError).
            finally(function() {

                $scope.isProcessing = false;
            });
        };

        $scope.saveSuccess = function(data) {

            if (data.error_code) {
                console.warn('saveSuccess', arguments);
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Account settings updated.'));

            $rootScope.$broadcast('user:updateInfo', $scope.formData);

            $scope.goBack();
        };

        $scope.saveError = function() {

            console.warn('saveError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.full_name) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide your full name.'));
                return false;
            }

            return true;
        };

        $scope.fetchData();
    };
});