define([
    'global/base/BaseController'
], function(Ctrl) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettextCatalog, $viewer, $location) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.form = {
            bIsCreator: 0,
            aPackages: []
        };
        $scope.dataReady = false;
        $scope.formData = {
            iPackageId: 0
        };

        $scope.dataReady = false;

        $scope.getForm = function() {

            $http2.post('directory/form_add_step1')
                .success($scope.doGetFormSuccess)
                .error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            // merge form option
            $.extend($scope.form, data);

            if (!$scope.form.bIsCreator && $scope.form.aPackages.length == 0) {
                $modal.alert(gettextCatalog.getString('There is no packages'));
                $scope.goBack();
            }

            if ($scope.form.bIsCreator && $scope.form.aPackages.length == 0) {
                $location.path('app/directory/add/claim/0');
            }

            $scope.formData.sPurpose = 'personal';

            if ($scope.form.aPackages.length) {
                $scope.formData.iPackageId = $scope.form.aPackages[0].iPackageId;
            }

            $scope.dataReady = true;
        };

        $scope.doGetFormError = function() {

            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.saveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || 'Can not load data from server');
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $location.path(data.iCompanyId ? ('app/ynjobposting_company/' + data.iCompanyId) : 'app/ynjobposting_companies/my');
        };

        $scope.onNextStep = function() {

            $location.path('app/directory/add/' + $scope.formData.sPurpose + '/' + $scope.formData.iPackageId);
        };

        $scope.getForm();

    };
});