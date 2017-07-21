define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $injector, $state,  $modal, gettext, gettextCatalog, $location, $site, $ionicHistory) {

        $site.requirePerm('photo.can_view_photos');

        var iPageId = $state.params.iPageId;

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.getPhotoItemExtraData  = function(){
            return {
                iItemId: $scope.item.getId(),
                sModule: $scope.item.getType(),
                sParentType: $scope.item.getType(),
                iParentId: $scope.item.getId(),
                iUserId: $scope.item.getId(),
            };
        };

        $scope.searchPhotos = {
            iItemId: $scope.item.getId(),
            sModule: $scope.item.getType(),
            sParentType: $scope.item.getType(),
            iParentId: $scope.item.getId(),
            iUserId: $scope.item.getId(),
            iPage: 1,
            sOrder: "latest",
            iAmountOfPhoto: 24 // 4 rows, 3 photos per rows
        };

        $scope.onPagePhotoMenu = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($scope.item.bCanSharePhotos) {
                settingBtns.push({
                    text: gettextCatalog.getString('Add Album'),
                    action: function(){
                        $location.path('app/albums/add/pages/' + iPageId);
                    }
                });
            }
            settingBtns.push({
                text: gettextCatalog.getString('View Albums'),
                action: function(){
                    $ionicHistory.nextViewOptions({
                        disableAnimate: true
                    });
                    $location.path('app/page_albums/'+ iPageId);
                }
            });
            settingBtns.push({
                text: gettextCatalog.getString('View Photos'),
                action: function(){
                    $ionicHistory.nextViewOptions({
                        disableAnimate: true
                    });
                    $location.path('app/page_photos/'+ iPageId);
                }
            });

            return settingBtns;
        });

        return $scope;
    };
});