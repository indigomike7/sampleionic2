define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state, addChannelData) {
        $site.debug > 2 && console.log('VideoChannelAddChannelUrlCtrl');
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

        $scope.dataReady = false;
        $scope.form = {};
        $scope.isProcessing = false;

        $scope.formData = {
            sUrl: (addChannelData.data.sUrl != null) ? addChannelData.data.sUrl : '',
            parent_id: $viewer.get('iUserId'),
            parent_type: 'user',
        };

        if (typeof $state.params.sParentType != 'undefined') {
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId = $state.params.iParentId;
            $scope.formData.sSubjectType = $state.params.sParentType;
            $scope.formData.iSubjectId = $state.params.iParentId;
        }

        $scope.loadInit = function() {
            var sendData = {
                bPrivacyNoCustom: true
            };
            $scope.dataReady = true;
        };

        // implement do save
        $scope.doSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            var onSuccess = function(data) {
                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $scope.isProcessing = false; // release blocking status

                if (data.message) {
                    $modal.toast(data.message);
                }
                addChannelData.extend($scope.formData);
                addChannelData.data.aChannel = data.channelInfo;
                $scope.goToPage('/app/videochannel/add/channel/detail');
            };

            var onError = function() {
                console.warn('videochannel/addChannelUrl', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $scope.isProcessing = true;
            $http2.post('videochannel/addChannelUrl', $scope.formData).success(onSuccess).error(onError).finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.formData.sUrl) {
                bAlert && $modal.alert(gettextCatalog.getString('Please input a valid url'));
                return false;
            }
            return true;
        };

        $scope.loadInit();
    };
});