define([
  'global/base/BaseController',
  'photo/model/photo',
],function(BaseController, ListController){
    
    return function($http2, $q, $state, $scope, $site, gettext, gettextCatalog, $injector, $modal,$site){
        
        $injector.invoke(BaseController, this, {$scope: $scope});


        $site.requirePerm('photo.can_upload_photos');
        $site.requirePerm('photo.can_create_photo_album');

        
        var iAlbumId  = $state.params.iAlbumId;
        
        $scope.removePhotos = new Array();
        $scope.photoItems = new Array();
        $scope.dataReady = false;
        $scope.isProcessing  = false;
        $scope.iNewPhotoIds = [];

        var sModule =  $state.params.sParentType || '';
        var iItemId = $state.params.iParentId || '';
        
        $scope.formData = {
            sTitle: '',
            sDescription: '',
            iCategoryId: 0,
            sAuthView: '0',
            sAuthComment: '0',
            sModule: sModule,
            iItemId: iItemId,
            iSearch: 1, // by default enable this value to show album on search result
        };
        
        $scope.form = {};
        
        $scope.onSave = function(){
            
            if($scope.isProcessing){
                return ;
            }
            
            if($scope.photoItems.length ==0){
                $modal.alert(gettextCatalog.getString('Select photo to upload!'));
                return ;
            }
            
            $scope.isProcessing  = true;
            $http2.post('photo/albumcreate', $scope.formData)
            .success(function(data){
                if(data.error_code){
                    $scope.isProcessing = false;
                    $modal.alert(data.error_message);
                    return ;
                }else{
                    $scope.formData.iAlbumId = data.iAlbumId;
                    var photos  =  $scope.prepareUploadPhotos();
                    if(photos.length){
                        $scope.onUploadPhotos(0, photos);    
                    }else{
                        console.log('albumcreate finally');
                        $scope.goBack();
                    }
                }
            })
            .error(function(){
                $modal.alert(gettextCatalog.getString('Count not get data from server'));
            })
            .finally(function(){
                $scope.isProcessing = false;
            });
        };
        
        $scope.doCreateFeed = function(){
            var sendData = {
                iAlbumId: $scope.formData.iAlbumId,
                sPhotoIds: $scope.iNewPhotoIds.join(','),
            };
            $http2.post('photo/postfeed',sendData).success(function(data){
                console.log(JSON.stringify(data));
            }).error(function(){
                console.warn('Can not get data from server');
            })
            .finally(function(){
                console.log('postfeed finally');
                // issue go back but does not see photo detail without reload page.
                if($scope.formData.sModule) {
                    $scope.goBack()
                } else {
                    $state.go('app.PhotoMyAlbum', {});
                }
            });
        };
        
        $scope.onUploadPhotos = function(index, photos){
            if(index >= photos.length){
                $scope.doCreateFeed();
                return ;
            }
            var fileURI  =  photos[index];
            var sendData = {
                iAlbumId: $scope.formData.iAlbumId,
                sModule: sModule,
                iItemId: iItemId,
            };

            $http2.upload('photo/upload',fileURI, sendData)
                .then(function(data){
                    if(data.error_code){
                        // skip error photo
                    }else{
                        $scope.iNewPhotoIds.push(data.iPhotoId);
                    }
                }, function(error){
                    if (error.code == FileTransferError.ABORT_ERR) {
                        return $modal.toast(gettextCatalog.getString('Canceled'));
                    }

                    $modal.alert(gettextCatalog.getString('can not upload photos'));
                })
                .finally(function(){
                    console.log('upload finally '+ (index + 1));
                    $scope.onUploadPhotos(index +1, photos);
                });  
        };
        
        $scope.onRemovePhotoItem = function(id){
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
            return photos;
        };
        
        $scope.validateOnAddPhotoItem = function(fileURI){
            for(var i = 0; i<$scope.photoItems.length; ++i){
                if($scope.photoItems[i].sPhotoURl ==  fileURI)
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

            $scope.photoItems.push({
               iPhotoId: (new Date()).getTime(),
               sPhotoUrl: fileURI,
               isNew: true,
           });
            
            $scope.$$phrase || $scope.$apply();
        };

        $scope.onRemovePhoto = function(index) {
            $scope.photos.splice(index, 1);
            $scope.$$phrase || $scope.$apply();
        };
        
        $scope.loadInit = function(){
            $http2.post('photo/formadd')
            .success(function(data){
                if(data.error_code){
                    $modal.alert(data.error_message);
                    $scope.goBack();
                }else{
                    if (!$scope.formData.sModule) {
                        $scope.form.viewOptions = data.view_options;
                        $scope.form.commentOptions = data.comment_options;
                    }
                    $scope.form.categoryOptions  = data.category_options;
                    $scope.formData.sAuthView =  data.default_privacy_setting;
                    $scope.dataReady = true;
                }
                
            })
            .error(function(){
                $modal.alert(gettextCatalog.getString('Can not get data from server'));
            });
        };
        
        $scope.loadInit();
    };
});
