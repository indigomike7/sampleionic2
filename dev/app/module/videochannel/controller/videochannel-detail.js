define([
    'videochannel/model/videochannel',
    'videochannel/controller/videochannel-item',
    'global/helper'
],function(Model, Ctrl, Helper){

    return function($sce, $injector, $scope, $state, $http2, $modal, gettext, gettextCatalog, $ionicModal, $site, $location, $timeout, $coreSettings, $dislike) {

        /**
         * check require permission
         */
        //$site.requirePerm('videochannel.view');

        /**
         * extend base controller
         */
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.Helper = Helper;

        /**
         * data is ready
         */

        var iVideoId = $state.params.iVideoId;

        $scope.dataReady = false;

        var sendData = {
            iVideoId : iVideoId,
            sParentType: '',
            iParentId: ''
            //fields: 'id,title,desc,stats,user,imgNormal,duration,canDelete,canView,canEdit,info,likes'
        };

        $scope.isLandscape = Math.abs(window.orientation) == 90;

        $scope.item = $.extend({
            iVideoId: iVideoId
        }, Model);

        $scope.onItemDelete  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this video?'),
            'videochannel/delete',
            function(){return {iVideoId: $scope.item.getId()};},
            function(data){
                $scope.goBack();
            }
        );

        $scope.onOrientationChange = function(){
            $timeout(function(){
                $scope.isLandscape = Math.abs(window.orientation) == 90;

                if (!$scope.isLandscape) {
                    var videoContainer = angular.element('.mejs-container');
                    videoContainer.css({
                        width: '100%'
                    });
                }
            }, 150);
        };

        $scope.onBeginFullScreen = function() {
            if (typeof(window.nativeControl) != 'undefined') {
                window.nativeControl.lockMenu();
            }
        };

        $scope.onEndFullScreen = function() {
            if (typeof(window.nativeControl) != 'undefined') {
                window.nativeControl.unlockMenu();
            }
        };

        $scope.onFullScreenChange = function(e) {
            if (document.webkitIsFullScreen) {
                $scope.onBeginFullScreen();
            } else {
                $scope.onEndFullScreen();
            }
        };

        $scope.loadInit = function(){
            $http2.get('videochannel/detail', sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else{
                        $.extend($scope.item, data);

                        var mp4Regex = new RegExp('\.mp4$', 'i');
                        if ($scope.item.sVideoUrl || mp4Regex.test($scope.item.sCode)) {
                            $scope.item.sVideoUrl = $scope.item.sVideoUrl ? $sce.trustAsResourceUrl($scope.item.sVideoUrl) : $sce.trustAsResourceUrl($scope.item.sCode);

                            if (ionic.Platform.isAndroid()) {
                                $timeout(function() {
                                    // for Android < 4.4
                                    var videoElement = document.getElementById('video_element');
                                    videoElement.addEventListener('webkitbeginfullscreen', $scope.onBeginFullScreen, false);
                                    videoElement.addEventListener('webkitendfullscreen', $scope.onEndFullScreen, false);

                                    // for Android 4.4+
                                    document.addEventListener('webkitfullscreenchange', $scope.onFullScreenChange, false);
                                }, 1000);

                                $scope.$on('$destroy', function() {
                                    document.removeEventListener('webkitfullscreenchange', $scope.onFullScreenChange);
                                });
                            } else {
                                $timeout(function() {
                                    $('#video_player_control').mediaelementplayer({
                                        poster: $scope.item.getImageSrc(),
                                        features: ['playpause','progress','current','duration','fullscreen']
                                    });
                                }, 200);
                            }
                            // fix issue can not play uploaded video
                            // Blocked loading resource from url not allowed by $sceDelegate policy
                        }

                        if($scope.item.sEmbed){
                            var video_width = window.screen.width;
                            $scope.item.sEmbed = $scope.item.sEmbed.replace(/width="640"/, 'width="'+video_width+'"')
                                .replace(/height="360"/, 'height="'+(video_width*360/640)+'"');

                            // $scope.item.sEmbed = $sce.trustAsHtml($scope.item.sEmbed);

                            $timeout(function() {
                                var $iframe_holder = $('#video_embed_iframe');
                                $iframe_holder.html($scope.item.sEmbed);
                                $iframe_holder.find('iframe').bind('load', function() {
                                    $site.debug > 2 && console.log('iframe.load');

                                    $iframe = $(this);
                                    $iframe.contents().find('.yt-uix-button-menu').remove(); // remove YouTube Context Menu
                                    $iframe.contents().find('.ytp-scalable-icon-shrink').remove(); // remove YouTube watermark
                                
                                    var preventEvents = 'mousedown touchstart mousemove touchmove mouseup touchend click';

                                    $iframe.contents().on(preventEvents, 'a', function(e) {
                                        e.preventDefault();
                                        window.open($(e.currentTarget).attr('href'), '_system');
                                    });

                                    $iframe.contents().on(preventEvents, '.logo', function(e) { // dailymotion logo
                                        e.preventDefault();
                                    });
                                });
                            }, 300);
                        }

                        $scope.dataReady = true;
                    }
                }).error(function() {
                })
                .error(function(){
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                    $scope.goBack();
                });
        };

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];

            if ($coreSettings.get('like_allow_dislike')) {

                if ($scope.item.getTotalDislike() > 0) {
                    btns.push({
                        text: $dislike.getDislikeStat($scope.item),
                        action: $scope.onViewDisliked
                    });
                }

                if ($scope.item.canDislike()) {
                    btns.push({
                        text: $scope.item.isDisliked() ? gettextCatalog.getString('Remove Dislike') : gettextCatalog.getString('Dislike'),
                        action: $scope.onItemDislike
                    });
                }
            }

            btns.push({
                text: gettextCatalog.getString('Share'),
                action: $scope.onItemShare
            });

            if($scope.item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete this video'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            if($scope.item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit this video'),
                    action: $scope.onItemEdit,
                });
            }

            if (!$scope.item.isOwner()) {
                btns.push({
                    text: gettextCatalog.getString('Report this video'),
                    action: $scope.onItemReport
                });
            }

            // Favorite/unfavorite option
            if($scope.item.bIsFavourite) {
                favouriteBtnText = 'Unfavorite';
            }else{
                favouriteBtnText = 'Favorite';
            }
            btns.push({
                text: gettextCatalog.getString(favouriteBtnText),
                action: function(){
                    var item = $scope.item;
                    function doFavouriteSuccess(data) {
                        if (data.error_code) {
                            $modal.alert(data.error_message || 'Can not load data from server');
                        }else{
                            if (data.message) {
                                $modal.toast(data.message);
                            }
                            item.bIsFavourite = !item.bIsFavourite;
                        }
                    }

                    function doFavouriteError() {
                        console.warn('unfavorite', arguments);
                        $modal.alert(gettextCatalog.getString('Can not load data from server'));
                    }

                    function doFavourite(){
                        if (item.isProcessingFavourite) {
                            return;
                        }

                        item.isProcessingFavourite = true;

                        var api = 'videochannel/favourite';

                        var favouriteData = {
                            iItemId: item.getId(),
                            sItemType: item.getType(),
                            sParentId: item.getParentModuleId(),
                            bIsFavourite: !item.bIsFavourite,
                        };

                        $http2.post(api, favouriteData)
                            .success(doFavouriteSuccess)
                            .error(doFavouriteError)
                            .
                            finally(function() {
                                item.isProcessingFavourite = false;
                            });
                    }
                    return doFavourite();
                }
            });


            return btns;
        });

        window.addEventListener('orientationchange', $scope.onOrientationChange);

        $scope.loadInit();
    };
});
