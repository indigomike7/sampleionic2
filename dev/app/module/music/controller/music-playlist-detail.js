define([
    'music/controller/music-playlist-detail-ipad',
    'music/model/playlist',
    'music/controller/music-playlist-item',
    'CONTROLLER_PLAYER',
],function(MusicPlaylistDetailIpadCtrl, PlaylistModel, ItemController,CONTROLLER_PLAYER){
    return function($q,$timeout, $scope, $injector, $state, $rootScope, $http2, $modal, $site, $location, $state){

        $injector.invoke(ItemController, this, {$scope: $scope});
        $scope.dataReady = false;
        $scope.item = $.extend({}, PlaylistModel, {
            iAlbumId: $state.params.iAlbumId,
            sModelType: $state.current.sModelType || 'music_album'
        });
        $scope.songs = [];
        
        
        // $scope.audio.setAttribute('id', 'music_audio_player');       
        $scope.loadInit = function(){
            
            var sendData= {
                iAlbumId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            };

            var successCb = function(data){
                if(data.error_code){
                    $modal.alert(data.error_message);
                    $scope.goBack();
                }else{
                    $.extend($scope.item, data);
                    
                    if($scope.item.bCanView){
                        $scope.songs  = data.aSongs;

                        for (var i= 0, len = $scope.songs.length; i<len; i++) {
                            $scope.songs[i].sSongPath = $scope.songs[i].sSongPath.replace('index.php', 'PF.Base');
                        }
                        $scope.songLength  =  data.aSongs.length;

                        //fix issue song length.
                        if($scope.songLength){
                            $timeout(function(){
                                var firstSong  = $scope.songs[0];
                                $('#id_player').html('<audio src="'+firstSong.sSongPath+'" width="100%" autoplay="true"></audio>');
                                $('#listsong li').first().addClass('current'); // require to initialize

                                var player =  CONTROLLER_PLAYER.initialize();
                            }, 500);
                        }
                    }  
                    $scope.dataReady = true; 
                }
            };

            var errorCb = function(){
              $modal.alert(gettextCatalog.getString('can not get data from server'));
            };
            
            $http2.get('album/detail', sendData).success(successCb).error(errorCb).finally(function() {
                $scope.isLoading = false;
            });
        };
        
        /**
         * make apply more frenquency
         */
        var iIntervalId  = setInterval(function(){ $scope.$$phase || $scope.$apply(); }, 250);
        
        $scope.$on('$destroy',function(){
            var music = $('audio').attr('src', '');
            
            if(music.length){
                music = music.get(0);
                music.load();
                music.play();
                music.pause();
            }
            
            
           window.clearInterval(iIntervalId); 
        });

        if (ionic.Platform.isIPad() && !$state.params.sParentType) {
            $injector.invoke(MusicPlaylistDetailIpadCtrl, this, {
                $scope: $scope
            });
        } else {
            $scope.loadInit();
        }
        
        return $scope;
    };
});
