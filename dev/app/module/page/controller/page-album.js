define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $injector, $state,  $modal, gettext, gettextCatalog, $location, $ionicHistory) {

        var iPageId = $state.params.iPageId;

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
            iPage: 1,
            sOrder: "recent",
            iAmountOfAlbum: 24 // 4 rows, 3 photos per rows
        };

        $scope.onPagePhotoMenu = $scope._setting($scope, function() {

            var settingBtns = [];

            settingBtns.push({
                text: gettextCatalog.getString('Add Album'),
                action: function(){
                    $location.path('app/albums/add/pages/' + iPageId);
                }
            });
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