define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('VideoChannelAddChannelCtrl');
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
            channelSource: ''
        };
        $scope.loadInit = function() {
            var sendData = {
                bPrivacyNoCustom: true
            };
            $scope.dataReady = true;
        };
        $scope.loadInit();
    };
});