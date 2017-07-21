define([
    'blog/controller/blog-form'
], function(BlogFormCtrl) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $ionicModal) {

        $injector.invoke(BlogFormCtrl, this, {
            $scope: $scope
        });

        /**
         * Prepare form data
         */

        // for creating blog in pages
        if(typeof $state.params.sParentType != 'undefined'){
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId =  $state.params.iParentId;
        }

        $scope.getForm = function() {

            $http2.post('blog/formadd')
                .success($scope.doGetFormSuccess)
                .error($http2.defaultErrorHandler)
                .error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;

            $scope.doPrepareFormData(data);

            $scope.dataReady = true;
        };

        $scope.doPrepareFormData = function(data) {

            $scope.formData = $.extend($scope.formData, {
                sStatus: 'publish',
                iPrivacy: data.default_privacy_setting,
                iPrivacyComment: $scope.form.comment_options.length ? $scope.form.comment_options[0].sValue : ''
            });
        };

        /**
         * Save data
         */
        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $scope.formData.sCategories = $scope.getSelectedCategoryIds().join();
            $scope.formData.sAttachment = $scope.getAttachedIds().join();

            $http2.post('blog/create', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
                finally(function() {
                    $scope.isProcessing = false;
                });
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            // change to goback to go back to page's blog after creating a blog
            $scope.goBack();
        };

        $scope.getForm();
    };
});