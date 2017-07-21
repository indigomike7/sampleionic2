define([
    'ultimatevideo/model/playlist',
    'ultimatevideo/controller/ultimatevideo-item-playlist',
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

        var iPlaylistId = $state.params.iPlaylistId;
        $scope.videoItemOptions = ['watch_later', 'add_to_playlist'];
        $scope.dataReady = false;

        var sendData = {
            iPlaylistId : iPlaylistId,
            sParentType: '',
            iParentId: ''
            //fields: 'id,title,desc,stats,user,imgNormal,duration,canDelete,canView,canEdit,info,likes'
        };
        $scope.searchVideos = {
            sView: 'all',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 5,
            sOrder: 'creation_date',
            iTotalVideo: 0,
            iPlaylistId: iPlaylistId,
        };

        $scope.isLandscape = Math.abs(window.orientation) == 90;

        $scope.item = $.extend({
            iPlaylistId: iPlaylistId
        }, Model);

        $scope.onItemDelete  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this playlist?'),
            'ultimatevideo/deletePlaylist',
            function(){return {iPlaylistId: $scope.item.getId()};},
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

        $scope.loadInit = function(){
            $http2.get('ultimatevideo/detailPlaylist', sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else{
                        $.extend($scope.item, data);
                        $scope.searchVideos.iTotalVideo = data.iTotalVideo;
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

            if($scope.item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            if($scope.item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit'),
                    action: $scope.onItemEdit,
                });
            }
            return btns;
        });

        window.addEventListener('orientationchange', $scope.onOrientationChange);

        $scope.loadInit();
    };
});
/**
 * Created by master on 1/9/17.
 */
