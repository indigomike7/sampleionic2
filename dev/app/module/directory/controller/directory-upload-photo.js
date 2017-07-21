define([
    'global/base/BaseController'
], function(Ctrl) {
    return function($scope, $injector, $location, $site, $http2, $modal, gettext, gettextCatalog, $state) {

        $injector.invoke(Ctrl, this, {$scope: $scope});
        var item = $scope.item;

        $scope.photos  = [];
        $scope.isProcessing = false;
        $scope.iNewPhotoIds = [];

        $scope.formData = {
            sSubjectType: 'directory',
            iSubjectId: $state.params.id,
        };

        $scope.onSave = function(){

            if($scope.isProcessing){
                $site.debug > 2 && console.log('in processing');
                return ;
            }

            if(!$scope.photos.length){
                $modal.alert(gettextCatalog.getString('Please select photos to upload'));
                return ;
            }else{
                $scope.isProcessing = true;
                $scope.doSaveWithPhoto(0);
            }
        };

        $scope.doSaveWithPhoto = function(index) {

            if(index >= $scope.photos.length){
                $site.debug > 2 && console.log(gettextCatalog.getString('Upload all photos'));
                $scope.doPostFeed();
                $scope.isProcessing = false;
                $scope.goBack();
                return ;
            }

            var sPath = $scope.photos[index].sPath;

            var success = function(data) {
                $scope.iNewPhotoIds.push(data.iPhotoId);
                $scope.doSaveWithPhoto(index+1);
            };

            var error = function(error) {
                console.error('doSaveWithPhoto', arguments);
                $scope.isProcessing = false;

                if (error.code == FileTransferError.ABORT_ERR) {
                    return $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $modal.alert(gettextCatalog.getString('Can not upload file. Please try again later'));
            };

            var sendData = {
                iItemId: $state.params.id,
                sModule: 'directory',
            };

            $http2.upload('photo/upload', sPath, sendData)
                .then(success, error);
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

            $scope.photos.push({
                sPath: fileURI,
            });

            $scope.$$phase || $scope.$apply();
        };

        $scope.onRemovePhoto = function(index) {
            $scope.photos.splice(index, 1);
            $scope.$$phase || $scope.$apply();
        };

        $scope.doPostFeed = function () {

            $scope.formData.sPhotoIds = $scope.iNewPhotoIds.join(',');
            $http2.post('photo/postfeed', $scope.formData).success(function(data){
                $site.debug > 2 && console.log(data);
            }).error(function(){
            }).finally(function(){
            });
        }

        return $scope;
    };
});