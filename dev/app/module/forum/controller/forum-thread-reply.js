define([
    'forum/controller/forum-post-form',
    'forum/model/post',
    'global/helper'
], function(PostFormCtrl, PostModel, Helper) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(PostFormCtrl, this, {
            $scope: $scope
        });

        /**
         * Get form
         */
        $scope.form = {};
        $scope.dataReady = false;

        $scope.formData = {
            iThreadId: $state.params.iThreadId,
            iPostId: $state.params.iPostId
        };

        $scope.getForm = function() {

            var postData = {
                iThreadId: $state.params.iThreadId,
                iPostId: $state.params.iPostId
            };

            $http2.post('forum/threadformreply', postData).success($scope.doGetFormSuccess).error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;
            $scope.formData.iIsSubscribed = $scope.form.bIsSubscribed ? 1 : 0;
            $scope.formData.sText = Helper.unescapeHTML($scope.form.sQuote) || '';
            $scope.dataReady = true;
        };

        /**
         * Save data
         */
        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            $scope.formData.sAttachment = $scope.getAttachedIds().join();

            $http2.post('forum/threadreply', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
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

            $scope.goBack();
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sText) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide some text'));
                return false;
            }

            return true;
        };

        $scope.getForm();
    };
});