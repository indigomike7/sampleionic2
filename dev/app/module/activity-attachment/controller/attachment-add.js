define([
    'text!tpl/location/location-list-modal.html'
], function(locationListModalTpl) {

    return function($scope, $ionicPopup, $ionicActionSheet, $ionicModal, $http2, $site, gettext, gettextCatalog, $modal) {

        $scope.attachmentItem = {};
        $scope.attachmentData = {};

        $scope.isAttached = function() {

            if (!$.isEmptyObject($scope.attachmentItem)) {
                $modal.alert(gettextCatalog.getString('Cannot contain more than one item. Please remove the current selection before adding another item.'));
                return true;
            };

            return false;
        };

        $scope.onAddPhoto = function(sourceType) {

            if ($scope.isProcessingAttach) {
                return;
            }

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

            $scope.attachmentItem = {
                sPaths: $scope.attachmentItem.sPaths || [],
                sType: 'photo'
            };

            $scope.attachmentItem.sPaths.push(fileURI);

            $scope.$$phase || $scope.$apply();
        };

        $scope.removeAttachmentPhoto = function(path) {

            for (var i = $scope.attachmentItem.sPaths.length - 1; i >= 0; i--) {
                if ($scope.attachmentItem.sPaths[i] == path) {
                    $scope.attachmentItem.sPaths.splice(i, 1);
                }
            };

            if (!$scope.attachmentItem.sPaths.length) {
                $scope.attachmentItem = {};
            }
        };

        $scope.onAddVideo = function() {

            if ($scope.isAttached() || $scope.isProcessingAttach) {
                return;
            }

            var getSuccess = function(fileURI) {
                $scope.addVideoFromDeviceSuccess(fileURI);
            };

            var getFail = function(msg) {
                console.warn(msg);
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            };

            navigator.camera.getPicture(getSuccess, getFail, {
                quality: 50,
                destinationType: Camera.DestinationType.FILE_URI,
                sourceType: Camera.PictureSourceType.PHOTOLIBRARY,
                mediaType: Camera.MediaType.VIDEO
            });
        };

        $scope.addVideoFromDeviceSuccess = function(fileURI) {

            $scope.attachmentItem = {
                sPath: fileURI,
                sType: 'video_upload'
            };

            $scope.$$phase || $scope.$apply();
        };

        $scope.onAddLocation = function() {
            $scope.locationListModal && $scope.locationListModal.remove();

            $scope.locationListModal = $ionicModal.fromTemplate(locationListModalTpl, {
                scope: $scope
            });
            $scope.locationListModal.show();
        };

        $scope.hideLocationListModal = function() {

            $scope.locationListModal.hide();
        };

        $scope.addLocationSuccess = function(location) {

            $scope.hideLocationListModal();

            $scope.attachmentItem = $.extend(location, {
                sType: 'location'
            });
        };

        $scope.onAddLink = function() {

            if ($scope.isAttached() || $scope.isProcessingAttach) {
                return;
            };

            $modal.prompt('http://www.example.com', function(result) {
                if (result.buttonIndex == 2) {
                    return true;
                }
                if (!result.input1) {
                    return false;
                }
                $scope.attachmentData.sLink = result.input1;
                $scope.addLink();
            }, gettextCatalog.getString('Attach Link'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.addLink = function() {

            $scope.isProcessingAttach = true;

            // prepare url
            if (!$scope.attachmentData.sLink.match(/^[a-zA-Z]{1,5}:\/\//)) {
                $scope.attachmentData.sLink = 'http://' + $scope.attachmentData.sLink;
            }

            $http2.post('link/preview', $scope.attachmentData)
                .success($scope.addLinkSuccess)
                .error($scope.addLinkError)
                .finally(function() {
                    $scope.attachmentData = {};
                    $scope.isProcessingAttach = false;
                });
        };

        $scope.addLinkSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.attachmentItem = $.extend(data, {
                sTitle: data.sTitle || data.sLink,
                sDescription: data.sDescription || '',
                sType: 'core_link'
            });
        };

        $scope.addLinkError = function() {

            console.warn('addLinkError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.removeAttachment = function() {

            $scope.attachmentItem = {};
        };

        if ($scope.attachmentContext) {
        switch ($scope.attachmentContext.action) {
            case 'camera':
                $scope.onAddPhoto('CAMERA');
                break;
            case 'location':
                $scope.onAddLocation();
                break;
        }
        }

        $scope.$on('$destroy', function() {
            $scope.locationListModal && $scope.locationListModal.remove();
        });
    };
});