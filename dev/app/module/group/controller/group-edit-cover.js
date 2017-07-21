define([
    'global/base/BaseController',
], function(BaseController) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.data = {
            sAction: 'edit',
            iGroupId: $state.params.iGroupId
        };

        $scope.getPicture = function(location) {

            var success = function(fileURI) {
                $scope.imgSrc = fileURI;
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

            $http2.upload('groups/edit_cover', $scope.imgSrc, $scope.data).then(success, error);
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Edit successfully'));
            $scope.goBack();
        };

        $scope.getPicture();
    };
});