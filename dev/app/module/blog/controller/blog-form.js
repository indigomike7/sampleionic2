define([
    'global/base/BaseController',
    'text!tpl/blog/blog-preview.html',
    'blog/model/blog',
    'text!tpl/blog/blog-category-list.html'
], function(BaseController, PreviewTpl, BlogModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $ionicModal) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        /**
         * Prepare form data
         */
        $scope.form = {};
        $scope.formData = {};

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

            $scope.doPrepareFormData();

            $scope.dataReady = true;
        };

        $scope.doGetFormError = function() {

            console.error('getForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.doPrepareFormData = function() {

            $scope.formData = $.extend($scope.formData, {
                sStatus: 'publish',
                iPrivacy: $scope.form.view_options.length ? $scope.form.view_options[0].sValue : '',
                iPrivacyComment: $scope.form.comment_options.length ? $scope.form.comment_options[0].sValue : ''
            });
        };

        /**
         * Handle select categories
         */
        $scope.showCategories = function(filter) {

            $scope.sCategoriesFilter = filter;

            $scope.categoriesModal || ($scope.categoriesModal = $ionicModal.fromTemplate(require('text!tpl/blog/blog-category-list.html'), {
                scope: $scope
            }));

            $scope.categoriesModal.show();
        };

        $scope.hideCategories = function() {

            $scope.categoriesModal.hide();
        };

        $scope.$on('$destroy', function() {

            $scope.categoriesModal && $scope.categoriesModal.remove();
        });

        $scope.getSelectedCategoryIds = function() {

            var selected = [];

            for (filter in $scope.form.category_options) {
                var categories = $scope.form.category_options[filter] || [];
                for (var i = 0; i < categories.length; i++) {
                    if (categories[i].selected) {
                        selected.push(categories[i].category_id);
                    }
                }
            }

            return selected;
        };

        /**
         * Handle attach files
         */

        $scope.attachedFiles = [];
        $scope.attachedLinks = [];

        $scope.onAttachPhoto = function() {

            $scope.onAddPhoto('attachment/attachphoto', $scope.attachPhotoSuccess);
        };

        $scope.attachPhotoSuccess = function(data) {

            var imgTag = '[img]' + data.sImagePath + '[/img]';

            $scope.formData.sText = ($scope.formData.sText || '') + imgTag;
        };

        $scope.onAttachFile = function() {

            $scope.onAddPhoto('attachment/attachfile', $scope.attachFileSuccess);
        };

        $scope.attachFileSuccess = function(data) {

            $scope.attachedFiles.push($.extend(data, {
                type: 'file'
            }));
        };

        $scope.onAttachLink = function() {

            if ($scope.isProcessingAttach) {
                return;
            };

            $modal.prompt('http://www.example.com', function(result) {
                
                if (result.buttonIndex == 2) {
                    return true;
                }
                
                if (!result.input1) {
                    return false;
                }

                // prepare url
                if (!result.input1.match(/^[a-zA-Z]{1,5}:\/\//)) {
                    result.input1 = 'http://' + result.input1;
                }

                var params = {
                    sUrl: result.input1,
                    sModule: 'blog'
                };

                $scope.onPostAttachment(params, 'attachment/attachlink', $scope.attachLinkSuccess);
            }, gettextCatalog.getString('Attach Link'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.attachLinkSuccess = function(data) {

            $scope.attachedLinks.push($.extend(data.link_data, {
                iId: data.link_data.attachment_id,
                type: 'link'
            }));
        };

        $scope.onAddPhoto = function(api, callback) {

            $scope._setting($scope, function() {

                return [{
                    text: gettextCatalog.getString('Take Photo'),
                    action: function() {
                        $scope.doAddPhoto('CAMERA', api, callback);
                    }
                }, {
                    text: gettextCatalog.getString('Select From Gallery'),
                    action: function() {
                        $scope.doAddPhoto('PHOTOLIBRARY', api, callback);
                    }
                }];
            })();
        };

        $scope.doAddPhoto = function(sourceType, api, callback) {

            sourceType = sourceType || 'PHOTOLIBRARY';

            var getSuccess = function(fileURI) {
                $scope.onUploadAttachment(fileURI, api, callback);
                $scope.$$phrase || $scope.$apply();
            };

            var getFail = function(msg) {
                console.warn(msg);
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            };

            navigator.camera.getPicture(getSuccess, getFail, {
                quality: 50,
                destinationType: navigator.camera.DestinationType.FILE_URI,
                sourceType: Camera.PictureSourceType[sourceType],
                encodingType: Camera.EncodingType.JPEG,
                correctOrientation: true,
                targetWidth: $site.imgTargetSize,
                targetHeight: $site.imgTargetSize
            });
        };

        $scope.onUploadAttachment = function(fileURI, api, callback) {

            var errorMsg = 'Can not upload file. Please try again later.';

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || errorMsg);
                }

                if (!data.sImagePath) {
                    return $modal.alert(errorMsg);
                }

                callback && callback(data);
            };

            var errorCb = function(error) {

                console.error(api, arguments);

                if (error.code == FileTransferError.ABORT_ERR) {
                    return $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $modal.alert(errorMsg);
            };

            var params = {
                sModule: 'blog'
            };

            $http2.upload(api, fileURI, params).then(successCb, errorCb);
        };

        $scope.onPostAttachment = function(params, api, callback) {

            $scope.isProcessingAttach = true;

            var errorMsg = 'Can not load data from server';

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || errorMsg);
                }

                callback && callback(data);
            };

            var errorCb = function() {

                console.warn(api, arguments);
                $modal.alert(errorMsg);
            };

            $http2.post(api, params).success(successCb).error(errorCb).
            finally(function() {
                $scope.isProcessingAttach = false;
            });
        };

        $scope.onRemoveAttachment = function(att) {

            var errorMsg = 'Can not delete attachment. Please try again later.';

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || errorMsg);
                }

                att.type == 'link' ? $scope.removeAttachedLink(att) : $scope.removeAttachedFile(att);
            };

            var errorCb = function() {

                console.warn('attachment/delete', arguments);
                $modal.alert(errorMsg);
            };

            var postData = {
                sType: att.type,
                iItemId: att.iId,
                sModule: 'blog'
            };

            $modal.confirm(gettextCatalog.getString('Are you sure?'), function(selected) {
                if (1 == selected) {
                    $http2.post('attachment/delete', postData).success(successCb).error(errorCb);
                }
            }, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.removeAttachedFile = function(att) {

            for (var i = 0; i < $scope.attachedFiles.length; i++) {
                if ($scope.attachedFiles[i].iId == att.iId) {
                    return $scope.attachedFiles.splice(i, 1);
                }
            }
        };

        $scope.removeAttachedLink = function(att) {

            for (var i = 0; i < $scope.attachedLinks.length; i++) {
                if ($scope.attachedLinks[i].iId == att.iId) {
                    return $scope.attachedLinks.splice(i, 1);
                }
            }
        };

        $scope.getAttachedIds = function() {

            var attached = [];

            for (var i = 0; i < $scope.attachedFiles.length; i++) {
                attached.push($scope.attachedFiles[i].iId);
            }

            for (var i = 0; i < $scope.attachedLinks.length; i++) {
                attached.push($scope.attachedLinks[i].iId);
            }

            return attached;
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

            $http2.post('blog/create', $scope.formData)
                .success($scope.doSaveSuccess)
                .error($scope.doSaveError)
                .error($http2.defaultErrorHandler)
                .
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

            $location.path('app/blogs/my');
        };

        $scope.doSaveError = function() {

            console.warn('doSaveError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.preview = function() {
            $scope.previewItem = $.extend({}, BlogModel, $scope.formData);
            $scope.previewItem.sText = $scope.previewItem.sText.replace(/\[img]([^\[]*)\[\/img\]/g, '<img src="$1"/>');

            if (!$scope.previewModal) {
                $scope.previewModal = $ionicModal.fromTemplate(PreviewTpl, {
                    scope: $scope
                });
            }

            $scope.previewModal.show();
        };

        $scope.closePreview = function(){

            if ($scope.previewModal) {
                $scope.previewModal.hide();
            }
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Fill in a title for your blog'));
                return false;
            }

            if (!$scope.formData.sText) {
                bAlert && $modal.alert(gettextCatalog.getString('Add some content to your blog'));
                return false;
            }

            return true;
        };
    };
});