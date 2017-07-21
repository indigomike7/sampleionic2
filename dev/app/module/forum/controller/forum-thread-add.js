define([
    'forum/controller/forum-thread-form',
    'forum/model/thread',
], function(ThreadFormCtrl, ThreadModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $ionicModal) {

        $injector.invoke(ThreadFormCtrl, this, {
            $scope: $scope
        });

        /**
         * Get form
         */
        $scope.formData = {
            iForumId: $state.params.iForumId,
            iIsSubscribed: 1,
            sTypeId: 'thread',
            iIsClosed: 0
        };

        $scope.getForm = function() {

            var postData = {
                iForumId: $state.params.iForumId
            };

            $http2.post('forum/threadformadd', postData).success($scope.doGetFormSuccess).error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;
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
            if ($scope.attachedPoll.iPollId) {
                $scope.formData.iPollId = $scope.attachedPoll.iPollId;
            }

            $http2.post('forum/threadadd', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
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

            $location.path(data.iThreadId ? 'app/forum_thread/' + data.iThreadId : 'app/forum/' + $state.params.iForumId);
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a title for your thread'));
                return false;
            }

            if (!$scope.formData.sText) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide some text'));
                return false;
            }

            return true;
        };

        $scope.getForm();
    };
});