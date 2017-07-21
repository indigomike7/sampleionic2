define([
    'global/validator',
	'global/base/ItemController',
	'text!tpl/photo/photo-edit-modal.html',
],function(Validator, Ctrl, photoEditModalTpl){
    return function($scope, $http2, $site, $injector, gettext, gettextCatalog, $modal, $ionicModal, $rootScope){
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.onSaveInfo = function(){
            
            if(Validator.isEmpty( $scope.formData.sTitle)){
                return $modal.alert(gettextCatalog.getString('Please provide title for this photo'));
            }
            
            var sendData = $scope.formData;
            $scope.isProcessing =  true;
            
            $http2.post('photo/edit', sendData)
            .success(function(data){
                if(data.error_code){
                    
                }else{
                    $.extend($scope.item, $scope.formData);
                    $modal.toast(gettextCatalog.getString('Your changes has been saved'));
                    $scope.hideEditPhotoModal();
                }
            })
            .error(function(){
                $modal.alert(gettextCatalog.getString('can not get data from server'));
            })
            .finally(function(){
                $scope.isProcessing =  false;
            });
        };
        
        $scope.hideEditPhotoModal = function(){
            
            $scope.editPhotoModal.remove();
        };
        
        $scope.onItemEdit = function(){
            
            $scope.formData = {
                // to identify this item
                iPhotoId: $scope.item.getId(),
                iItemId: $scope.item.getId(),
                sItemType: $scope.item.getType(),
                
                // editable data
                sTitle: $scope.item.getTitle(),
                sDescription: $scope.item.sDescription,
            };
            
            $scope.editPhotoModal = $ionicModal.fromTemplate(photoEditModalTpl,{
                scope: $scope,
            });    
            
            $scope.editPhotoModal.show();
        };
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this photo?'),
            'photo/delete',
            function(){
                return {
                    iPhotoId: $scope.item.getId(),
                    iItemId: $scope.item.getId(),
                    sItemType: $scope.item.getType(),
                };
            },function(data){
                $scope.items.deleteItem($scope.item.getId());
                $scope.goBack();
            });
            
        $scope.onItemMakeProfilePicture = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to make this photo your profile picture?'),
            'photo/setprofile',
            function(){
                return {
                    iPhotoId: $scope.item.getId(),
                    iItemId: $scope.item.getId(),
                    sItemType: $scope.item.getType()
                };
            }, function(data){
                $rootScope.$broadcast('user:updateAvatar', {
                    user_image: data.sProfileImage
                });
            });
        
        $scope.onItemSetting = $scope._setting($scope, function(){
            var btns = [];
            var item = $scope.item;
            /**
             * this item does not affected by level id.
             */
            

            if(item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit Photo'),
                    action: function(){
                        $scope.onItemEdit();
                    }
                });
            }
                
            if(item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete Photo'),
                    action: function(){
                        $scope.onItemDelete();
                    },
                    destructive: true,
                });
            }
            

            if(item.bCanMakeProfilePicture){
                btns.push({
                    text: gettextCatalog.getString('Make Profile Photo'),
                    action: function(){
                        $scope.onItemMakeProfilePicture();
                    },
                });
            }
            
            if(!item.isOwner()){
                btns.push({
                    text: gettextCatalog.getString('Report this photo'),
                    action: function(){
                        $scope.onItemReport();
                    }
                });
            }
            return btns;
        });
    };
});
