define([
    'global/base/BaseController',
    'ynchat/model/ynchat',
    'text!tpl/ynchat/ynchat-smile.html'
], function(BaseController, YnchatModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $ynchat, $timeout, $ionicScrollDelegate, $ynchatWebsocket, $ionicPopover, $ionicActionSheet) {

        if (typeof cordova != 'undefined') {
            cordova.plugins.Keyboard.disableScroll(true);
        }

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.ynchatObj = $.extend({}, YnchatModel, {
            user_id: $state.params.id || 0
        });

        /**
         * Data for message list directive
         */
        $scope.messageListApi = 'ynchat/threadInfo';
        $scope.messageListData = {
            action: 'threadInfo',
            iReceiver: 0,
            iMessageId: 0,
            iNew: 0,
            sUserIdHash: $ynchat.getConfig('sUserIdHash'),
            iFriendId: $scope.ynchatObj.getId()
        };

        /**
         * Data for post new message
         */
        $scope.messageData = {
            sMessage: '',
            aFile: []
        };

        /**
         * Ynchat config
         */
        $scope.config = $ynchat.getConfig();

        $scope.$on('ynchat:updateConfig', function() {
            $scope.config = $ynchat.getConfig();
        });

        /**
         * Scroll view to bottom
         */
        $scope.scrollBottom = function() {

            if (ionic.Platform.isIOS()) {
                $ionicScrollDelegate.$getByHandle('ynchat-message-list').scrollBottom();
            } else {
                $('[delegate-handle="ynchat-message-list"]').animate({
                    scrollTop: $(document).height()
                });
            }
        };

        $scope.loadMessageComplete = function() {

            $timeout($scope.scrollBottom, 300);
        };

        /**
         * Check connection is offline or socket is not open
         */
        $scope.isNoConnection = function() {

            if (!$ynchatWebsocket.isOpen() || (navigator.connection && navigator.connection.type == Connection.NONE)) {
                return true;
            }

            return false;
        };

        $scope.updateConnectionStatus = function() {

            $scope.bNoConnection = $scope.isNoConnection();

            $timeout($scope.scrollBottom, 300);
        };

        $scope.bNoConnection = $scope.isNoConnection();

        document.addEventListener('offline', $scope.updateConnectionStatus, false);
        document.addEventListener('online', $scope.updateConnectionStatus, false);

        $scope.$on('ynchatWebsocket:close', $scope.updateConnectionStatus);
        $scope.$on('ynchatWebsocket:open', $scope.updateConnectionStatus);

        /**
         * Popover for pick emoticon, sticker
         */
        $scope.showSmilePopover = function($event) {

            $scope.smilePopover || ($scope.smilePopover = $ionicPopover.fromTemplate(require('text!tpl/ynchat/ynchat-smile.html'), {
                scope: $scope
            }));

            $timeout(function() {
                $scope.smilePopover.show($event);
                if (ionic.Platform.isAndroid()) {
                    $scope.disableScrollContent();
                }
            }, 300);
        };

        $scope.hideSmilePopover = function() {

            $scope.smilePopover && $scope.smilePopover.hide();
        };

        $scope.$on('popover.hidden', function() {
            
            if (ionic.Platform.isAndroid()) {
                $scope.enableScrollContent();
            }
        });

        $scope.disableScrollContent = function() {

            var $body = $('body');
            var $content = $('.yn-content');
            var top = $body.scrollTop();

            $content.css({
                'height': (window.innerHeight + top) + 'px',
                'overflow-y': 'hidden',
                'margin-top': '-' + top + 'px'
            });
        };

        $scope.enableScrollContent = function() {

            var $body = $('body');
            var $content = $('.yn-content');
            var top = $content.css('margin-top').match(/\d+/)[0];

            $content.css({
                'height': 'auto',
                'overflow-y': 'visible',
                'margin-top': 'initial'
            });
            $body.scrollTop(top);
        };

        $scope.onSendSticker = function(iStickerId) {

            $scope.hideSmilePopover();
            $scope.onSendMessage(iStickerId);
        };

        $scope.addEmoticon = function(text) {

            $scope.messageData.sMessage += text;

            var ngInput = angular.element('.input-message');
            ngInput.scrollTop(ngInput[0].scrollHeight);
        };

        $scope.onOrientationChange = function() {

            $scope.smilePopover && $scope.smilePopover.hide();
        };

        window.addEventListener('orientationchange', $scope.onOrientationChange);

        /**
         * Fix issue key board show but not focus to input
         * @todo Find better solution
         */
        $scope.keyboardShowHandler = function() {

            if ((ionic.Platform.isIPad() && $('.popover-ipad-search').is(':visible')) || $('.menu.menu-right .item-input-search').is(':visible')) {
                return; // search popover is opening
            }

            var ngInput = angular.element('.input-message');
            ngInput.focus();
            ngInput.val('').val($scope.messageData.sMessage); // move caret to end, it does not move automatically when adding emoticons
            ngInput.scrollTop(ngInput[0].scrollHeight); // scroll to bottom
        };

        window.addEventListener('native.keyboardshow', $scope.keyboardShowHandler);

        /**
         * Send photo
         */
        $scope.onPhotoClick = function() {

            if ($scope.isProcessingFile) {
                return;
            }

            var hideSheet = $ionicActionSheet.show({
                buttons: [{
                    text: gettextCatalog.getString('Take Photo')
                }, {
                    text: gettextCatalog.getString('Select From Gallery')
                }],
                cancelText: gettextCatalog.getString('Cancel'),
                cancel: function() {
                    // add cancel code..
                },
                buttonClicked: function(index) {
                    if (index == 0) {
                        $scope.onGetPicture('CAMERA');
                    } else {
                        if (ionic.Platform.isIOS()) {
                            // use input type="file" for iOS, it support to select multiple files
                            $('.input-file').focus().trigger('click');
                        } else {
                            // input type="file" is not supported on Android 4.4
                            $scope.onGetPicture('PHOTOLIBRARY');
                        }
                    }
                    return true;
                }
            });
        };

        $scope.onGetPicture = function(sourceType) {

            sourceType = sourceType || 'PHOTOLIBRARY';

            var getSuccess = function(dataURL) {
                $scope.uploadCapturedPhoto(dataURL);
            };

            var getFail = function(msg) {
                console.warn(msg);
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            };

            navigator.camera.getPicture(getSuccess, getFail, {
                quality: 50,
                destinationType: navigator.camera.DestinationType.DATA_URL,
                sourceType: Camera.PictureSourceType[sourceType],
                encodingType: Camera.EncodingType.JPEG,
                correctOrientation: true,
                targetWidth: $site.imgTargetSize,
                targetHeight: $site.imgTargetSize
            });
        };

        $scope.onFileChange = function(ele) {

            var $input = $(ele);
            var selectedFiles = $input.prop('files');

            // reset input
            $input.replaceWith($input.clone(true));

            // upload files
            var length = selectedFiles.length;
            if (length) {
                var validTypes = ['image/png', 'image/gif', 'image/jpeg', 'image/pjpeg'];
                for (var i = 0; i < length; i++) {
                    if (validTypes.indexOf(selectedFiles[i].type) > -1) {
                        $scope.messageData.aFile.push(selectedFiles[i]);
                    }
                }
                if (!$scope.messageData.aFile.length) {
                    return $modal.alert(gettextCatalog.getString('No valid photos has been selected. Please try again.'));
                }

                $scope.isProcessingFile = true;
                $scope.processFile(0);
            }
        };

        $scope.processFile = function(index) {

            var length = $scope.messageData.aFile.length;
            if (index < length) {
                $scope.readFile(index);
            } else {
                $scope.isProcessingFile = false;
                $scope.onSendMessage(null, JSON.stringify($scope.messageData.aFile));
            }
        };

        $scope.readFile = function(index) {

            if (!window.FileReader) {
                $scope.isProcessingFile = false;
                return $modal.alert(gettextCatalog.getString('Can not load file reader'));
            }

            var file = $scope.messageData.aFile[index];
            var reader = new FileReader();

            reader.onload = function(e) {

                $scope.messageData.aFile[index] = {
                    file_name: file.name,
                    file_data: e.target.result
                };

                $scope.uploadFile(index);
            };

            reader.readAsDataURL(file);
        };

        $scope.uploadFile = function(index) {

            var file = $scope.messageData.aFile[index];

            var sendData = {
                iReceiverId: $scope.ynchatObj.getId(),
                fileName: file.file_name,
                fileData: file.file_data
            };

            var onSuccess = function(data) {

                if (data.error_code) {
                    $scope.isProcessingFile = false;
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $scope.messageData.aFile[index] = {
                    id: data.id,
                    name: data.name,
                    type: data.type
                };

                $scope.processFile(index + 1);
            };

            var onError = function() {

                $scope.isProcessingFile = false;
                console.warn('ynchat/upload', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post('ynchat/upload', sendData).success(onSuccess).error(onError);
        };

        $scope.uploadCapturedPhoto = function(dataURL) {

            $scope.isProcessingFile = true;

            var sendData = {
                iReceiverId: $scope.ynchatObj.getId(),
                fileName: '[Untitled].jpg',
                fileData: 'data:image/jpeg;base64,' + dataURL
            };

            var onSuccess = function(data) {

                if (data.error_code) {
                    console.warn('ynchat/upload', JSON.stringify(arguments));
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $scope.messageData.aFile = [{
                    id: data.id,
                    name: data.name,
                    type: data.type
                }];

                $scope.onSendMessage(null, JSON.stringify($scope.messageData.aFile));
            };

            var onError = function() {

                console.warn('ynchat/upload', JSON.stringify(arguments));
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post('ynchat/upload', sendData).success(onSuccess).error(onError).
            finally(function() {
                $scope.isProcessingFile = false;
            });
        };

        $ynchat.setCurrent($scope.ynchatObj.getId());

        /**
         * Update view
         */
        window.addEventListener('native.keyboardshow', $scope.scrollBottom);
        window.addEventListener('native.keyboardhide', $scope.scrollBottom);

        /**
         * Destroy view
         */
        $scope.$on('$destroy', function() {

            $ynchat.removeCurrent();

            if (typeof cordova != 'undefined') {
                cordova.plugins.Keyboard.disableScroll(false);
            }

            $scope.smilePopover && $scope.smilePopover.remove();

            window.removeEventListener('orientationchange', $scope.onOrientationChange);
            window.removeEventListener('native.keyboardshow', $scope.scrollBottom);
            window.removeEventListener('native.keyboardhide', $scope.scrollBottom);
        });
    };
});