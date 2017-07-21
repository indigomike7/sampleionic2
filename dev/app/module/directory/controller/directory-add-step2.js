define([
    'directory/controller/directory-form'
], function(Ctrl) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.sPurpose = $state.params.sPurpose;
        $scope.formApiData = {
            iPackageId: ($scope.sPurpose == 'personal') ? $state.params.iPackageId : 0
        };

        //set init values
        $scope.formData = {
            sType: ($scope.sPurpose == 'claim') ? 'claiming' : 'business',
        };
        $scope.formApi = 'directory/form_add';
        $scope.saveApi = 'directory/add';

        $scope.prepareFormData = function() {
            if (typeof($scope.form.categoryOptions) != 'undefined' && $scope.form.categoryOptions.length){
                $scope.formData.iCategoryId = $scope.form.categoryOptions[0].id;
                $scope.formData.iPackageId = $state.params.iPackageId;
            }

            if (typeof($scope.form.sizeOptions) != 'undefined' && $scope.form.sizeOptions.length) {
                $scope.formData.sSize = $scope.form.sizeOptions[0];
            }
        };

        $scope.saveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || 'Can not load data from server');
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.goBack();
            //$location.path(data.iBusinessId ? ('app/directory/' + data.iBusinessId) : 'app/directory/my');
        };

        $scope.getForm();
    };
});