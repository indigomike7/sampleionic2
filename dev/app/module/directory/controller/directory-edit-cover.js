define([
    'global/base/BaseController',
],function(BaseController){

    return function($http2, $q, $state, $scope, $site, gettext, gettextCatalog, $injector, $modal){

        $injector.invoke(BaseController, this, {$scope: $scope});

        var iBusinessId = $state.params.iBusinessId;

        $scope.removePhotos = new Array();
        $scope.photoItems = new Array();
        $scope.dataReady = false;
        $scope.isProcessing  = false;
        $scope.form = {};

        $scope.onSave = function() {

            if ($scope.isProcessing) {
                return;
            }

            $scope.isProcessing  = true;

            if ($scope.removePhotos.length) {
                $scope.doRemovePhotos();
            } else {
                $scope.doUploadPhotos();
            }
        };

        $scope.doRemovePhotos = function() {

            var sendData = {
                aCoverIds: $scope.removePhotos,
                iBusinessId: iBusinessId
            };

            $http2.post('directory/delete_cover', sendData)
                .success(function(data){
                    if (data.error_code) {
                        $modal.alert(data.error_message || 'Can not load data from server');
                        return $scope.goBack();
                    }
                    $scope.doUploadPhotos();
                })
                .error(function(){
                    console.error('Remove photos', arguments);
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                    $scope.goBack();
                });
        };

        $scope.doUploadPhotos = function() {

            var photos =  $scope.prepareUploadPhotos();

            if(photos.length){
                $scope.onUploadPhotos(0, photos);
            }else{
                $scope.goBack();
            }
        };

        $scope.onUploadPhotos = function(index, photos){

            if(index >= photos.length){
                $scope.goBack();
                return ;
            }

            var fileURI  =  photos[index];

            var sendData = {
                iBusinessId: iBusinessId
            };

            $http2.upload('directory/upload_cover',fileURI, sendData)
                .then(function(data){
                    console.log(data);
                }, function(error){
                    console.warn(arguments);

                    if (error.code == FileTransferError.ABORT_ERR) {
                        return $modal.toast(gettextCatalog.getString('Canceled'));
                    }
                })
                .finally(function(){
                    $scope.onUploadPhotos(index +1, photos);
                });
        };

        $scope.onRemovePhotoItem = function(id){

            if ($scope.isProcessing) {
                return false;
            }

            for(var i =0; i< $scope.photoItems.length; ++i){
                if($scope.photoItems[i].iPhotoId == id){
                    if(!$scope.photoItems[i].hasOwnProperty('isNew')){
                        $scope.removePhotos.push(id);
                    }
                    $scope.photoItems.splice(i,1);
                    return;
                }
            }
        };

        $scope.prepareUploadPhotos = function(){
            var photos =  [];
            for(var i =0; i< $scope.photoItems.length; ++i){
                if($scope.photoItems[i].hasOwnProperty('isNew')
                    && $scope.photoItems[i].isNew == true){
                    photos.push($scope.photoItems[i].sPhotoUrl);
                }
            }
            $site.debug > 2 && console.log(photos);
            return photos;
        };

        $scope.onAddPhoto = $scope._setting($scope, function() {

            if ($scope.isProcessing) {
                return false;
            }

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

            $scope.photoItems.push({
                iPhotoId: (new Date()).getTime(),
                sPhotoUrl: fileURI,
                isNew: true
            });

            $scope.$$phrase || $scope.$apply();
        };

        $scope.initForm = function() {

            var sendData = {
                iBusinessId: $state.params.iBusinessId
            };

            $http2.get('directory/detail', sendData)
                .success(function(data){
                    if (data.error_code) {
                        $modal.alert(data.error_message || 'Can not load data from server');
                        return $scope.goBack();
                    }

                    $scope.form = data;
                    $scope.photoItems = data.aCoverPhotosEdit;
                    $scope.iMaxCover = data.iMaxCover;
                    $scope.dataReady = true;
                })
                .error(function(){
                    console.error('getForm', arguments);
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                    $scope.goBack();
                });
        };

        $scope.initForm();
    };
});
