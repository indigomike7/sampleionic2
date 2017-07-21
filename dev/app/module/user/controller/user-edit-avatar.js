define([
    'global/base/BaseController',
], function(BaseController) {

    return function($scope, $rootScope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.data = {
            iUserId: $state.params.iUserId
        };

        $scope.getPicture = function(location) {

            var success = function(fileURI) {
                $scope.getPictureSuccess(fileURI);
                $scope.$$phase || $scope.$apply();
            };

            var fail = function(msg) {
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
                $scope.goBack();
                $scope.$$phase || $scope.$apply();
            };

            if (typeof(navigator.camera) != 'undefined') {
                navigator.camera.getPicture(success, fail, {
                    quality: 50,
                    destinationType: Camera.DestinationType.FILE_URI,
                    sourceType: Camera.PictureSourceType.PHOTOLIBRARY,
                    encodingType: Camera.EncodingType.JPEG,
                    mediaType: Camera.MediaType.PICTURE,
                    correctOrientation: true,
                    targetWidth: $site.imgTargetSize,
                    targetHeight: $site.imgTargetSize
                });
            }
        };

        $scope.getPictureSuccess = function(fileURI) {

            $scope.imgSrc = fileURI;

            $('#user_photo_holder').html('<img id="user_photo" class="photo-cropping" src="' + fileURI + '" />');

            setTimeout(function() {
                $scope.initJcrop($('#user_photo'));
            }, 200);
        };

        $scope.initJcrop = function($ele) {

            var iViewWidth = $ele.width();
            var iViewHeight = $ele.height();

            $scope.data.iWidth = iViewWidth;
            $scope.data.iHeight = iViewHeight;

            var iMinSize = 48;
            if (iViewWidth < 48 || iViewHeight < 48) {
                iMinSize = Math.min(iViewWidth, iViewHeight);
            }

            var aCoords = [0, 0, 100, 100];

            if (iViewWidth > 100 && iViewHeight > 100) {
                aCoords[0] = iViewWidth / 2 - 50;
                aCoords[1] = iViewHeight / 2 - 50;
                aCoords[2] = aCoords[0] + 100;
                aCoords[3] = aCoords[1] + 100;
            } else {
                if (iViewHeight < iViewWidth) {
                    aCoords[0] = iViewWidth / 2 - iViewHeight / 2;
                    aCoords[1] = 0;
                    aCoords[2] = aCoords[0] + iViewHeight;
                    aCoords[3] = iViewHeight;
                } else {
                    aCoords[0] = 0;
                    aCoords[1] = iViewHeight / 2 - iViewWidth / 2;
                    aCoords[2] = iViewWidth;
                    aCoords[3] = aCoords[0] + iViewWidth;
                }
            }

            $scope.data.sCoordinates = aCoords[0] + ':' + aCoords[1] + ':' + iViewWidth + ':' + iViewHeight;

            var getCoords = function(c) {
                $scope.data.sCoordinates = (c.w > 0 && c.h > 0) ? (c.x + ':' + c.y + ':' + c.w + ':' + c.h) : '';
            };

            $ele.Jcrop({
                aspectRatio: 1 / 1,
                minSize: [iMinSize, iMinSize],
                setSelect: aCoords,
                onSelect: getCoords,
                onChange: getCoords
            });
        };

        $scope.save = function() {

            var success = function(data) {
                $scope.doSaveSuccess(data);
            };

            var error = function(error) {
                console.warn('save', arguments);

                if (error.code == FileTransferError.ABORT_ERR) {
                    return $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $modal.alert(gettextCatalog.getString('Can not upload file. Please try again later'));
            };

            $http2.upload('user/edit_avatar', $scope.imgSrc, $scope.data).then(success, error);
        };

        $scope.doSaveSuccess = function(data) {

            console.log('doSaveSuccess', data);

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Edit sucessfully'));
            $rootScope.$broadcast('user:updateAvatar', data);
            $scope.goBack();
        };

        $scope.getPicture();
    };
});