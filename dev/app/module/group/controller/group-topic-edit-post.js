define([
    'group/model/discussion_post',
    'global/base/BaseController'
], function(PostModel, BaseController) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.dataReady = false;

        $scope.formData = {
            iPostId: $state.params.iPostId
        };

        $scope.getForm = function() {

            var postData = {
                iPostId: $state.params.iPostId
            };

            $http2.get('group/post_info', postData)
            .success($scope.doGetFormSuccess)
            .error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = $.extend({}, PostModel, data);
            $scope.formData.sBody = $scope.form.getBody();
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

            $scope.formData.iDeletePhoto = $scope.formData.iDeletePhoto ? 1 : 0;

            if ($scope.formData.photoPath && (!$scope.form.getAttachedPhotoUrl() || $scope.formData.iDeletePhoto)) {
            	$scope.doSaveWithPhoto();
            } else {
            	$scope.save();
            }
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            $http2.post('group/edit_post', $scope.formData)
            .success($scope.doSaveSuccess)
            .error($scope.doSaveError)
            .error($scope.doSaveError)
            .finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.goBack();
        };

        $scope.doSaveError = function() {

            console.error('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
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

        	$http2.upload('group/edit_post', $scope.formData.photoPath, $scope.formData)
        	.then(success, error);
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sBody) {
                bAlert && $modal.alert(gettextCatalog.getString('Content cannot be empty'));
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

        $scope.getForm();
    };
});