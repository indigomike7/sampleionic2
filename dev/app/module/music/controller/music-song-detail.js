define([
    'music/controller/music-song-detail-ipad',
    'music/model/song',
    'music/controller/music-song-item',
    'CONTROLLER_PLAYER',
], function(MusicSongDetailIpadCtrl, SongModel, ItemController, CONTROLLER_PLAYER) {
    return function($q, $timeout, $scope, $injector, $state, $rootScope, $http2, $modal, $site, $location, $state, gettext, gettextCatalog, $coreSettings, $dislike) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });
        $scope.dataReady = false;
        $scope.item = $.extend({}, SongModel, {
            iSongId: $state.params.iSongId,
            sModelType: $state.current.sModelType || 'music_song',
            sParentType: $state.params.sParentType || '',
            iParentId: $state.params.iParentId || ''
        });
        $scope.songs = [];

        // $scope.audio.setAttribute('id', 'music_audio_player');       
        $scope.loadInit = function() {

            var sendData = {
                iSongId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            };

            var successCb = function(data) {

                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }

                $.extend($scope.item, data);

                $scope.songs = [$scope.item];

                $timeout(function() {
                    $('#id_player').html('<audio src="' + $scope.item.getSongPath() + '" width="100%" autoplay="true"></audio>');
                    $('#listsong li').first().addClass('current'); // require to initialize

                    var player = CONTROLLER_PLAYER.initialize({
                        features: ['playpause', 'clear', 'current', 'progress', 'duration']
                    });
                }, 200);

                $scope.dataReady = true;
            };

            var errorCb = function() {

                console.warn('song/detail', JSON.stringify(arguments));
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.get('song/detail', sendData).success(successCb).error(errorCb).finally(function() {
                $scope.isLoading = false;
            });
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

        $scope.getSettingBtns = function() {

            var settingBtns = [];

            if ($coreSettings.get('like_allow_dislike')) {

                if ($scope.item.getTotalDislike() > 0) {
                    settingBtns.push({
                        text: $dislike.getDislikeStat($scope.item),
                        action: $scope.onViewDisliked
                    });
                }

                if ($scope.item.canDislike()) {
                    settingBtns.push({
                        text: $scope.item.isDisliked() ? gettextCatalog.getString('Remove Dislike') : gettextCatalog.getString('Dislike'),
                        action: $scope.onItemDislike
                    });
                }
            }

            if (!$scope.item.isOwner()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Report this song'),
                    action: $scope.onItemReport
                });
            }

            return settingBtns;
        };

        $scope.onItemSetting = $scope._setting($scope, $scope.getSettingBtns);

        if (ionic.Platform.isIPad() && !$state.params.sParentType) {
            $injector.invoke(MusicSongDetailIpadCtrl, this, {
                $scope: $scope
            });
        } else {
            $scope.loadInit();
        }

        $scope.pauseMusic = function() {

            var music = $('audio');
            if (music.length) {
                music = music.get(0);
                music.pause();
            }
        };

        document.addEventListener("pause", $scope.pauseMusic, false);

        return $scope;
    };
});