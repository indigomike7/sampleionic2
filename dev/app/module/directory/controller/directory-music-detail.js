define([
    'CONTROLLER_PLAYER',
    'music/controller/music-playlist-item',
    'music/model/playlist'
], function(CONTROLLER_PLAYER, ItemController, ItemModel) {
    return function($timeout, $scope, $injector, $state, $http2, $modal, $site, gettextCatalog) {
        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.dataReady = false;

        $scope.item = $.extend({}, ItemModel, {
            iAlbumId: $state.params.iAlbumId
        });

        $scope.songs = [];

        $scope.loadInit = function() {
            var sendData = {
                iAlbumId: $scope.item.getId()
            };

            var onSuccess = function(data) {
                if (data.error_code) {
                    $site.debug > 2 && console.warn(arguments);
                    $modal.alert(data.message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }

                $.extend($scope.item, data);

                if ($scope.item.bCanView) {
                    $scope.songs = data.aSongs;
                    $scope.songLength = data.aSongs.length;

                    // fix issue song length.
                    if ($scope.songLength) {
                        $timeout(function() {
                            var firstSong = $scope.songs[0];
                            $('#id_player').html('<audio src="' + firstSong.sSongPath + '" width="100%" autoplay="true"></audio>');
                            $('#listsong li').first().addClass('current'); // require to initialize
                            var player = CONTROLLER_PLAYER.initialize();
                        }, 200);
                    }
                }

                $scope.dataReady = true;
            };

            var onError = function() {
                $site.debug > 2 && console.warn(arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.goBack();
            };

            $http2.get('album/detail', sendData).success(onSuccess).error(onError);
        };

        /**
         * make apply more frenquency
         */
        var iIntervalId = setInterval(function() {
            $scope.$$phase || $scope.$apply();
        }, 250);

        $scope.$on('$destroy', function() {
            var music = $('audio').attr('src', '');

            if (music.length) {
                music = music.get(0);
                music.load();
                music.play();
                music.pause();
            }

            window.clearInterval(iIntervalId);
        });

        $scope.loadInit();
    };
});
