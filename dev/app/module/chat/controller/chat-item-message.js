define([
    'global/base/ItemController'
], function (Ctrl) {
    return function ($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal, socket) {
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemSetting = $scope._setting($scope, function () {
            var btns = [];
            if($scope.item.isViewer()) {
                btns.push({
                    text: gettextCatalog.getString('Delete'),
                    action: function () {
                        socket.emit('chat_delete', $scope.thread_id, $scope.item.getId());
                        $scope.items.deleteItem($scope.item.getId());
                    },
                    destructive: true
                });
            }
            return btns;
        });

        socket.on('chat_delete', function(key) {
            if($scope.item.time_stamp == key) {
                $scope.items.deleteItem($scope.item.getId());
            }
        });

    };

});
