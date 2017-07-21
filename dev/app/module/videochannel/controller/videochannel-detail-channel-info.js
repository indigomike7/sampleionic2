define([
    'videochannel/model/videochannel-channel',
    'videochannel/controller/videochannel-item-channel',
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

        var iChannelId = $state.params.iChannelId;

        $scope.dataReady = false;

        var sendData = {
            iChannelId : iChannelId,
            sParentType: '',
            iParentId: ''
        };

        $scope.item = $.extend({
            iChannelId: iChannelId
        }, Model);

        $scope.loadInit = function(){
            $http2.get('videochannel/detailChannel', sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else {
                        $.extend($scope.item, data);
                        $scope.dataReady = true;
                    }
                }).error(function() {
            })
                .error(function(){
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                    $scope.goBack();
                });
        };

        $scope.loadInit();

        $scope.$on('videochannel:deleted', function(event, args) {
            $scope.item.iNumberVideos--;
        });
    };
});
