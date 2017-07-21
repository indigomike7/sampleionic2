define([
    'music/controller/music-playlist-list-ipad',
    'music/model/playlist',
    'global/base/ListController'
], function(MusicPlaylistListIpadCtrl, PlayListModel, ListCtrl) {
    return function($scope, $injector, gettext, gettextCatalog) {
        
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more albums'),
            itemModel: PlayListModel,
            apiService: 'album/fetch',
            listById: false,
            getQueryData: function(){
                return $scope.$parent.searchMusic;
            }
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(MusicPlaylistListIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});