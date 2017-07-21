define([
    'global/validator',
    'videochannel/model/videochannel-channel',
    'global/base/ListController',
], function(Validator, VideoChannelModel, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('VideoChannelAddMoreVideoCtrl');
        /**
         * check require permission
         */
        //$site.requirePerm('videochannel.create');

        /**
         * extend base controllers
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        $scope.data = {};
        $scope.dataReady = false;
        $scope.form = {};
        $scope.isProcessing = false;
        $scope.aVideos = {};
        var iChannelId = $state.params.iChannelId;
        $scope.formData = {
            iChannelId: iChannelId,
        };

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more videos'),
            itemModel: VideoChannelModel,
            apiService: function(){
                return 'videochannel/getVideoList';
            },
            listById: false,
            getQueryData: function(){
                return $scope.formData;
            },
        });

        $scope.loadInit = function() {
            var sendData = {
                bPrivacyNoCustom: true
            };

            var onSuccess = function(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }
                $scope.data.aVideos = data.aVideos;
                if(!$scope.data.aVideos.length)
                    $scope.noItems = true;
                $scope.dataReady = true;
            };

            var onError = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                // return scope.goBack();
            };
            $http2.post('videochannel/getVideoList', $scope.formData).success(onSuccess).error(onError);
        };

        // implement do save
        $scope.doSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }
            $scope.isProcessing = true;
            var onSuccess = function(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return scope.goBack();
                }
                iChannelId = data.iChannelId;
                if(iChannelId > 0){
                    $scope.goToPage('/app/videochannel-channel/'+iChannelId);
                }
            };

            var onError = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                return scope.goBack();
            };
            var videoFilter = function(){
                aKeys = Object.keys($scope.aVideos);
                values = [];
                for(i = 0; i < aKeys.length; i++){
                    if($scope.aVideos[aKeys[i]] == true)
                        values.push(aKeys[i]);
                }
                return values;
            };
            $http2.post('videochannel/addMoreVideo', {
                aVideos: videoFilter(),
                iChannelId: $scope.formData.iChannelId
            }).success(onSuccess).error(onError).finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.isValidData = function(bAlert) {

            return true;
        };

        $scope.loadInit();
    };
});