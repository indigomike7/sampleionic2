define([
    'comment/model/comment',
    'activity-attachment/controller/attachment-add'
], function(Model, AttachmentAddCtrl) {
    return function($scope, $injector, $http2, $site, $modal, gettext, gettextCatalog, $timeout, $ionicActionSheet) {
        $scope.isProcessingPost = false;
        $scope.isShowStickers = false;
        $scope.aStickers = [];
        $scope.aAttachmentItems = [];
        $scope.data = {
            adv_file: [],
            server_id: [],
            iItemId: $scope.obj.getId(),
            sItemType: $scope.obj.getType(),
            sParentId: $scope.obj.getParentModuleId(),
            sText: ''
        };
        $injector.invoke(AttachmentAddCtrl, this, {
            $scope: $scope
        });

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
                        $scope.onAddPhoto('CAMERA');
                    } else {
                        $scope.onAddPhoto('PHOTOLIBRARY');
                    }
                    return true;
                }
            });
        };

        $scope.doAddPhotoSuccess = function(fileURI) {

            $scope.aAttachmentItems.push({
                sPath: fileURI,
                sType: 'photo'
            });

            $scope.$$phase || $scope.$apply();
        };

        $scope.onPostComment = function() {
            if ($scope.isProcessingPost) {
                return;
            }

            if (!$scope.data.sText && !$scope.aAttachmentItems.length) {
                return $modal.alert(gettextCatalog.getString('Please enter your comment'));
            }

            $scope.isProcessingPost = true;

            if ($scope.aAttachmentItems.length) {
                // only get the last sticker
                $scope.cleanupAttachments();
            } else {
                $scope.doPostComment();
            }
        };

        $scope.cleanupAttachments = function(index) {

            // get the last sticker only
            var length = $scope.aAttachmentItems.length,
                hasSticker = 0,
                lastStickerFound = 0;
            for (var i=0;i<length;i++) {
                if ($scope.aAttachmentItems[i].sType === 'sticker') {
                    hasSticker = 1;
                    lastStickerFound = i;
                }
            }
            if (hasSticker) {
                $scope.aAttachmentItems = $scope.aAttachmentItems.slice(lastStickerFound, lastStickerFound + 1);
            }
            // start upload/post
            console.log('before process attachments');
            console.log($scope.aAttachmentItems);
            $scope.processAttachments(0);
        };

        $scope.processAttachments = function(index) {

            if (index >= $scope.aAttachmentItems.length) {
                return $scope.doPostComment();
                // return console.log($scope.aAttachmentItems);
            }

            var currentAttachment = $scope.aAttachmentItems[index];
            if (currentAttachment.sType === 'sticker') {
                $scope.data.sticker_id = currentAttachment.sticker_id;
                $scope.data.adv_file.push(currentAttachment.sticker_destination);
                $scope.data.data_type = 'sticker';
                $scope.processAttachments(index +1 );
            } else {
                // upload photo
                fileURI = currentAttachment.sPath;
                $scope.data.data_type = 'photo';
                $http2.upload('comment/upload',fileURI, {})
                    .then(function(data){
                        if(data.error_code || data.status === 0){
                            // skip error photo
                        }else{
                            $scope.data.adv_file.push(data.data.destination);
                            $scope.data.server_id.push(data.data.server_id);
                        }
                    }, function(error){
                        if (error.code === FileTransferError.ABORT_ERR) {
                            return $modal.toast(gettextCatalog.getString('Canceled'));
                        }

                        $modal.alert(gettextCatalog.getString('can not upload photos'));
                    })
                    .finally(function(){
                        $scope.processAttachments(index + 1);
                    });
            }
        };

        $scope.doPostComment = function(){
            $timeout(function() {
                if (window.cordova && window.cordova.plugins.Keyboard) {
                    window.cordova.plugins.Keyboard.close();
                }
            }, 100);
            console.log('before post comments');
            console.log($scope.data);

            $http2.post('comment/add', $scope.data)
                .success($scope.postSuccess)
                .error(function() {
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                })
                .finally(function() {
                    $scope.isProcessingPost = false;
                });
        };

        $scope.postSuccess = function(data) {
            if (data.error_code) {
                $modal.alert(data.error_message);
            } else {
                $modal.toast(gettextCatalog.getString('Comment posted successfully'));
                $scope.data.sText = '';
                $scope.data.adv_file = [];
                $scope.data.server_id = [];
                $scope.data.data_type = '';
                $scope.obj.comments.push($.extend({}, Model, data));
                $scope.obj.iTotalComment++;
                $scope.aAttachmentItems = [];
            }
        };

        // custom work

        $scope.onStickerClick = function() {
            $scope.isShowStickers = !$scope.isShowStickers;
        };

        $scope.onSelectSticker = function(sticker_id) {
            var length = $scope.aStickers.length;
            for (var i=0; i<length; i++) {
                if ($scope.aStickers[i].sticker_id == sticker_id) {
                    $scope.aAttachmentItems.push({
                        sType: 'sticker',
                        sPath: $scope.aStickers[i].sticker_path,
                        sticker_destination: $scope.aStickers[i].sticker_destination,
                        sticker_id: sticker_id
                    });
                }
            }
            $scope.isShowStickers = false;
        };

        $scope.getStickersSuccess = function(data) {
            if (!data.error_code) {
                $scope.aStickers = data;
            }
        };

        $scope.getStickers = function() {
            $http2.get('comment/get_stickers')
                .success($scope.getStickersSuccess)
        };

        $scope.removeAttachment = function(index) {
            $scope.aAttachmentItems.splice(index, 1);
        };

        $scope.getStickers();
    };
});
