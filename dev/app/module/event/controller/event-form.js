define([
    'event/model/event',
    'global/base/BaseController',
    'moment'
], function(EventModel, Ctrl, Moment) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {};
        $scope.tmpData = {};
        $scope.formApi = '';
        $scope.formApiData = {};
        $scope.saveApi = '';

        $scope.getForm = function() {

            $http2.post($scope.formApi, $scope.formApiData)
                .success($scope.doGetFormSuccess)
                .error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.updateFormData(data);
            $scope.dataReady = true;
        };

        $scope.doGetFormError = function() {

            console.error('getForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.onSave = function() {

            if ($scope.isProcessing) {
                return;
            }

            if (!$scope.isValidData(true)) {
                return;
            }

            if (ionic.Platform.isIOS()) {
                angular.extend($scope.formData, {
                    start_date: Moment($scope.tmpData.start_date.getTime()).format('YYYY-MM-DD'),
                    start_time: Moment($scope.tmpData.start_time.getTime()).format('HH:mm:00'),
                    end_date: Moment($scope.tmpData.end_date.getTime()).format('YYYY-MM-DD'),
                    end_time: Moment($scope.tmpData.end_time.getTime()).format('HH:mm:00')
                });
            }

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.save();
            }
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            $http2.post($scope.saveApi, $scope.formData)
                .success($scope.doSaveSuccess)
                .error($scope.doSaveError)
                .finally(function() {
                    $scope.isProcessing = false;
                });
        };

        $scope.doSaveWithPhoto = function() {

            var success = function(data) {
                $scope.doSaveSuccess(data);
            };

            var error = function(error) {
                console.error('doSaveWithPhoto', arguments);

                if (error.code == FileTransferError.ABORT_ERR) {
                    return $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $modal.alert(gettextCatalog.getString('Can not upload file. Please try again later'));
            };

            $http2.upload($scope.saveApi, $scope.formData.photoPath, $scope.formData).then(success, error);
        };

        $scope.doSaveError = function() {

            console.error('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.title) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter Event Name.'));
                return false;
            }

            if ($scope.formData.title.length > 64) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter Event Name no more than 64 characters.'));
                return false;
            }

            if (!$scope.formData.start_date) {
                bAlert && $modal.alert(gettextCatalog.getString('Please select start date.'));
                return false;
            }

            if (!$scope.formData.start_time) {
                bAlert && $modal.alert(gettextCatalog.getString('Please select start time.'));
                return false;
            }

            if (!$scope.formData.end_date) {
                bAlert && $modal.alert(gettextCatalog.getString('Please select end date.'));
                return false;
            }

            if (!$scope.formData.end_time) {
                bAlert && $modal.alert(gettextCatalog.getString('Please select end time.'));
                return false;
            }

            var isValidTime = false;
            if (ionic.Platform.isIOS()) {
                if ($scope.tmpData.end_date.getTime() > $scope.tmpData.start_date.getTime() || ($scope.tmpData.end_date.getTime() == $scope.tmpData.start_date.getTime() && $scope.tmpData.end_time.getTime() > $scope.tmpData.start_time.getTime())) {
                    isValidTime = true;
                }
            } else {
                var event_start = new Date(($scope.formData.start_date + ' ' + $scope.formData.start_time).replace(/-/g, '/'));
                var event_end = new Date(($scope.formData.end_date + ' ' + $scope.formData.end_time).replace(/-/g, '/'));

                if (event_end.getTime() > event_start.getTime()) {
                    isValidTime = true;
                }
            }

            if (!isValidTime) {
                bAlert && $modal.alert(gettextCatalog.getString('End Time must be greater than Start Time.'));
                return false;
            }

            return true;
        };

        $scope.onAddPhoto = $scope._setting($scope, function() {

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
        });

        $scope.doAddPhoto = function(sourceType) {

            sourceType = sourceType || 'PHOTOLIBRARY';

            var getSuccess = function(fileURI) {
                $scope.doAddPhotoSuccess(fileURI);
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

        $scope.doAddPhotoSuccess = function(fileURI) {

            $scope.formData.photoPath = fileURI;

            $scope.$$phase || $scope.$apply();
        };

        $scope.onRemovePhoto = function() {

        	$scope.formData.photoPath = null;
        };
    };
});