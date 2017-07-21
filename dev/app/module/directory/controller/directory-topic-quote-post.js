define([
    'directory/model/discussion_post',
    'global/base/BaseController'
], function(PostModel, BaseController) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.dataReady = false;
        $scope.formData = {
            iTopicId: $state.params.iTopicId,
            iQuoteId: $state.params.iPostId || null
        };

        $scope.getForm = function() {

            var postData = {
                iTopicId: $state.params.iTopicId
            };

            $http2.get('directory/topic_info', postData)
            .success($scope.doGetFormSuccess)
            .error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || 'Can not load data from server');
                return $scope.goBack();
            }

            $scope.formData.iWatch = data.bIsWatching ? true : false;
            $scope.dataReady = true;
        };

        $scope.doGetFormError = function() {

            console.error('getForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.getQuotedPost = function() {

            var postData = {
                iPostId: $state.params.iPostId
            };

            $http2.get('directory/post_info', postData)
            .success($scope.getQuotedPostSuccess)
            .error($scope.getQuotedPostError);
        };

        $scope.getQuotedPostSuccess = function(data) {

            if (data.error_code) {
                return; // skip this error
            }

            $scope.quotedPost = $.extend({}, PostModel, data);
        };

        $scope.getQuotedPostError = function() {

            console.error('getQuotedPost', arguments); // skip this error
        };

        $scope.onSave = function() {

            if ($scope.isProcessing) {
                return;
            }

            if (!$scope.isValidData(true)) {
                return;
            }
            
            // changed this attribute make checkbox to be unchecked.
            // $scope.formData.iWatch = $scope.formData.iWatch ? 1 : 0;

            if ($scope.formData.photoPath) {
            	$scope.doSaveWithPhoto();
            } else {
            	$scope.save();
            }
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            $http2.post('directory/post_reply', $scope.formData)
            .success($scope.doSaveSuccess)
            .error($scope.doSaveError)
            .finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || 'Can not load data from server');
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

        	$http2.upload('directory/post_reply', $scope.formData.photoPath, $scope.formData).then(success, error);
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

        if ($state.params.iPostId) {
            $scope.getQuotedPost();
        }

        $scope.getForm();
    };
});