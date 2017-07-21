define([
    'global/base/BaseController'
], function(BaseController) {

    return function($scope, $injector, $http2, $site, $modal, gettext, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        /**
         * Get form
         */
        $scope.doGetFormError = function() {

            console.error('getForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        /**
         * Save data
         */
        $scope.doSaveError = function() {

            console.warn('doSaveError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
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
                    sModule: 'forum'
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
                sModule: 'forum'
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
                sModule: 'forum'
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
    };
});