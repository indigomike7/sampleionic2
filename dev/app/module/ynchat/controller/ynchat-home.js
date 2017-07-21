define([
    'global/base/BrowseController',
    'ynchat/controller/ynchat-home-ipad',
    'text!tpl/ynchat/ynchat-search-dir.html',
    'text!tpl/ynchat/ynchat-detail.html'
], function(BrowseController, YnchatHomeIpadCtrl, searchTemplate, ynchatDetailTpl) {

    return function($scope, $injector, $http2, $site, $modal, gettext, gettextCatalog, $ynchat, $state) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.ynchatListData = {};

        $scope.ynchatId = $state.params.id || 0;

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

            if (!ionic.Platform.isIPad()) {
                if ($ynchat.getVibrateStatus() == 'on') {
                    settingBtns.push({
                        text: gettextCatalog.getString('Disable Vibration'),
                        action: function() {
                            $ynchat.setVibrateStatus('off');
                        }
                    });
                } else {
                    settingBtns.push({
                        text: gettextCatalog.getString('Enable Vibration'),
                        action: function() {
                            $ynchat.setVibrateStatus('on');
                        }
                    });
                }
            }

            return settingBtns;
        });

        $scope.onChangeStatus = function(iStatus) {

            if ($scope.isProcessingChangeStatus) {
                return;
            }

            $scope.isProcessingChangeStatus = true;

            var postData = {
                iStatus: iStatus,
                sUserIdHash: $ynchat.getConfig('sUserIdHash')
            };

            $http2.post('ynchat/updateStatusGoOnline', postData).success(function(data) {
                $scope.doChangeStatusSuccess(data, iStatus);
            }).error($scope.doChangeStatusError).finally(function() {
                $scope.isProcessingChangeStatus = false;
            });
        };

        $scope.doChangeStatusSuccess = function(data, iStatus) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.hideSheet();

            if (data.message) {
                $modal.toast(data.message);
            }

            $ynchat.setUserSetting({
                iIsGoOnline: iStatus
            });
        };

        $scope.doChangeStatusError = function() {

            console.warn('doChangeStatusError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        if (ionic.Platform.isIPad()) {
            $injector.invoke(YnchatHomeIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});