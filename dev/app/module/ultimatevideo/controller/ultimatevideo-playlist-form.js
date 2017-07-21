define([
    'ultimatevideo/model/playlist',
    'global/base/BaseController',
    'moment'
], function(PlaylistModel, Ctrl, Moment) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {};
        $scope.tmpData = {};
        $scope.saveApi = '';

        $scope.onSave = function() {

            if ($scope.isProcessing) {
                return;
            }

            if (!$scope.isValidData(true)) {
                return;
            }

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.save();
            }
        };

        $scope.isValidData = function(bAlert){
            console.log('isValidData', $scope.formData);
            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Please input playlist name'));
                return false;
            }
            if (!$scope.formData.iCategoryId || $scope.formData.iCategoryId == '0') {
                bAlert && $modal.alert(gettextCatalog.getString('Please choose select category the playlist will belong to'));
                return false;
            }
            return true;
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

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Playlist has been created successfully'));

            if (data.iPlaylistId) {
                var modelType = typeof(data.sModelType) != 'undefined' ? data.sModelType : 'ultimatevideo_playlist';
                return $location.path('app/' + modelType + '/' + data.iEventId);
            }

            $location.path('app/ultimatevideo/playlist');
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