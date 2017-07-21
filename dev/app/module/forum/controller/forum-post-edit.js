define([
    'forum/controller/forum-post-form',
    'forum/model/post'
], function(PostFormCtrl, PostModel) {

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
            iPostId: $state.params.iPostId
        };

        $scope.getForm = function() {

            var postData = {
                iPostId: $state.params.iPostId
            };

            $http2.post('forum/postformedit', postData).success($scope.doGetFormSuccess).error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = $.extend({}, PostModel, data);
            $scope.formData.sText = $scope.form.getTextNotParsed();
            $scope.doPrepareAttachedItems();
            $scope.dataReady = true;
        };

        /**
         * Handle attach files
         */
        $scope.doPrepareAttachedItems = function() {

            $scope.attachedFiles = $scope.form.getAttachmentPhotos().map(function(att) {
                return {
                    iId: att.attachment_id,
                    sImagePath: att.data.photo_url,
                    type: 'file'
                };
            });

            $scope.attachedLinks = $scope.form.getAttachmentLinks().map(function(att) {
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

            $http2.post('forum/postedit', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
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