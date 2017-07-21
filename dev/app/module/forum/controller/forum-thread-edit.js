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
        $scope.getForm = function() {

            var postData = {
                iThreadId: $state.params.iThreadId
            };

            $http2.post('forum/threadformedit', postData).success($scope.doGetFormSuccess).error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;
            $scope.doPrepareFormData();
            $scope.doPrepareAttachedItems();
            $scope.dataReady = true;
        };

        $scope.doPrepareFormData = function() {

            $scope.form.aThread = $.extend({}, ThreadModel, $scope.form.aThread);

            $scope.formData = $.extend($scope.formData, {
                iThreadId: $state.params.iThreadId,
                sTitle: $scope.form.aThread.getTitle(),
                sText: $scope.form.aThread.getTextNotParsed(),
                iIsClosed: $scope.form.aThread.isClosed() ? 1 : 0
            });
        };

        /**
         * Handle attach files
         */
        $scope.doPrepareAttachedItems = function() {

            if ($scope.form.aPoll.iPollId) {
                $scope.attachedPoll = $scope.form.aPoll;
            }

            $scope.attachedFiles = $scope.form.aThread.getAttachmentPhotos().map(function(att) {
                return {
                    iId: att.attachment_id,
                    sImagePath: att.data.photo_url,
                    type: 'file'
                };
            });

            $scope.attachedLinks = $scope.form.aThread.getAttachmentLinks().map(function(att) {
                return $.extend(att.data, {
                    iId: att.attachment_id,
                    url: att.data.link,
                    type: 'link'
                });
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

            $scope.formData.sAttachment = $scope.getAttachedIds().join();
            if ($scope.attachedPoll.iPollId) {
                $scope.formData.iPollId = $scope.attachedPoll.iPollId;
            }

            $http2.post('forum/threadedit', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
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