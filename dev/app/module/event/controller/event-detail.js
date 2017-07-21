define([
    'event/controller/event-item',
    'event/model/event',
    'text!tpl/event/event-guest-list.html',
    'text!tpl/event/event-invite-list.html'
], function(EventItemCtrl, EventModel, eventGuestListTpl, eventInviteListTpl) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $ionicModal) {

        var iEventId = $state.params.id;

        $injector.invoke(EventItemCtrl, this, {
            $scope: $scope
        });


        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this event?'),
            'event/delete',
            function() {
                return {
                    iEventId: $scope.item.getId(),
                    sModelType: $scope.item.getType()
                };
            },
            function(data){
                $scope.goBack();
            }
        );

        $scope.selectedTab = $state.params.tab || 'information';

        $scope.item = $.extend({}, EventModel, {
            iEventId: iEventId
        });

        if (typeof($state.current.modelType) != 'undefined') {
            angular.extend($scope.item, {
                sModelType: $state.current.modelType
            });
        }

        $scope.fetchData = function() {

            var postData = {
                iEventId: iEventId,
                //sModelType: $scope.item.getType()
            };

            $http2.get('event/detail', postData)
                .success($scope.fetchDataSuccess)
                .error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            //console.error('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($scope.item.canInvite()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Invite Guests'),
                    action: $scope.showInviteList
                });
            }

            settingBtns.push({
                text: gettextCatalog.getString('Share This Event'),
                action: $scope.onItemShare
            });

            if ($scope.item.canEdit()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Edit Event'),
                    action: $scope.onEdit
                });
            }

            if ($scope.item.canDelete()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Delete Event'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return settingBtns;
        });

        $scope.showInviteList = function() {

            $scope.inviteListModal = $ionicModal.fromTemplate(eventInviteListTpl, {
                scope: $scope
            });

            $scope.inviteListModal.show();
        };

        $scope.hideInviteList = function() {

            $scope.inviteListModal.hide();
        };

        $scope.doDeleteSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $location.path('app/events/my');
        };

        $scope.showGuestList = function(iRSVP) {

            $scope.guestListModalOptions = {
                iRSVP: iRSVP
            };

            $scope.guestListModal = $ionicModal.fromTemplate(eventGuestListTpl, {
                scope: $scope
            });

            $scope.guestListModal.show();
        };

        $scope.hideGuestList = function() {

            $scope.guestListModal.hide();
        };

        $scope.$on('$destroy', function() {

            $scope.inviteListModal && $scope.inviteListModal.remove();
            $scope.guestListModal && $scope.guestListModal.remove();
        });

        $scope.fetchData();
    };
});