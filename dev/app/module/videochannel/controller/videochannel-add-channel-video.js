define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state, addChannelData) {
        $site.debug > 2 && console.log('VideoChannelAddChannelVideoCtrl');
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
        $scope.data = addChannelData.data;
        $scope.dataReady = false;
        $scope.form = {};
        $scope.isProcessing = false;
        $scope.aVideos = {};

        $scope.formData = {
            sUrl: '',
            sTitle: $scope.data.sTitle,
            sSummary: $scope.data.sDescription,
            parent_id: $viewer.get('iUserId'),
            parent_type: 'user',
            category_id: 0,
            auth_view: '0',
            auth_comment: '0',
        };

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


                $scope.dataReady = true;
            };

            var onError = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                // return scope.goBack();
            };
            $http2.post('videochannel/getVideoList', {sUrl:$scope.data.sChannelFeedUrl}).success(onSuccess).error(onError);
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
            $http2.post('videochannel/saveChannel', {
                sTitle:$scope.data.sTitle,
                iCategoryId:$scope.data.iCategoryId,
                sSiteId:$scope.data.sSiteId,
                sChannelFeedUrl:$scope.data.sChannelFeedUrl,
                sDescription:$scope.data.sDescription,
                auth_view:$scope.data.auth_view,
                auth_comment:$scope.data.auth_comment,
                aVideos: videoFilter()
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