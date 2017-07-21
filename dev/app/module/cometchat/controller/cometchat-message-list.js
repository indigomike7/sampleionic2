define([
    'cometchat/model/cometchat-message',
    'global/base/ListController',
    'global/helper',
    'moment'
], function(CometchatMessageModel, ListCtrl, Helper, Moment) {

    return function($scope, $injector, $rootScope, $site, $modal, gettext, gettextCatalog, $http, $q, $viewer, $ionicScrollDelegate) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            isFirstLoad: true,
            groupedItems: [],
            apiService: 'chat/getmessages',
            itemModel: CometchatMessageModel,
            listById: true,
            noMoreText: null,
            listData: {
                iMessageAmount: 10,
                iItemId: $scope.cometchatObj.getId(),
                sItemType: 'cometchat',
                sAction: 'more',
                user_id: $viewer.get('iUserId')
            },
            messagePeriod: 3 // in minute
        });

        $scope.loadMore = function() {

            if ($scope.isProcessingLoadMore) {
                return;
            }

            $scope.isProcessingLoadMore = true;

            $scope.canceller = $q.defer();

            var config = {
                // cache: true,
                timeout: $scope.canceller.promise
            };

            $http.post($site.getCometchatApiUrl($scope.apiService), $scope.listData, config).success($scope.loadMoreSuccess).error($scope.loadMoreError).
            finally(function() {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.isProcessingLoadMore = false;
            });
        };

        $scope.loadMoreSuccess = function(data) {

            if (data.error_code) {
                $scope.canLoadMore = false;
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            var extraData = $scope.getItemExtraData();

            var moreItems = data.map(function(item) {
                return $.extend({}, $scope.itemModel, item, extraData);
            });

            if (0 == moreItems.length) {
                $scope.canLoadMore = false;
                
                if ($scope.isFirstLoad) {
                    $scope.isFirstLoad = false;
                    $scope.scrollBottom();
                }

                if (0 == $scope.items.length) {
                    return $scope.noItems = true;
                } else {
                    return $scope.noMoreText && $modal.toast($scope.noMoreText);
                }
            }

            $scope.items = moreItems.concat($scope.items);

            $scope.listData.iMaxId = $scope.items[0].getId();

            $scope.groupedItems = $scope.groupItems($scope.items);

            if ($scope.isFirstLoad) {
                $scope.isFirstLoad = false;
                $scope.scrollBottom();
            }

            $scope.onLoadMoreEnd();
        };

        $scope.groupItems = function(items) {

            var rs = $scope.groupItemsLv1(items);

            return rs.map(function(items) {
                return $scope.groupItemsLv2(items);
            });
        };

        $scope.groupItemsLv1 = function(items) {

            var rs = [];
            var iTimestamp = 0; // different date
            var iSenderId = 0; // different user
            var sStatus = null; // error or different status

            for (var i = 0; i < items.length; i++) {
                if (!Moment(items[i].getTimestamp()).isSame(iTimestamp, 'day') 
                || items[i].getStatus() == 'error' || (items[i].getStatus() != sStatus) 
                || items[i].getSenderId() != iSenderId) {
                    iTimestamp = items[i].getTimestamp();
                    iSenderId = items[i].getSenderId();
                    sStatus = items[i].getStatus();
                    rs.push([]);
                }
                rs[rs.length - 1].push(items[i]);
            }

            return rs;
        };

        $scope.groupItemsLv2 = function(items) {

            var rs = [];
            var iTimestamp = -1e6; // period between 2 message

            for (var i = 0; i < items.length; i++) {
                var period = (items[i].getTimestamp() - iTimestamp) / 60e3;
                if (period > $scope.messagePeriod) {
                    rs.push([]);
                }
                iTimestamp = items[i].getTimestamp();
                rs[rs.length - 1].push(items[i]);
            }

            return rs;
        };

        $scope.$parent.onSendMessage = function() {

            $.extend($scope.messageData, {
                iItemId: $scope.cometchatObj.getId(),
                sItemType: 'cometchat',
                sMessage: Helper.escapeHTML($scope.messageData.sMessage),
                user_id: $viewer.get('iUserId')
            });

            var position = $scope.addSendingMessage($scope.messageData);

            var success = function(data) {
                $scope.sendMessageSuccess(data, position);
            };

            var error = function() {
                $scope.sendMessageError(arguments, position);
            };

            $http.post($site.getCometchatApiUrl('chat/sendmessage'), $scope.messageData)
            .success(success)
            .error(error);

            $scope.$parent.messageData = {};
        };

        $scope.sendMessageSuccess = function(data, position) {

            console.log('sendMessageSuccess', data, position);

            var index = $scope.getItemIndexByPosition(position, $scope.items);

            if (data.error_code) {
                $scope.items[index].setStatus('error');
            } else {
                $.extend($scope.items[index], data);
                $scope.items[index].setStatus('');
            }

            $scope.groupedItems = $scope.groupItems($scope.items);

            $scope.scrollBottom();
        };

        $scope.sendMessageError = function(arguments, position) {

            console.warn('sendMessageError', arguments, position);

            var index = $scope.getItemIndexByPosition(position, $scope.items);
            $scope.items[index].setStatus('error');

            $scope.groupedItems = $scope.groupItems($scope.items);

            $scope.scrollBottom();
        };

        $scope.addSendingMessage = function(messageData) {

            $scope.noItems = false;

            var newItem = $.extend({}, CometchatMessageModel, {
                iMessageId: 0,
                iSenderId: $viewer.get('iUserId'),
                iItemId: $viewer.get('iUserId'),
                sItemType: 'user',
                sMessage: messageData.sMessage,
                iTimestamp: 0,
                sStatus: 'sending'
            });

            var fromId = $scope.items.length ? $scope.items[0].getId() : 0;
            var newLength = $scope.items.push(newItem);

            $scope.groupedItems = $scope.groupItems($scope.items);

            $scope.scrollBottom();

            // return position of new message to update after receive response from server
            return {
                fromId: fromId,
                newIndex: newLength - 1
            };
        };

        $scope.getItemIndexByPosition = function(position, items) {

            var fromIndex = $scope.getItemIndexById(position.fromId, items);

            if (fromIndex == -1) {
                return position.newIndex;
            }

            return fromIndex + position.newIndex;
        };

        $scope.getItemIndexById = function(id, items) {

            for (var i = 0; i < items.length; i++) {
                if (items[i].getId() == id) {
                    return i;
                }
            }

            return -1;
        };

        $scope.scrollBottom = function() {
            
            $ionicScrollDelegate.$getByHandle('cometchat-message-list').scrollBottom();
        };

        $scope.addNewMessage = function(e, newItems) {

            var newCnt = 0;
            for (var i = 0; i < newItems.length; i++) {
                if (newItems[i].getSenderId() == $scope.cometchatObj.getId()) {
                    $scope.items.push(newItems[i]);
                    newCnt++;
                }
            }

            if ($scope.items.length) {
                $scope.listData.iMaxId = $scope.items[0].getId();
                $scope.noItems = false;
            }

            if (newCnt > 0) {
                $scope.groupedItems = $scope.groupItems($scope.items);
                $scope.scrollBottom();
            }
        };

        $scope.doResetQuery = function(listData) {
            
            $scope.listData.iItemId = $scope.cometchatObj.getId();

            $scope.listData.iMinId = 0;
            $scope.listData.iMaxId = 0;
            
            $scope.items = [];
            $scope.groupedItems = [];
            $scope.noItems = false;
            $scope.canLoadMore = true;
            $scope.isFirstLoad = true;

            // reload
            $scope.loadMore();
        };

        $scope.onObjChange = function(e, obj) {

            $scope.canceller && $scope.canceller.resolve('abort');
            $scope.cancelled = true;

            $scope.doResetQuery();
        };

        $scope.loadMore();

        window.addEventListener('native.keyboardshow', $scope.scrollBottom);
        window.addEventListener('native.keyboardhide', $scope.scrollBottom);

        $scope.$on('cometchat:ping', $scope.addNewMessage);

        $scope.$on('cometchat:objChange', $scope.onObjChange);

        $scope.$on('$destroy', function() {
            window.removeEventListener('native.keyboardshow', $scope.scrollBottom);
            window.removeEventListener('native.keyboardhide', $scope.scrollBottom);
        });
    };
});