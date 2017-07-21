define([
  'global/base/BaseController',
  'photo/model/photo',
],function(BaseController, ListController){
    
    return function($http2, $q, $state, $scope, $site, gettext, gettextCatalog, $injector, $modal){
        
        $injector.invoke(BaseController, this, {$scope: $scope});
        
        var iAlbumId  = $state.params.iAlbumId;
        
        $scope.removePhotos = new Array();
        $scope.photoItems = new Array();
        $scope.dataReady = false;
        $scope.isProcessing  = false;
        $scope.form = {};
        $scope.formData =  {};

        var sModule = '';
        var iItemId = '';
        
        $scope.onSave = function(){
            
            if($scope.isProcessing){
                return ;
            }
            
            if($scope.photoItems.length ==0){
                $modal.alert(gettextCatalog.getString('Select photo to upload!'));
                return ;
            }
            
            $scope.isProcessing  = true;
            
            $q.all([
                $http2.post('photo/albumedit', $scope.data),
                $http2.post('photo/delete_photos',{iPhotoIds: $scope.removePhotos}),
            ])
            .then(function(results){
                var data = results[0].data;
                if(data.error_code){
                    $scope.isProcessing = false;
                    $modal.alert(data.error_message);
                    return ;
                }else{
                    var photos  =  $scope.prepareUploadPhotos();
                    
                    if(photos.length){
                        $scope.onUploadPhotos(0, photos);    
                    }else{
                        $scope.goBack();
                    }
                    
                }
            },function(){
              $scope.isProcessing = false;  
            });
        };
        
        $scope.onUploadPhotos = function(index, photos){
            if(index >= photos.length){
                $scope.goBack();
                return ;
            }
            var fileURI  =  photos[index];
            
            var uploadParams = {
                iAlbumId: $scope.data.iAlbumId,
                sModule: sModule,
                iItemId: iItemId,
            }
            
            $http2.upload('photo/upload',fileURI, uploadParams)
                .then(function(data){
                    console.log(data);
                }, function(error){
                    console.log(arguments);

                    if (error.code == FileTransferError.ABORT_ERR) {
                        return $modal.toast(gettextCatalog.getString('Canceled'));
                    }
                })
                .finally(function(){
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
            console.log(photos);
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
        
        
         
        $q.all([
            $http2.post('photo/formadd'),
            $http2.post('photo/listalbumphoto',{
                iAlbumId: iAlbumId,
                iAmountOfPhoto: 500,
            }),
            $http2.post('photo/albumview',{
                iAlbumId: iAlbumId,
                fields: 'id,title,desc,auth,category,stats',
            })
        ])
        .then(function(results){
            
            var data0 =  results[0].data;
            $scope.photoItems = results[1].data;
            
            var data =  results[2].data;

            $scope.form.categoryOptions  = data0.category_options;
            $scope.form.viewOptions = data0.view_options;
            $scope.form.commentOptions =  data0.comment_options;
            sModule =  data.sModule;
            iItemId = data.iItemId;
            
            $scope.dataReady = true;    
            
            $scope.data = {
                iAlbumId: data.iAlbumId,
                sTitle: data.sTitle,
                sDescription: data.sDescription,
                sAuthComment: data.sCommentPrivacy,
                sAuthView: data.sViewPrivacy,
                iCategoryId: parseInt(data.iCategoryId)
            };
            $scope.dataReady = true;
        }, function(){
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        });
    };
});
