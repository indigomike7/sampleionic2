define([
    'forum/model/post',
    'forum/model/thread',
    'global/base/BaseController',
    'moment',
    'poll/model/poll',
    'text!tpl/forum/forum-poll-voters.html'
], function(PostModel, ThreadModel, BaseController, Moment, PollModel) {

    return function($scope, $injector, $state, $http2, $site, gettext, gettextCatalog, $modal, $location, $ionicScrollDelegate, $ionicModal, $history) {

        $scope.Moment = Moment;

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.item = $.extend({}, ThreadModel, {
            iThreadId: $state.params.iThreadId
        });

        $scope.sendData = {
            iPage: 1,
            iAmountOfPost: 10,
            iThreadId: $scope.item.getId()
        };

        $scope.totalPage = 1;

        $scope.getItem = function() {

            $http2.get('forum/threaddetail', $scope.sendData).success($scope.getItemSuccess).error($scope.getItemError);
        };

        $scope.getItemSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data.aThread);
            $scope.totalPage = Math.ceil($scope.item.getTotalPost() / $scope.sendData.iAmountOfPost) || 1;

            $scope.posts = $scope.mapPosts(data.aPost);

            if ($scope.item.hasPoll()) {
                $scope.poll = $.extend({}, PollModel, data.aPoll);
                $scope.pollView = $scope.poll.isVoted() ? 'results' : 'questions';
                $scope.loadPollVoters();
            }

            $scope.dataReady = true;

        };

        $scope.getItemError = function() {

            console.error('getItem', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.mapPosts = function(items) {

            return (items || []).map(function(item) {
                return $.extend({}, PostModel, item);
            });
        };

        $scope.onChangePage = function(e, iPage) {

            $scope.posts = [];
            $scope.sendData.iPage = iPage;
            $scope.loadPosts(iPage);

            $ionicScrollDelegate.$getByHandle('forum-thread-detail').scrollTop();
        };

        $scope.loadPosts = function(iPage) {

            $scope.isLoadingPosts = true;

            $http2.get('forum/threaddetail', $scope.sendData).success(function(data) {
                if (iPage == $scope.sendData.iPage) {
                    $scope.loadPostsSuccess(data);
                }
            }).error(function() {
                if (iPage == $scope.sendData.iPage) {
                    console.warn('loadPosts', arguments);
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                }
            }).
            finally(function() {
                if (iPage == $scope.sendData.iPage) {
                    $scope.isLoadingPosts = false;
                }
            });
        };

        $scope.loadPostsSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.posts = $scope.mapPosts(data.aPost);
        };

        $scope.$on('pagination:change', $scope.onChangePage);

        $scope.onItemSubscribe = function(bSubscrible) {

            if ($scope.isProcessingSubscrible) {
                return;
            }

            $scope.isProcessingSubscrible = true;

            var api = bSubscrible ? 'forum/threadsubscribe' : 'forum/threadunsubscribe';

            var sendData = {
                iThreadId: $scope.item.getId()
            };

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                if (data.message) {
                    $modal.toast(data.message);
                }

                $scope.item.bIsSubscribed = bSubscrible;
            };

            var errorCb = function() {

                console.warn(api, arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            $http2.post(api, sendData).success(successCb).error(errorCb).
            finally(function() {
                $scope.isProcessingSubscrible = false;
            });
        };

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure?'),
            'forum/threaddelete',
            function() {
                return {
                    iThreadId: $scope.item.getId()
                };
            },
            function(data) {
                $history.splice();
                $location.path('app/forum/' + $scope.item.getForumId());
            }
        );

        $scope.onItemShare = function() {

            if (typeof window.plugins == 'undefined' || !$scope.item.getSocialShareUrl()) {
                return;
            }

            window.plugins.socialsharing.share(null, null, null, $scope.item.getSocialShareUrl());
        };

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];

            if ($scope.item.canShare()) {
                btns.push({
                    text: gettextCatalog.getString('Share'),
                    action: $scope.onItemShare
                });
            }

            if ($scope.item.isSubscribed()) {
                btns.push({
                    text: gettextCatalog.getString('Unsubscribe'),
                    action: function() {
                        $scope.onItemSubscribe(false);
                    }
                });
            } else {
                btns.push({
                    text: gettextCatalog.getString('Subscribe'),
                    action: function() {
                        $scope.onItemSubscribe(true);
                    }
                });
            }

            if ($scope.item.canEdit()) {
                btns.push({
                    text: gettextCatalog.getString('Edit thread'),
                    action: function() {
                        $location.path('app/forum_thread/' + $scope.item.getId() + '/edit');
                    }
                });
            }

            if ($scope.item.canDelete()) {
                btns.push({
                    text: gettextCatalog.getString('Delete thread'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return btns;
        });

        /**
         * Handle view poll
         */
        $scope.onPollSettings = $scope._setting($scope, function() {

            var btns = [];

            if (!$scope.poll.isVoted()) {
                if ($scope.pollView != 'questions') {
                    btns.push({
                        text: gettextCatalog.getString('View Questions'),
                        action: function() {
                            $scope.pollView = 'questions';
                        }
                    });
                }

                if ($scope.pollView != 'results') {
                    btns.push({
                        text: gettextCatalog.getString('View Results'),
                        action: function() {
                            $scope.pollView = 'results';
                        }
                    });
                }
            }

            if ($scope.poll.getTotalVote() > 0) {
                btns.push({
                    text: gettextCatalog.getString('View Member Votes'),
                    action: $scope.showPollVoters
                });
            }

            return btns;
        });

        $scope.onPollVote = function(option) {

            if ($scope.isProcessingPollVote) {
                return;
            }

            $scope.isProcessingPollVote = true;

            var oldAnswerId = $scope.poll.iAnswerId;
            $scope.poll.iAnswerId = option.iAnswerId;

            var sendData = {
                iPollId: $scope.poll.getId(),
                iAnswerId: option.iAnswerId
            };

            var revertAnswer = function() {

                $scope.poll.iAnswerId = oldAnswerId;
                $scope.$$phase || $scope.$apply();
            };

            var successCb = function(data) {
                
                if (data.error_code) {$scope.poll.iAnswerId = oldAnswerId;
                    revertAnswer();
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $modal.toast(data.message || gettextCatalog.getString('Voted successfully.'));
                $.extend($scope.poll, data);
                $scope.pollView = 'results';

                $scope.loadPollVoters();
            };

            var errorCb = function() {

                console.warn('poll/vote', arguments);
                revertAnswer();
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post('poll/vote', sendData).success(successCb).error(errorCb).
            finally(function() {
                $scope.isProcessingPollVote = false;
            });
        };

        $scope.loadPollVoters = function() {

            $scope.isLoadingPollVoters = true;

            var sendData = {
                iPollId: $scope.poll.getId()
            };

            var successCb = function(data) {
                
                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $scope.poll.voters = data || [];
            };

            var errorCb = function() {

                console.warn('poll/voter', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post('poll/voter', sendData).success(successCb).error(errorCb).finally(function() {
                $scope.isLoadingPollVoters = false;
            });
        };

        $scope.showPollVoters = function() {

            $scope.pollVotersModal || ($scope.pollVotersModal = $ionicModal.fromTemplate(require('text!tpl/forum/forum-poll-voters.html'), {
                scope: $scope
            }));

            $scope.pollVotersModal.show();
        };

        $scope.hidePollVoters = function() {

            $scope.pollVotersModal && $scope.pollVotersModal.hide();
        };

        $scope.removePollVoters = function() {

            $scope.pollVotersModal && $scope.pollVotersModal.remove();
        };

        $scope.$on('$destroy', function() {

            $scope.removePollVoters();
        });

        $scope.getItem();
    };
});