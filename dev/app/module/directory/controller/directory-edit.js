define([
    'global/helper',
    'directory/controller/directory-form'
], function(Helper, Ctrl) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.formApiData = {
            iBusinessId: $state.params.iBusinessId
        };

        $scope.formApi = 'directory/form_edit';
        $scope.saveApi = 'directory/edit';

        $scope.prepareFormData = function() {

            $scope.formData = $.extend($scope.formData, {
                iBusinessId: $state.params.iBusinessId,
                iCategoryId: $scope.form.aItem.iCategoryId || '',
                iPackageId: $scope.form.aItem.iPackageId || '',
                sTitle: Helper.unescapeHTML($scope.form.aItem.sTitle) || '',
                sShortDesc: Helper.unescapeHTML($scope.form.aItem.sShortDesc) || '',
                sDescription: Helper.unescapeHTML($scope.form.aItem.sDescription) || '',
                sSize: $scope.form.aItem.sSize,
                sWebAddress: $scope.form.aItem.aWebAddress[0] || '',
                sPhone: $scope.form.aItem.aPhone[0] || '',
                sFax: $scope.form.aItem.aFax[0] || '',
                sEmail: $scope.form.aItem.sEmail || '',
            });

            if ($scope.form.aItem.aLocations && $scope.form.aItem.aLocations.length) {
                $scope.tempData.aLocation = {
                    formatted_address: $scope.form.aItem.aLocations[0].sLocation,
                    geometry: {
                        location: new google.maps.LatLng($scope.form.aItem.aLocations[0].fLatitude, $scope.form.aItem.aLocations[0].fLongitude)
                    }
                };
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