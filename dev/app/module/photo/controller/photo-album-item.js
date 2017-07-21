define([
	'global/base/ItemController'
],function(ItemController){
    return function($scope, $rootScope, $site, $http2, $injector, gettext, gettextCatalog, $location, $modal){
        
        $injector.invoke(ItemController, this, {$scope: $scope});
        
        $scope.onItemSetting =  $scope._setting($scope, function(){
            
            var btns  = new Array();
            var item = $scope.item;
            
            if(item.canEdit())
                btns.push({
                    text: gettextCatalog.getString('Edit Album'),
                    action: $scope.onItemEdit,
                });   
                
             if(item.canDelete())
                btns.push({
                    text: gettextCatalog.getString('Delete Album'),
                    action: $scope.onItemDelete,
                });
                
              if(!item.isOwner())
                btns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport,
                });
                
                return btns;
        });
        
        $scope.onItemEdit = function(){
            
            if($scope.item.isProcessing)
                return ;
            
            $location.path('app/albums/edit/' + $scope.item.iAlbumId);  
        };
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this album?'),
            'photo/albumdelete',
            function(){
                return {iAlbumId: $scope.item.getId()};
            },
            function(data){
                $scope.items.deleteItem($scope.item.getId());
            }
        ); 
        
        return $scope;
    };
});
