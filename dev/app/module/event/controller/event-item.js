define([
    'global/base/ItemController'
], function(ItemController) {
    return function($scope, $injector, gettext, gettextCatalog, $location, $modal, $site, $http2, $rootScope) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.onItemSetting = $scope._setting($scope, function() {
            var iconCheckBox = ionic.Platform.isAndroid() ? 'ion-android-checkbox' : 'ion-ios-checkmark';
            var iconCheckBoxBlank = ionic.Platform.isAndroid() ? 'ion-android-checkbox-outline-blank' : 'ion-ios-circle-outline';

            var settingBtns = [];

            if ($scope.item.canView()) {
                settingBtns.push({
                    text: ($scope.item.getRSVP() == 1 ? '<i class="icon ' + iconCheckBox + '"></i> ' : '<i class="icon ' + iconCheckBoxBlank + '"></i> ') + gettextCatalog.getString('Attending'),
                    action: $scope.onAttending
                });
                settingBtns.push({
                    text: ($scope.item.getRSVP() == 2 ? '<i class="icon ' + iconCheckBox + '"></i> ' : '<i class="icon ' + iconCheckBoxBlank + '"></i> ') + gettextCatalog.getString('Maybe Attending'),
                    action: $scope.onMaybeAttending
                });
                settingBtns.push({
                    text: ($scope.item.getRSVP() == 3 ? '<i class="icon ' + iconCheckBox + '"></i> ' : '<i class="icon ' + iconCheckBoxBlank + '"></i> ') + gettextCatalog.getString('Not Attending'),
                    action: $scope.onNotAttending
                });
            }

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

        $scope.onEdit = function() {
            $location.path('/app/' + $scope.item.getType() + '/' + $scope.item.getId() + '/edit');
        };


        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this event?'),
            'event/delete',
            function(){
                return {
                    iEventId: $scope.item.getId(),
                    sModelType: $scope.item.getType()
                };
            },
            function(data){
                $scope.items.deleteItem($scope.item.getId());
                $scope.$emit('event:deleted', $scope.item.getId());
            }
        );


        $scope.onAttending = function() {

            if (!$scope.item.canView() || $scope.isProcessingRSVP) {
                return;
            }

            $scope.currentRsvp = $scope.item.getRSVP();
            $scope.item.setRSVP(1);

            $scope.postRSVP('event/addrsvp', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType(),
                iRsvp: 1
            });
        };

        $scope.onMaybeAttending = function() {

            if (!$scope.item.canView() || $scope.isProcessingRSVP) {
                return;
            }

            $scope.currentRsvp = $scope.item.getRSVP();
            $scope.item.setRSVP(2);

            $scope.postRSVP('event/addrsvp', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType(),
                iRsvp: 2
            });
        };

        $scope.onNotAttending = function() {

            if (!$scope.item.canView() || $scope.isProcessingRSVP) {
                return;
            }

            $scope.currentRsvp = $scope.item.getRSVP();
            $scope.item.setRSVP(3);

            $scope.postRSVP('event/addrsvp', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType(),
                iRsvp: 3
            });
        };

        $scope.onLeave = function() {

            $scope.postRSVP('event/leave', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            });
        };

        $scope.onAcceptInvite = function() {

            $scope.postRSVP('event/accept', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            });
        };

        $scope.onIgnoreInvite = function() {

            $scope.postRSVP('event/reject', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            });
        };

        $scope.onCancelRequest = function() {

            $scope.postRSVP('event/cancel', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            });
        };

        $scope.onSendRequest = function() {

            $scope.postRSVP('event/request', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            });
        };

        $scope.onJoin = function() {

            $scope.postRSVP('event/join', {
                iEventId: $scope.item.getId(),
                sModelType: $scope.item.getType()
            });
        };

        $scope.postRSVP = function(api, postData) {

            if ($scope.isProcessingRSVP) {
                return;
            }

            $scope.isProcessingRSVP = true;

            $http2.post(api, postData)
                .success($scope.postRSVPSuccess)
                .error($scope.postRSVPError)
                .finally(function() {
                    $scope.isProcessingRSVP = false;
                });
        };

        $scope.postRSVPSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $.extend($scope.item, data.event_data);

            $scope.currentRsvp = null;
        };

        $scope.postRSVPError = function() {

            console.error('postRSVP', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));

            if ($scope.currentRsvp) {
                $scope.item.setRSVP($scope.currentRsvp);
            }
        };
    };
});