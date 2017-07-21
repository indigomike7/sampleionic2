define([
    'group/model/group',
    'global/base/BaseController'
], function(GroupModel, Ctrl) {
    return function($scope, $injector, $state,  $modal, gettext, gettextCatalog, $location, $ionicHistory) {

        var iGroupId = $state.params.iGroupId;

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.getPhotoItemExtraData  = function(){
            return {
                iItemId: $scope.item.getId(),
                sModule: $scope.item.getType()
            };
        };
        
        $scope.searchAlbums = {
            iItemId: $scope.item.getId(),
            sModule: $scope.item.getType(),
            iGroup: 1,
            sOrder: "recent",
            iAmountOfAlbum: 24 // 4 rows, 3 photos per rows
        };

        $scope.onGroupPhotoMenu = $scope._setting($scope, function() {

            var settingBtns = [];

            settingBtns.push({
                text: gettextCatalog.getString('Add Album'),
                action: function(){
                    $location.path('app/albums/add/groups/' + iGroupId);
                }
            });
            settingBtns.push({
                text: gettextCatalog.getString('View Albums'),
                action: function(){
                    $ionicHistory.nextViewOptions({
                        disableAnimate: true
                    });
                    $location.path('app/group_albums/'+ iGroupId);
                }
            });
            settingBtns.push({
                text: gettextCatalog.getString('View Photos'),
                action: function(){
                    $ionicHistory.nextViewOptions({
                        disableAnimate: true
                    });
                    $location.path('app/group_photos/'+ iGroupId);
                }
            });

            return settingBtns;
        });

        return $scope;
    };
});