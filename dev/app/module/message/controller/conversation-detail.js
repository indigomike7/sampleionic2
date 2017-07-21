define([
    'message/controller/conversation-item',
    'message/model/conversation',
    'user/model/user'
], function(ConversationItemCtrl, ConversationModel, Model) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(ConversationItemCtrl, this, {
            $scope: $scope
        });

        $scope.Viewer = $.extend({}, Model, $viewer.get());

        $scope.item = $.extend({}, ConversationModel, {
            iConversationId: $state.params.id
        });

        $scope.fetchData = function() {

            var postData = {
                iConversationId: $scope.item.getId()
            };

            $http2.get('message/conversation_detail', postData)
                .success($scope.fetchDataSuccess)
                .error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);
            $scope.recipients = $scope.item.getRecipients();
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.error('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.showMoreRecipients = function() {

            $scope.bShowMoreRecipients = true;
        };

        $scope.showLessRecipients = function() {

            $scope.bShowMoreRecipients = false;
        };

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($scope.item.canReply()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Reply'),
                    action: function() {
                        $location.path('app/message/' + $scope.item.getId() + '/reply');
                    }
                });
            }

            settingBtns.push({
                text: gettextCatalog.getString('Archive'),
                action: $scope.onItemDelete
            });

            return settingBtns;
        });

        $scope.onItemDelete = function() {

            if ($scope.isProcessingDelete) {
                return;
            }

            // archive
            $scope.doItemDelete();
        };

        $scope.doItemDelete = function() {

            $scope.isProcessingDelete = true;


            var postData = {
                iItemId: $scope.item.getId(),
                sType: ($scope.item.getFolderName() == 'sent') ? 'sentbox' : $scope.item.getFolderName()
            };

            $http2.post('message/delete', postData)
                .success($scope.doDeleteSuccess)
                .error($scope.doDeleteError)
                .finally(function() {
                    $scope.isProcessingDelete = false;
                });
        };

        $scope.doDeleteSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $location.path('app/messages');
        };

        $scope.doDeleteError = function() {

            console.error('message/delete', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.fetchData();
    };
});