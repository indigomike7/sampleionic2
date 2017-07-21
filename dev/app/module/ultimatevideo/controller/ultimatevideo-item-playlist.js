define([
    'global/base/ItemController'
],function(Ctrl){
    return function($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal){
        $site.debug > 2 && console.log('UltimateVideoItemPlaylistController');
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];
            if($scope.item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            if($scope.item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit'),
                    action: $scope.onItemEdit,
                });
            }

            if(typeof $scope.extraPlaylistOptions != 'undefined') {
                if($scope.extraPlaylistOptions.indexOf('delete_history') >= 0) {
                    btns.push({
                        text: gettextCatalog.getString('Remove'),
                        action: $scope.onDeleteHistory
                    });
                }

            }
            return btns;
        });

        $scope.onItemDelete  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this playlist?'),
            'ultimatevideo/deletePlaylist',
            function(){return {iPlaylistId: $scope.item.getId()};},
            function(data){
                $modal.toast(data.message);
                $scope.items.deleteItem($scope.item.getId());
            }
        );

        $scope.onItemEdit  = function(){
            $location.path('app/ultimatevideo/edit/playlist/'  + $scope.item.iPlaylistId);
        };

        $scope.onDeleteHistory = function() {
            $http2.post('ultimatevideo/deleteHistory', {iItemId: $scope.item.getId(), iType: 1})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.items.deleteItem($scope.item.getId());
                        $modal.toast('This playlist has been removed from your History list.');
                    }
                }).error(function () {
                console.error('removeHistory', arguments);
            }).finally(function () {

            });
        };

    };

});
