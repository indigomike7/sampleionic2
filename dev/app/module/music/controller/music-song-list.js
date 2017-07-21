define([
    'music/controller/music-song-list-ipad',
    'music/model/song',
    'global/base/ListController'
], function(MusicSongListIpadCtrl, SongModel, ListCtrl) {
    return function($scope, $injector, gettext, gettextCatalog) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more albums'),
            itemModel: SongModel,
            apiService: function(){
                if (typeof $scope.$parent.searchMusicApi != 'undefined') {
                    return $scope.$parent.searchMusicApi;
                }
                
                return 'song/fetch';
            },
            listById: false,
            getQueryData: function() {
                return $scope.$parent.searchMusic;
            },
            getItemExtraData: function() {
                return $scope.$parent.musicExtraData || {};
            },
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(MusicSongListIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});