define([
  'photo/model/album',
  'photo/controller/photo-album-item'
],function(Model, Ctrl){
    
    return function($http2, $state, $scope, $site, gettext, gettextCatalog, $injector, $modal, $location){

        var iAlbumId  = $state.params.iAlbumId;
        
        $scope.photoItemWith = 160;
        $scope.photoItemHeight  = 160;
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.iAlbumId = iAlbumId;
        
        $scope.item  =  $.extend({
            iAlbumId: iAlbumId,
        }, Model);
        
        $scope.searchPhotosApi = 'photo/fetch_photo';
        
        $scope.searchPhotos  = {
            sParentType: 'album',
            iParentId: $scope.item.getId(),
            iPage: 1,
            iLimit: 1000,
            fields: 'id,title,type,imgFull,imgNormal,albumId',
        };
        
        $scope.getItemExtraData =  function(){
            return {
              iParentId: $scope.item.getId(),
              sParentType: $scope.item.getType(),  
            };
        };

        $scope.getPhotoItemExtraData =  function(){
          return {
              iParentId: $scope.item.getId(),
              sParentType: $scope.item.getType(),  
            };  
        }
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this album?'),
            'photo/albumdelete',
            function(){
                return {iAlbumId: $scope.item.getId()};
            },
            function(data){
                $scope.goBack();
            }
        ); 
        $scope.fetchData = function(iAlbumId){
            var sendData = {
                iAlbumId: iAlbumId,
                fields: 'id,title,desc,stats,canView,user,likes',
            };
            $http2.get('photo/albumview', sendData)
            .success(function(data){
                if(data.error_code){
                    $modal.alert(data.error_message);
                }else{
                    $.extend($scope.item, data);
                    $scope.dataReady = true;
                }
            })
            .error(function(){
                $modal.alert(gettextCatalog.getString('Can not get data from server'));
            })
            .finally(function(){
                // finally source code
            });
        };
        $scope.fetchData(iAlbumId);
    };
});
