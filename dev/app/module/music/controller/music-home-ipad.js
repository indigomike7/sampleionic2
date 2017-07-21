define([
    'global/base/BrowseController',
    'music/model/playlist',
    'music/model/song',
    'text!tpl/music/music-playlist-search.html',
    'text!tpl/music/music-song-search.html'
], function(BrowseController, PlaylistModel, SongModel, playlistSearchTemplate, songSearchTemplate) {

    return function($scope, $injector, $state, gettext, gettextCatalog, $location) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        /**
         * Handle tabs
         */
        if ($state.current.itemType == 'song' && $state.current.itemView == 'all') {
            $scope.activeTab = 0;
        } else if ($state.current.itemType == 'song' && $state.current.itemView == 'my') {
            $scope.activeTab = 1;
        } else {
            $scope.activeTab = 2;
        }

        $scope.onMoreTabs = $scope._setting($scope, function() {
            var btns = [];

            btns.push({
                text: gettextCatalog.getString('All Albums'),
                action: function() {
                    $location.path('app/music_playlists');
                }
            });

            btns.push({
                text: gettextCatalog.getString('My Albums'),
                action: function() {
                    $location.path('app/music_my_playlists');
                }
            });

            return btns;
        });

        /**
         * Handle search
         */
        $scope.searchTemplate = $state.current.itemType == 'song' ? songSearchTemplate : playlistSearchTemplate;

        $scope.searchMusic = {
            sSearch: '',
            sView: $state.current.itemView,
            iLimit: 20,
            iPage: 1,
            sOrder: 'latest'
        };

        $scope.onSubmitSearch = function() {

            $scope.$broadcast('music:queryChange', $scope.searchMusic);
        };

        /**
         * Handle active object
         */
        $scope.setActiveObj = function(obj) {

            $scope.activeObj = obj;

            $scope.$broadcast('music:objChange', $scope.activeObj);
        };

        $scope.onItemClick = function(item) {

            if (!$scope.activeObj || $scope.activeObj.getId() != item.getId()) {
                $scope.setActiveObj(item);
            }
        };

        /**
         * For detail link
         */
        if ($state.params.iItemId) {
            var item = {};

            if ($state.current.itemType == 'song') {
                $.extend(item, SongModel, {
                    iSongId: $state.params.iItemId,
                    sModelType: $state.current.sModelType || 'music_song'
                });
            } else {
                $.extend(item, PlaylistModel, {
                    iAlbumId: $state.params.iItemId,
                    sModelType: $state.current.sModelType || 'music_album'
                });
            }

            $scope.initObj = item;
        }
    };
});