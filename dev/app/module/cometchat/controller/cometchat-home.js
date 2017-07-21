define([
    'global/base/BrowseController',
    'cometchat/controller/cometchat-home-ipad',
    'text!tpl/cometchat/cometchat-search-dir.html',
    'text!tpl/cometchat/cometchat-detail.html'
], function(BrowseController, CometchatHomeIpadCtrl, searchTemplate, cometchatDetailTpl) {

    return function($scope, $injector, $http, $site, $modal, gettext, gettextCatalog, $cometchat, $state, $viewer, $ionicModal, $location) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.cometchatListData = {
            sAction: 'all',
            user_id: $viewer.get('iUserId')
        };

        $scope.cometchatId = $state.params.id || 0;

        $scope.onCometchatSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            settingBtns.push({
                text: $cometchat.getStatus() == 'available' ? '<b>' + gettextCatalog.getString('Available') + '</b>' : gettextCatalog.getString('Available'),
                action: function() {
                    $scope.onChangeStatus('available');
                }
            });
            settingBtns.push({
                text: $cometchat.getStatus() == 'busy' ? '<b>' + gettextCatalog.getString('Busy') + '</b>' : gettextCatalog.getString('Busy'),
                action: function() {
                    $scope.onChangeStatus('busy');
                }
            });
            settingBtns.push({
                text: $cometchat.getStatus() == 'invisible' ? '<b>' + gettextCatalog.getString('Invisible') + '</b>' : gettextCatalog.getString('Invisible'),
                action: function() {
                    $scope.onChangeStatus('invisible');
                }
            });
            settingBtns.push({
                text: $cometchat.getStatus() == 'offline' ? '<b>' + gettextCatalog.getString('Offline') + '</b>' : gettextCatalog.getString('Offline'),
                action: function() {
                    $scope.onChangeStatus('offline');
                }
            });

            if ($cometchat.isMuteNotification()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Unmute Notification'),
                    action: function() {
                        $cometchat.setMuteNotification(false);
                    }
                });
            } else {
                settingBtns.push({
                    text: gettextCatalog.getString('Mute Notification'),
                    action: function() {
                        $cometchat.setMuteNotification(true);
                    }
                });
            }

            if (!ionic.Platform.isIPad()) {
                if ($cometchat.getVibrateStatus() == 'on') {
                    settingBtns.push({
                        text: gettextCatalog.getString('Disable Vibration'),
                        action: function() {
                            $cometchat.setVibrateStatus('off');
                        }
                    });
                } else {
                    settingBtns.push({
                        text: gettextCatalog.getString('Enable Vibration'),
                        action: function() {
                            $cometchat.setVibrateStatus('on');
                        }
                    });
                }
            }

            return settingBtns;
        });

        $scope.onChangeStatus = function(sStatus) {

            if ($scope.isProcessingChangeStatus) {
                return;
            }

            $scope.isProcessingChangeStatus = true;

            var postData = {
                sStatus: sStatus,
                user_id: $viewer.get('iUserId')
            };

            $http.post($site.getCometchatApiUrl('chat/changestatus'), postData)
            .success($scope.doChangeStatusSuccess)
            .error($scope.doChangeStatusError).finally(function() {
                $scope.isProcessingChangeStatus = false;
            });
        };

        $scope.doChangeStatusSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.hideSheet();

            if (data.message) {
                $modal.toast(data.message);
            }

            $cometchat.setStatus(data.sStatus);
        };

        $scope.doChangeStatusError = function() {

            console.warn('doChangeStatusError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.showCometchat = function(obj) {

            $scope.cometchatModal && $scope.cometchatModal.remove();

            $scope.cometchatObj = obj;

            $scope.cometchatModal = $ionicModal.fromTemplate(cometchatDetailTpl, {
                scope: $scope
            });

            $scope.cometchatModal.show();
        };

        $scope.hideCometchat = function() {

            $scope.cometchatModal.remove();
        };

        $scope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams, options) {
            
            $scope.cometchatModal && $scope.cometchatModal.remove();
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(CometchatHomeIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});