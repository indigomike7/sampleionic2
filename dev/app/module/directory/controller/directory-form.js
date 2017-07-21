define([
    'global/base/BaseController',
    'global/validator'
], function(BaseController, Validator) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {};

        $scope.formApi = '';
        $scope.formApiData = {};
        $scope.tempData = {};
        $scope.saveApi = '';

        $scope.getForm = function() {

            $http2.post($scope.formApi, $scope.formApiData).success($scope.getFormSuccess).error($scope.getFormError);
        };

        $scope.getFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || 'Can not load data from server');
                return $scope.goBack();
            }

            $scope.form = data;

            $scope.prepareFormData();

            $scope.dataReady = true;
        };

        $scope.getFormError = function() {

            console.error('getForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.prepareFormData = function() {

        };

        $scope.removeLocation = function() {

            delete($scope.tempData.aLocation);
        };

        $scope.prepareSendData = function() {
            var aLocation = $scope.tempData.aLocation;
            if (aLocation) {
                $.extend($scope.formData, {
                    sLocationAddress: aLocation.formatted_address,
                    sLat: aLocation.geometry.location.lat(),
                    sLong: aLocation.geometry.location.lng()
                });
            }

            if ($scope.formData.sWebsite && !$scope.formData.sWebsite.match(/^[a-zA-Z]{1,5}:\/\//)) {
                $scope.formData.sWebsite = 'http://' + $scope.formData.sWebsite;
            }
        };

        $scope.save = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.prepareSendData();

            if ($scope.formData.sProfilePhoto) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.doSave();
            }            
        };

        $scope.doSave = function() {
            $scope.isProcessing = true;

            $http2.post($scope.saveApi, $scope.formData).success($scope.saveSuccess).error($scope.saveError).finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveWithPhoto = function() {
            $http2.upload($scope.saveApi, $scope.formData.sProfilePhoto, $scope.formData).then($scope.saveSuccess, $scope.saveError);
        };

        $scope.saveSuccess = function(data) {

            // will be overridden
        };

        $scope.saveError = function(error) {

            console.warn('save', arguments);

            if (error.code == FileTransferError.ABORT_ERR) {
                return $modal.toast(gettextCatalog.getString('Canceled'));
            }

            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Business Name is required'));
                return false;
            }

            if (!$scope.formData.iCategoryId) {
                bAlert && $modal.alert(gettextCatalog.getString('Category is required'));
                return false;
            }

            if (!$scope.formData.sShortDesc) {
                bAlert && $modal.alert(gettextCatalog.getString('Short Description is required'));
                return false;
            }

            if (!$scope.tempData.aLocation) {
                bAlert && $modal.alert(gettextCatalog.getString('Address is required'));
                return false;
            }

            if (!$scope.formData.sPhone) {
                bAlert && $modal.alert(gettextCatalog.getString('Phone is required'));
                return false;
            }

            if (!$scope.formData.sEmail) {
                bAlert && $modal.alert(gettextCatalog.getString('Email is required'));
                return false;
            }

            return true;
        };

        $scope.onAddPhoto = function() {

            $scope._setting($scope, function() {

                return [{
                    text: gettextCatalog.getString('Take Photo'),
                    action: function() {
                        $scope.doAddPhoto('CAMERA');
                    }
                }, {
                    text: gettextCatalog.getString('Select From Gallery'),
                    action: function() {
                        $scope.doAddPhoto('PHOTOLIBRARY');
                    }
                }];
            })();
        };

        $scope.doAddPhoto = function(sourceType) {

            sourceType = sourceType || 'PHOTOLIBRARY';

            var getSuccess = function(fileURI) {
                $scope.formData.sProfilePhoto = fileURI;
                $scope.$$phase || $scope.$apply();
            };

            var getFail = function(msg) {
                console.warn(msg);
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            };

            navigator.camera.getPicture(getSuccess, getFail, {
                quality: 50,
                destinationType: navigator.camera.DestinationType.FILE_URI,
                sourceType: Camera.PictureSourceType[sourceType],
                encodingType: Camera.EncodingType.JPEG,
                correctOrientation: true,
                targetWidth: $site.imgTargetSize,
                targetHeight: $site.imgTargetSize
            });
        };

        $scope.onRemovePhoto = function() {

            delete($scope.formData.sProfilePhoto);
        };
    };
});