define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
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
            sUrl: '',
            parent_id: $viewer.get('iUserId'),
            parent_type: 'user',
            category_id: 0,
            auth_view: '0',
            auth_comment: '0',
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

            var onSuccess = function(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return scope.goBack();
                }

                $scope.form.categoryOptions = data.category_options;
                $scope.form.viewOptions = data.view_options;
                $scope.form.commentOptions = data.comment_options;

                $scope.formData.auth_view = data.default_privacy_setting;
                if ($scope.form.commentOptions.length > 0) {
                    $scope.formData.auth_comment = $scope.form.commentOptions[0].sValue;
                }

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

            var onSuccess = function(data) {
                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $scope.isProcessing = false; // release blocking status

                if (data.message) {
                    $modal.toast(data.message);
                }

                $scope.goBack();
            };

            var onError = function() {
                console.warn('videochannel/create', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $scope.isProcessing = true;
            
            $http2.post('videochannel/create', $scope.formData).success(onSuccess).error(onError).finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.formData.category_id) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a category this video will belong to.'));
                return false;
            }

            if (!$scope.formData.sUrl || !Validator.isUrl($scope.formData.sUrl)) {
                bAlert && $modal.alert(gettextCatalog.getString('Please provide a valid URL for your video.'));
                return false;
            }

            return true;
        };

        $scope.loadInit();
    };
});