define([
    'chat/model/chat-message',
], function(ChatMessageModel) {

    return function($scope, $injector, $rootScope, $site, $modal, gettext, gettextCatalog, $http2, $q, $viewer, $ionicScrollDelegate, socket) {
        $scope.itemModel = ChatMessageModel;
        $scope.items = [];
        $scope.noItems = false;

        $scope.prepareItem = function(input) {

            var item = $.extend({}, $scope.itemModel, input);
            return item;
        };

        var buildMessage = function(message, do_scroll, force) {
            if (typeof(message) == 'string') {
                message = JSON.parse(message);
            }
            if(message.thread_id != $scope.thread_id)
                return false;
            $scope.items.push($scope.prepareItem(message));
            $scope.scrollBottom();
        };
        $scope.scrollBottom = function() {
            $ionicScrollDelegate.scrollBottom();
        };
        socket.on('loadConversation', function (threads) {
            for (var i in threads) {
                var thread = $.parseJSON(threads[i]);

                buildMessage(thread);
            }
        });

        socket.on('chat', function (chat) {
            var users = chat.thread_id.split(':'), total_friends = 0;
            for (var i in users) {
                if (1 == users[i]) {
                    total_friends++;
                }
            }

            if (!total_friends) {
                console.log('Unable to chat with this user.');
                return;
            }
            buildMessage(chat, false, true);
        });
        socket.emit('loadConversation', {
            user_id: $viewer.get('iUserId'),
            thread_id: $scope.thread_id
        });


        $scope.doResetQuery = function() {

            $scope.canceller && $scope.canceller.resolve('abort');
            $scope.cancelled = true;

            $scope.items = [];
            $scope.noItems = false;

            // reload
            $scope.isProcessingLoad = false;
            socket.emit('loadConversation', {
                user_id: $viewer.get('iUserId'),
                thread_id: $scope.thread_id
            });
        };
        $scope.$on('build-message', function(event, args) {
            buildMessage(args, true, false);
        });
        $scope.$on('chat:objChange', function(event, args) {
            $scope.doResetQuery();
        });
    };
});