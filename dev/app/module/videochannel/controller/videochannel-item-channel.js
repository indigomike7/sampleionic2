define([
    'global/base/ItemController'
],function(Ctrl){
    return function($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal){

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];
            if($scope.item.canAutoUpdate()){
                btns.push({
                    text: gettextCatalog.getString('Auto update'),
                    action: function(){
                        item = $scope.item;
                        function doAutoUpdateSuccess(data){
                            if (data.error_code) {
                                $modal.alert(data.error_message || 'Can not load data from server');
                            }else{
                                if (data.message) {
                                    $modal.toast(data.message);
                                }
                                item.iNumberVideos = data.iNumberVideos;
                            }

                        }
                        function doAutoUpdateError(){
                            console.warn('AutoUpdate', arguments);
                            $modal.alert(gettextCatalog.getString('Can not load data from server'));

                        }
                        function doAutoUpdate(){
                            if (item.isProcessingAutoUpdate) {
                                return;
                            }

                            item.isProcessingAutoUpdate = true;

                            var api = 'videochannel/autoUpdate';

                            var autoUpdateData = {
                                iItemId: item.getId(),
                                sItemType: item.getType(),
                                sParentId: item.getParentModuleId(),
                            };

                            $http2.post(api, autoUpdateData)
                                .success(doAutoUpdateSuccess)
                                .error(doAutoUpdateError)
                                .
                                finally(function() {
                                    item.isProcessingAutoUpdate = false;
                                });

                        }
                        return doAutoUpdate();
                    }
                });
            }

            if($scope.item.canAddMoreVideo()){
                btns.push({
                    text: gettextCatalog.getString('Add more videos'),
                    action: $scope.onItemAddMoreVideo,
                });
            }

            if($scope.item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete this channel'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            if($scope.item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit this channel'),
                    action: $scope.onItemEdit,
                });
            }
            //
            // if (!$scope.item.isOwner()) {
            //     btns.push({
            //         text: gettextCatalog.getString('Report this video'),
            //         action: $scope.onItemReport
            //     });
            // }

            return btns;
        });

        $scope.onItemDelete  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this channel?'),
            'videochannel/deleteChannel',
            function(){return {iChannelId: $scope.item.getId()};},
            function(data){
                $modal.toast(data.message);
                $scope.items.deleteItem($scope.item.getId());
                $scope.$emit('videochannel:deleted', $scope.item.getId());
            }
        );

        $scope.onItemEdit  = function(){
            $location.path('app/videochannel-channel/edit/'  + $scope.item.iChannelId);
        };

        $scope.onItemAddMoreVideo = function(){
            $scope.goToPage('app/videochannel/add-video/' + $scope.item.iChannelId);
        }
    };

});
