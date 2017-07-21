define([
    'ynchat/model/ynchat-message'
], function(YnchatMessageModel) {

    return function($scope, $injector, $rootScope, $site, $modal, gettext, gettextCatalog, $http2, $q, $viewer, $ynchat, $ynchatWebsocket, $timeout) {

        $scope.itemModel = YnchatMessageModel;
        $scope.items = [];
        $scope.noItems = false;

        $scope.load = function() {

            if ($scope.isProcessingLoad) {
                return;
            }

            $scope.isProcessingLoad = true;

            $scope.canceller = $q.defer();

            var config = {
                // cache: true,
                timeout: $scope.canceller.promise
            };

            $http2.post($scope.messageListApi, $scope.messageListData, config).success($scope.loadSuccess).error($scope.loadError).
            finally(function() {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.isProcessingLoad = false;
            });
        };

        $scope.loadSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            var loadItems = data.aMoreMessages || data.aMessages || [];
            loadItems = loadItems.map($scope.prepareItem);

            if (0 == loadItems.length) {
                return $scope.noItems = true;
            }

            $scope.items = ($scope.messageListData.action == 'getOldConversation') ? $scope.addItemDate(loadItems) : loadItems;

            $scope.loadMessageComplete();
        };

        $scope.loadError = function(data, status, headers, config) {

            if ($scope.cancelled) {
                $scope.cancelled = false;
                return console.log('load', 'user cancelled', arguments);
            } else {
                console.error('load', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }
        };

        $scope.prepareItem = function(input) {

            var item = $.extend({}, $scope.itemModel, input);
            
            switch (item.getType()) {
                case 'file':
                    item.files = $scope.getFilesData(item);
                    break;
                case 'link':
                case 'video':
                    item.attachment = $scope.getAttachmentData(item);
                    break;
            }

            return item;
        };

        $scope.getFilesData = function(item) {

            return item.getData().map(function(file) {
                file.url = $ynchat.getFileUrl(file.id);
                return file;
            });
        };

        $scope.getAttachmentData = function(item) {

            return $.extend({}, item.getData(), {
                imageUrl: atob(item.getData().imageUrl),
                url: atob(item.getData().url)
            });
        };

        $scope.addItemDate = function(loadItems) {

            var items = [];
            var sDate = '';

            for (var i = 0; i < loadItems.length; i++) {
                if (loadItems[i].sDate != sDate) {
                    sDate = loadItems[i].sDate;
                    items.push($.extend({}, $scope.itemModel, {
                        sDate: sDate,
                        type: 'date'
                    }));
                }
                items.push(loadItems[i]);
            }

            return items;
        };

        $scope.$parent.onSendMessage = function(stickerId, files) {

            if ($scope.bNoConnection) {
                return console.log('No connection');
            }
            
            var sendData = {
                action: 'sendMessageAsText',
                data: {
                    iReceiverId: $scope.ynchatObj.getId(),
                    sUserIdHash: $ynchat.getConfig('sUserIdHash')
                }
            };

            if (files) {
                // send file
                sendData.action = 'sendMessageWithFiles';
                sendData.data.files = files;
                sendData.data.sText = '';
            } else if (stickerId) {
                // send sticker
                sendData.data.iStickerId = stickerId;
                sendData.data.sText = '';
            } else {
                // send text
                sendData.data.sText = $scope.messageData.sMessage;
            }
            
            $ynchatWebsocket.send('message', JSON.stringify(sendData));
            $scope.$parent.messageData = {
                sMessage: '',
                aFile: []
            };
        };

        $scope.addNewMessage = function(e, data) {

            var newItem = $scope.prepareItem(data);

            if (newItem.getSenderId() != $scope.ynchatObj.getId() && newItem.getReceiverId() != $scope.ynchatObj.getId()) {
                return;
            }

            $scope.items.push(newItem);
            $scope.noItems = false;

            $timeout($scope.scrollBottom, 300);
        };

        $scope.doResetQuery = function() {
            
            $scope.canceller && $scope.canceller.resolve('abort');
            $scope.cancelled = true;
            
            $scope.items = [];
            $scope.noItems = false;

            // reload
            $scope.isProcessingLoad = false;
            $scope.load();
        };

        if ($scope.ynchatObj.getId()) {
            $scope.load();
        }

        $scope.$on('ynchatWebsocket:newTextMessage', $scope.addNewMessage);
        $scope.$on('ynchatWebsocket:newFileMessage', $scope.addNewMessage);

        $scope.$on('ynchat:messageListDataUpdate', $scope.doResetQuery);
    };
});