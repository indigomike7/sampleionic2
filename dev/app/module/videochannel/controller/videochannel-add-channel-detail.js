define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state, addChannelData) {
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
        $.extend(addChannelData.data.aChannel, {
            parent_id: $viewer.get('iUserId'),
            parent_type: 'user',
            category_id: 0,
            auth_view: '0',
        });
        $scope.dataReady = false;
        $scope.form = {};
        $scope.isProcessing = false;

        $scope.formData = addChannelData.data.aChannel;

        $scope.loadInit = function() {
            var sendData = {
                bPrivacyNoCustom: true
            };

            var onSuccess = function(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return scope.goBack();
                }
                $scope.form.categoryOptions = data.category_options;
                $scope.form.viewOptions = data.view_options;
                $scope.formData.auth_view = data.default_privacy_setting;
                $scope.dataReady = true;
            };

            var onError = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                return scope.goBack();
            };

            $http2.post('videochannel/formadd', sendData).success(onSuccess).error(onError);
        };

        // implement do save
        $scope.doSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }
            addChannelData.extend($scope.formData);
            $scope.goToPage('/app/videochannel/add/channel/video');
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Please input channel title'));
                return false;
            }
            if (!$scope.formData.iCategoryId) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a category this channel will belong to.'));
                return false;
            }
            return true;
        };

        $scope.loadInit();
    };
});