define([
    'global/base/BrowseController',
    'chat/controller/chat-home-ipad',
    'text!tpl/chat/chat-search-dir.html',
    'text!tpl/chat/chat-detail.html',
    'text!tpl/chat/chat-smile.html'
], function(BrowseController, ChatHomeIpadCtrl, searchTemplate, chatDetailTpl) {
    return function($scope, $injector, $http2, $site, $modal, gettext, gettextCatalog, $chat, $state, $viewer, $ionicModal, $location, socket, $ionicPopover, $ionicActionSheet, $timeout, $rootScope) {
        var site = require('settings/site');

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });
        $scope.thread_id = '';
        $scope.searchTemplate = searchTemplate;
        $scope.dataReady = false;

        $scope.chatListData = {
            sAction: 'all',
            user_id: $viewer.get('iUserId')
        };

        $scope.chatId = $state.params.id || 0;

        $scope.userStatus = 'online';

        $scope.getStatus = function() {

            var postData = {
                user_id: $viewer.get('iUserId')
            };

            $http2.post('chat/getstatus', postData)
            .success($scope.getStatusSuccess)
            .error($scope.getStatusError)
            .finally(function() {
                $scope.dataReady = true;
            });
        };

        $scope.getStatusSuccess = function(data) {

            if (data.error_code) {
                console.warn('getStatusSuccess', data);
            }

            $scope.userStatus = data.sStatus;
        };

        $scope.getStatusError = function() {

            console.warn('getStatusError', arguments);
        };

        $scope.onChatSetting = $scope._setting($scope, function() {

            var settingBtns = [];
            if ($chat.isMuteNotification()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Unmute Notification'),
                    action: function() {
                        $chat.setMuteNotification(false);
                    }
                });
            } else {
                settingBtns.push({
                    text: gettextCatalog.getString('Mute Notification'),
                    action: function() {
                        $chat.setMuteNotification(true);
                    }
                });
            }

            if (!ionic.Platform.isIPad()) {
                if ($chat.getVibrateStatus() == 'on') {
                    settingBtns.push({
                        text: gettextCatalog.getString('Disable Vibration'),
                        action: function() {
                            $chat.setVibrateStatus('off');
                        }
                    });
                } else {
                    settingBtns.push({
                        text: gettextCatalog.getString('Enable Vibration'),
                        action: function() {
                            $chat.setVibrateStatus('on');
                        }
                    });
                }
            }

            return settingBtns;
        });

        $scope.doChangeStatusSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.hideSheet();

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.userStatus = data.sStatus;
        };

        $scope.doChangeStatusError = function() {

            console.warn('doChangeStatusError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.showChat = function(obj) {
            $scope.chatModal && $scope.chatModal.remove();

            $scope.chatObj = obj;

            if(parseInt($viewer.get('iUserId')) < parseInt($scope.chatObj.getId()))
                $scope.thread_id = $viewer.get('iUserId') + ':' + $scope.chatObj.getId();
            else
                $scope.thread_id = $scope.chatObj.getId() + ':' + $viewer.get('iUserId');

            $scope.$broadcast('chat:objChange', obj);
            $scope.chatModal = $ionicModal.fromTemplate(chatDetailTpl, {
                scope: $scope
            });

            $scope.chatModal.show();
        };

        $scope.hideChat = function() {

            $scope.chatModal.remove();
        };

        $scope.getStatus();

        $scope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams, options) {
            
            $scope.chatModal && $scope.chatModal.remove();
        });

        if (site.template == 'ipad') {
            $injector.invoke(ChatHomeIpadCtrl, this, {
                $scope: $scope
            });
        }

    //    init socket service
        $scope.initSocketService = function() {
            $http2.post('chat/getConfig')
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message || 'Can not load data from server');
                    }
                    socket.init(data.sChatServer);
                    $viewer.update({sSitePhotoLink:data.sSitePhotoLink});
                    socket.on('chat', function (chat) {
                        /*send notifications*/
                        if (chat.user.id != $viewer.get('iUserId')) {
                            $chat.setUnread(chat.user.id);
                            if ($chat.getVibrateStatus() == 'on' && (window.isInBackground || $state.current.module != 'chat')) {
                                $modal.vibrate(100);
                            }
                        }
                    });
                })
                .error(function (data) {
                })
                .finally(function() {
                    $scope.dataReady = true;
                });
        };
        $scope.initSocketService();

        //load emojis & init attachment
        $scope.emojis = [];
        $scope.attachment_id = {
            link: '',
            default_image: '',
            title: '',
            description: '',
            thread_id: ''
        };
        $scope.messageData = {
            sMessage: ''
        };

        //load emojis
        $scope.loadEmojis = function() {
            if($scope.emojis != '')
                return;
            $http2.get('chat/getEmojis', {
                bPrivacyNoCustom: true
            })
                .success(function(data) {
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{
                        $scope.emojis = data.content;
                    }

                }).error(function() {

            });
        };

        $scope.showUrlPopover = function($event) {
            $modal.prompt(gettextCatalog.getString('Paste your link here'), function(result) {
                $scope.$$phase || $scope.$apply();

                if (result.buttonIndex == 2) {
                    return true;
                }
                if (!result.input1) {
                    return false;
                }

                // Get video information
                $http2.post('chat/attachLink', {
                    sUrl: result.input1
                })
                    .success(function(data){
                        if (data.error_code) {
                            $modal.alert(data.error_message);
                        }else{
                            // set attachment preview
                            $scope.attachment_id = data;
                            $scope.attachment_id.thread_id = $scope.thread_id;
                        }
                    })
                    .error(function(){
                        console.log(arguments);
                    })
                    .finally(function(){
                        $scope.isProcessing = false;
                    });
            }, gettextCatalog.getString('Attach link'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);

        };
        $scope.disableScrollContent = function() {

            var $body = $('body');
            var $content = $('.yn-content');
            var top = $body.scrollTop();

            $content.css({
                'height': (window.innerHeight + top) + 'px',
                'overflow-y': 'hidden',
                'margin-top': '-' + top + 'px'
            });
        };

        $scope.enableScrollContent = function() {

            var $body = $('body');
            var $content = $('.yn-content');
            var top = $content.css('margin-top').match(/\d+/)[0];

            $content.css({
                'height': 'auto',
                'overflow-y': 'visible',
                'margin-top': 'initial'
            });
            $body.scrollTop(top);
        };

        $scope.addEmoticon = function(text) {

            $scope.messageData.sMessage += text;

            var ngInput = angular.element('.input-message');
            ngInput.scrollTop(ngInput[0].scrollHeight);
        };

        $scope.onOrientationChange = function() {

            $scope.smilePopover && $scope.smilePopover.hide();
        };

        window.addEventListener('orientationchange', $scope.onOrientationChange);

        $scope.removeAttachLink = function() {
            $scope.attachment_id = {
                link: '',
                default_image: '',
                title: '',
                description: '',
                thread_id: ''
            };
        };

        $scope.onSendMessage = function(){
            if($.trim($scope.messageData.sMessage) =='')
                return;
            // add message to message list
            var message = {
                text: $scope.messageData.sMessage,
                user: {
                    id: $viewer.get('iUserId'),
                    name: $viewer.get('sFullName'),
                    photo_link: $viewer.get('sSitePhotoLink')
                },
                time_stamp: Math.floor(Date.now() / 1000),
                thread_id: $scope.thread_id,
                attachment_id: ($scope.attachment_id.link ? $scope.attachment_id : ''),
                listing_id: 0
            };
            $rootScope.$broadcast('build-message', message);

            // emit chat event
            socket.emit('chat', message);
            $scope.messageData.sMessage = '';
            $scope.removeAttachLink();
        };
        $scope.loadEmojis();

    };
});