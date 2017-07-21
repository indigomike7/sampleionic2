define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state, addChannelData) {
        $site.debug > 2 && console.log('VideoChannelAddChannelKeywordsCtrl');
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
            keywords:(addChannelData.data.keywords != null) ? addChannelData.data.keywords : '',
        };

        if (typeof $state.params.sParentType != 'undefined') {
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId = $state.params.iParentId;
            $scope.formData.sSubjectType = $state.params.sParentType;
            $scope.formData.iSubjectId = $state.params.iParentId;
        }

        $scope.loadInit = function() {
            $scope.dataReady = true;
        };

        // implement do save
        $scope.doSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }
            addChannelData.extend($scope.formData);

            $scope.goToPage('/app/videochannel/add/channel/list');
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.formData.keywords) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter channel keywords'));
                return false;
            }
            return true;
        };

        $scope.loadInit();
    };
});