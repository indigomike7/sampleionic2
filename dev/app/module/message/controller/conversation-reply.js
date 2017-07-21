define([
	'message/controller/conversation-detail'
], function(ConversationDetailCtrl) {

	return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

		$injector.invoke(ConversationDetailCtrl, this, {
			$scope: $scope
		});

        $scope.iItemId = $state.params.id;

		$scope.messageData = {
            iItemId: $scope.iItemId
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.messageData.sText) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter the message'));
                return false;
            }

            return true;
        };

        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            if ($scope.photoURI) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.save();
            }
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            $http2.post('message/reply', $scope.messageData, {
                timeout: 0
            })
            .success($scope.doSaveSuccess)
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

            $location.path('app/message/' + $scope.iItemId);
        };

        $scope.doSaveError = function() {

            console.error('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.doSaveWithPhoto = function() {

            var success = function(data) {
                $scope.uploadPhotoSuccess(data);
            };

            var error = function(error) {
                console.error('message/attach', arguments);

                if (error.code == FileTransferError.ABORT_ERR) {
                    return $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $modal.alert(gettextCatalog.getString('Can not upload file. Please try again later'));
            };

            $http2.upload('message/attach', $scope.photoURI, {}).then(success, error);
        };

        $scope.uploadPhotoSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (!data.sImagePath) {
                console.warn('message/attach', data);
                return $modal.alert(gettextCatalog.getString('Can not upload file. Please try again later'));
            }

            $scope.messageData.sText += ' ' + data.sImagePath;
            $scope.messageData.attachmentData = {
                type: 'photo'
            };

            $scope.save();
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
                $scope.photoURI = fileURI;
                $scope.$$phrase || $scope.$apply();
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
            $scope.photoURI = null;
        };
	};
});