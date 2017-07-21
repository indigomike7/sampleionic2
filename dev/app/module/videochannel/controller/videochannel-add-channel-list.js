define([
    'global/validator',
    'videochannel/model/videochannel-channel',
    'global/base/ListController',
], function(Validator, VideoChannelModel, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state, addChannelData) {
        $site.debug > 2 && console.log('VideoChannelAddChannelListCtrl');
        /**
         * check require permission
         */
        //$site.requirePerm('videochannel.create');

        /**
         * extend list controllers
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        $scope.dataReady = false;
        $scope.form = {};
        $scope.isProcessing = false;
        $scope.formData = {
            sUrl: '',
            parent_id: $viewer.get('iUserId'),
            parent_type: 'user'
        };
        $scope.searchChannels = {
            keywords:(addChannelData.data.keywords != null) ? addChannelData.data.keywords : '',
            iPage: 1,
        };
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more channels'),
            itemModel: VideoChannelModel,
            apiService: function(){
                return 'videochannel/findChannel';
            },
            listById: false,
            getQueryData: function(){
                return $scope.searchChannels;
            },
        });

        $scope.loadInit = function() {
            $scope.dataReady = true;
        };

        // implement do save
        $scope.doSave = function() {
            if($scope.isProcessing || !$scope.isValidData(true)){
                return;
            }
            aSelectedItem = $scope.items[$scope.formData.iChannelItemId];

            addChannelData.data.aChannel = {
                sSiteId: 'youtube',
                sTitle: aSelectedItem.title,
                sChannelFeedUrl: aSelectedItem.url,
                sDescription: aSelectedItem.summary,
                sImg: aSelectedItem.video_image,
                iChannelId: 0
            };
            $scope.goToPage('/app/videochannel/add/channel/detail');
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.formData.iChannelItemId) {
                bAlert && $modal.alert(gettextCatalog.getString('Please select a channel'));
                return false;
            }
            return true;
        };

        $scope.loadInit();
    };
});