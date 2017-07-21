define([
    'ynchat/controller/ynchat-base',
    'ynchat/model/ynchat',
    'text!tpl/ynchat/ynchat-history.html'
], function(YnchatBaseCtrl, YnchatModel) {

    return function($scope, $injector, $ynchat, $ionicScrollDelegate, gettext, gettextCatalog, $location, $ionicModal) {

        $injector.invoke(YnchatBaseCtrl, this, {
            $scope: $scope
        });

        $scope.scrollTop = function() {

            $ionicScrollDelegate.$getByHandle('ynchat-message-list').scrollTop();
        };

        $scope.setObj = function(obj) {

            $scope.ynchatObj = obj;

            $ynchat.setCurrent($scope.ynchatObj.getId());

            $scope.messageListData.iFriendId = $scope.ynchatObj.getId();

            $scope.scrollTop();

            $scope.$broadcast('ynchat:messageListDataUpdate', $scope.messageListData);
        };

        $scope.setObjById = function(id) {

            var obj = $.extend({}, YnchatModel, {
                user_id: id
            });

            $scope.setObj(obj);
        };

        $scope.onYnchatSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($ynchat.getUserSetting('iIsGoOnline') == 1) {
                settingBtns.push({
                    text: gettextCatalog.getString('Go Offline'),
                    action: function() {
                        $scope.onChangeStatus(0);
                    }
                });
            } else {
                settingBtns.push({
                    text: gettextCatalog.getString('Go Online'),
                    action: function() {
                        $scope.onChangeStatus(1);
                    }
                });
            }

            if ($ynchat.isMuteNotification()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Unmute Notification'),
                    action: function() {
                        $ynchat.setMuteNotification(false);
                    }
                });
            } else {
                settingBtns.push({
                    text: gettextCatalog.getString('Mute Notification'),
                    action: function() {
                        $ynchat.setMuteNotification(true);
                    }
                });
            }

            if ($scope.ynchatObj.getId()) {
                settingBtns.push({
                    text: gettextCatalog.getString('View old conversations'),
                    action: $scope.showHistory
                });
            }

            return settingBtns;
        });

        $scope.showHistory = function() {

            $scope.historyModal && $scope.historyModal.remove();
            $scope.historyModal = $ionicModal.fromTemplate(require('text!tpl/ynchat/ynchat-history.html'), {
                scope: $scope
            });

            $scope.historyModal.show();
        };

        $scope.closeHistory = function() {

            $scope.historyModal && $scope.historyModal.remove();
        };

        $scope.$on('$destroy', function() {

            $scope.historyModal && $scope.historyModal.remove();
        });
    };
});