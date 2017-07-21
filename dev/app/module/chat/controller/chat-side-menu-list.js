define([
    'chat/model/chat',
    'global/base/ListController'
], function(ChatModel, ListCtrl) {

    return function($scope, $injector, $interval, $chat, $q, $http2, $site, $modal, gettext, gettextCatalog) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            isFirstLoad: true,
            noMoreText: null,
            enableLoadMore: false,
            itemModel: ChatModel,
            apiService: 'chat/getFriendList',
            getQueryData: function() {
                return $scope.$parent.chatListData;
            }
        });

        $scope.loadMore = function() {
            
            $scope.$qMore = $q.defer();

            $scope.listData =  $scope.getQueryData();
            
            $http2.post($scope.apiService, $scope.listData,{
                // cache: true,
                timeout: $scope.$qMore.promise
            })
            .success($scope.loadMoreSuccess)
            .error($scope.loadMoreError)
            .finally(function() {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                if (!$scope.enableLoadMore) {
                    $scope.canLoadMore = false;
                }
            });
        };

        $scope.loadMoreSuccess = function(data) {

            if (data.error_code) {
                $scope.canLoadMore = false;
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }
            
            var extraData = $scope.getItemExtraData();
            var regEx = new RegExp($scope.listData.sSearch, 'i');

            var moreItems = data.map(function(item) {
                item = $.extend({}, $scope.itemModel, item, extraData);
                item.setHidden(!regEx.test(item.getTitle()));
                return item;
            });

            $scope.items = moreItems.sort($scope.sortRule);

            $scope.noItems = ($scope.getTotalShow() == 0);

            $scope.onLoadMoreEnd();

            if ($scope.isFirstLoad) {
                $scope.isFirstLoad = false;
            }
        };

        $scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad && $scope.chatId) {
                $scope.showChatById($scope.chatId);
            }
        };

        $scope.sortRule = function(a, b) {

            // Sorting rule – the weight of sorting corresponding to the list as below:
            // - New message on top
            // - Online on top
            // - Alphabetical of display name
            
            if (a.isUnread() && !b.isUnread()) {
                return -1;
            }
            if (!a.isUnread() && b.isUnread()) {
                return 1;
            }
            
            if (a.getStatus() == 'online' && b.getStatus() != 'online') {
                return -1;
            }
            if (a.getStatus() != 'online' && b.getStatus() == 'online') {
                return 1;
            }
            
            if (a.getTitle() < b.getTitle()) {
                return -1;
            }
            if (a.getTitle() > b.getTitle()) {
                return 1;
            }

            return 0;
        };

        $scope.getTotalShow = function() {

            var showCnt = 0;

            for (var i = 0; i < $scope.items.length; i++) {
                if (!$scope.items[i].isHidden()) {
                    showCnt++;
                }
            }

            return showCnt;
        };

        $scope.$parent.onSearch = function() {

            $scope.listData = $scope.getQueryData();
            var regEx = new RegExp($scope.listData.sSearch, 'i');

            for (var i = 0; i < $scope.items.length; i++) {
                $scope.items[i].setHidden(!regEx.test($scope.items[i].getTitle()));
            }

            $scope.noItems = ($scope.getTotalShow() == 0);
        };

        $scope.getItemById = function(id) {

            for (var i = 0; i < $scope.items.length; i++) {
                if ($scope.items[i].getId() == id) {
                    return $scope.items[i];
                }
            }

            return null;
        };

        $scope.showChatById = function(id) {

            var obj = $scope.getItemById(id);

            if (obj) {
                $scope.showChat(obj);
            }
        };

        $scope.updatePromise = $interval($scope.loadMore, 30e3);

        $scope.$on('$destroy', function() {
            $interval.cancel($scope.updatePromise);
        });

        $scope.$on('chat:updateUnread', function() {
            $scope.items.sort($scope.sortRule);
        });

        $scope.$on('chat:ocnClick', function(e, data) {
            if (typeof(data.id) != 'undefined') {
                $scope.showChatById(data.id);
            }
        });
    };
});