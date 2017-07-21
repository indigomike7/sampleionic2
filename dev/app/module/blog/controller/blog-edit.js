define([
    'blog/controller/blog-form',
    'blog/model/blog',
    'global/helper'
], function(BlogFormCtrl, BlogModel, Helper) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(BlogFormCtrl, this, {
            $scope: $scope
        });

        $scope.item = $.extend({}, BlogModel, {
            iBlogId: $state.params.id || 0
        });

        /**
         * Prepare form data
         */
        $scope.getForm = function() {

            var postData = {
                iBlogId: $scope.item.getId()
            };

            $http2.post('blog/formedit', postData)
            .success($scope.doGetFormSuccess)
            .error($scope.doGetFormError);
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);
            $scope.form = data;

            $scope.doPrepareFormData();
            $scope.doPrepareCategoryOptions();
            $scope.doPrepareAttachedItems();
            
            $scope.dataReady = true;
        };

        $scope.doPrepareFormData = function() {

            $scope.formData = $.extend($scope.formData, {
                iBlogId: $scope.item.getId(),
                sTitle: Helper.unescapeHTML($scope.item.getTitle()),
                sText: Helper.unescapeHTML($scope.item.getTextNotParsed()),
                sStatus: $scope.item.isPublish() ? 'publish' : 'draft',
                iPrivacy: $scope.item.getPrivacy(),
                iPrivacyComment: $scope.item.getPrivacyComment()
            });
        };

        /**
         * Handle select categories
         */
        $scope.isSelectedCategory = function(id) {

            var selecteds = $scope.item.getSelectedCategories();

            for (var i = 0; i < selecteds.length; i++) {
                if (selecteds[i].category_id == id) {
                    return true;
                }
            }

            return false;
        };

        $scope.doPrepareCategoryOptions = function() {

            for (filter in $scope.form.category_options) {
                $scope.form.category_options[filter].map(function(option) {
                    option.selected = $scope.isSelectedCategory(option.category_id);
                    return option;
                });
            }
        };

        /**
         * Handle attach files
         */
        $scope.doPrepareAttachedItems = function() {

            $scope.attachedFiles = $scope.item.getAttachmentPhotos().map(function(att) {
                return {
                    iId: att.attachment_id,
                    sImagePath: att.data.photo_url,
                    type: 'file'
                };
            });

            $scope.attachedLinks = $scope.item.getAttachmentLinks().map(function(att) {
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

            $scope.formData.sCategories = $scope.getSelectedCategoryIds().join();
            $scope.formData.sAttachment = $scope.getAttachedIds().join();

            $http2.post('blog/edit', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
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

            $location.path(data.iBlogId ? ('app/blog/' + data.iBlogId) : 'app/blogs/my');
        };

        $scope.getForm();
    };
});
