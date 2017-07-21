define([
    'text!tpl/core/core-rate.html',
    'text!tpl/core/core-report.html',
    'text!tpl/dislike/disliked-list.html',
    'text!tpl/share/share-friend-wall.html',
    'text!tpl/share/share-wall.html',
    'text!tpl/comment/comment-popup.html',
], function(RateTpl, ReportTpl, dislikedListTpl) {

    return function($scope, $ionicPopup, $ionicModal, $ionicActionSheet, gettext, gettextCatalog, $site, $modal, $ionicSideMenuDelegate, $ionicNavBarDelegate, $http2, $location, $history, $viewer, $dislike, $ionicSideMenuDelegate, $timeout, $state) {

        /**
         * current location
         */
        $scope.currentPath = $location.path();

        /**
         * current state
         */
        $scope.currentState = $state.current;

        /**
         * toggle left side menu
         */
        $scope.toggleLeftSideMenu = function() {
            $ionicSideMenuDelegate.toggleLeft();
        };

        /**
         * toggle right side menu
         */
        $scope.toggleRightSideMenu = function() {
            $ionicSideMenuDelegate.toggleRight();
        };

        /**
         * go history back
         */
        $scope.goBack = function() {
            $history.back();
        };

        /**
         * go to page
         * @param string path
         */
        $scope.goToPage = function(path) {
            if (ionic.Platform.isIPad()) {
                var handle = $ionicSideMenuDelegate.$getByHandle('side-menus-left');
                if (handle) handle.toggleLeft(false);

                $timeout(function() {
                    $location.url(path, true);
                }, 200);
            } else {
                $location.url(path, true);
            }
        };

        /**
         * go to home
         */
        $scope.goHome = function() {
            $location.path($site.home);
        };

        /**
         * get private page title
         */
        $scope.getPrevPageTitle = function() {
            var prev = $history.getPrev();
            return gettext(prev.title) || '';
        };

        $scope._itemConfirm = function(title, api, post_data, cb){
            return function(){
                var send_data = (typeof post_data == 'function')?post_data(): post_data;
                $modal.confirm (title, function(result){
                    if(result == 1) return ;

                    if($scope.item.isProccessing){
                        return ;
                    }
                    $http2.post(api, send_data)
                        .success(function(data){
                            if(data.error_code)
                            {
                                $modal.alert(data.error_message);
                            }else{
                                if(data.message){
                                    $modal.toast(data.message);
                                }
                                cb(data);
                            }
                        })
                        .error(function(){

                        })
                        .finally(function(){
                            $scope.item.isProccessing = false;
                        });
                }, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('Cancel'), gettextCatalog.getString('OK')]);
            };
        };

        $scope._setting = function($scope, setSettingBtns) {
            return function() {
                var btns = setSettingBtns();

                var options = {
                    buttons: btns,
                    cancelText: gettextCatalog.getString('Cancel'),
                    buttonClicked: function(index) {
                        btns[index].action();
                        return true;
                    }
                };

                if (btns.length) {

                    var destructive = 0;

                    for (var i = 0; i < btns.length; ++i) {

                        if(btns[i].text){
                            btns[i].text = gettext(btns[i].text);
                        }

                        if (typeof btns[i].destructive != 'undefined' && btns[i].destructive) {
                            destructive = btns[i];
                            btns.splice(i, 1);
                        };
                    }


                    if (destructive) {
                        options.destructiveText = destructive.text,
                            options.destructiveButtonClicked = function() {
                                destructive.action();
                                return true;
                            };
                    };
                }

                $scope.hideSheet = $ionicActionSheet.show(options);
            };
        };

        $scope._commentPopup = function($scope, getItem){
            return function(){
                $scope.commentObj = getItem();

                $scope.commentModal && $scope.commentModal.remove();
                $scope.$on('$destroy', function() {
                    $scope.commentModal && $scope.commentModal.remove();
                });

                $scope.commentModal = $ionicModal.fromTemplate(require('text!tpl/comment/comment-popup.html'), {
                    scope: $scope
                });

                $scope.hideCommentBox = function(){
                    $scope.commentModal.remove();
                };

                $scope.commentModal.show();
            };
        };

        $scope.showShareOnFriendWallModal = function() {
            $scope.shareOnFriendWallModal && $scope.shareOnFriendWallModal.remove();
            
            $scope.shareOnFriendWallModal = $ionicModal.fromTemplate(require('text!tpl/share/share-friend-wall.html'), {
                scope: $scope
            });

            $scope.shareOnFriendWallModal.show();

            $scope.$on('$destroy', function() {
                $scope.shareOnFriendWallModal && $scope.shareOnFriendWallModal.remove();
            });
        };

        $scope.hideShareOnFriendWallModal = function() {
            $scope.shareOnFriendWallModal && $scope.shareOnFriendWallModal.hide();
        };

        $scope._share = function($scope, getItem) {

            var item;

            function doShareWall() {

                if (!$scope.shareData.sContent) {
                    $modal.alert(gettextCatalog.getString('Please write something to share'));
                    return;
                }

                $scope.sharePopup.close();

                $http2.post('feed/share', $scope.shareData)
                    .success(doShareWallSuccess)
                    .error(doShareWallError);
            };

            function doShareWallSuccess(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || 'Can not load data from server');
                }

                if (data.message) {
                    $modal.toast(data.message);
                    item.iTotalShare += 1;
                }
            };

            function doShareWallError() {
                console.warn('doShareWallError', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            function shareOnWall() {
                $scope.sharePopup && $scope.sharePopup.close();
                $scope.sharePopup = $ionicPopup.show({
                    template: require('text!tpl/share/share-wall.html'),
                    title: gettextCatalog.getString('Share on your wall'),
                    scope: $scope,
                    buttons: [{
                        text: gettextCatalog.getString('Close'),
                        onTap: function(e) {
                            $scope.shareData = {};
                        }
                    }, {
                        text: $scope.isProcessingShareWall ? gettextCatalog.getString('Sharing...') : gettextCatalog.getString('Share'),
                        type: 'button-positive',
                        onTap: function(e) {
                            e.preventDefault();
                            doShareWall(e);
                        }
                    }, ]
                });
            };

            function shareOnSocial() {

                $site.debug >2 && console.log('try to share: ',item.getSocialShareUrl());

                if (typeof window.plugins == 'undefined') {
                    // social sharing is not loaded or this app is runned on browser mode.
                    return;
                }

                window.plugins.socialsharing.share(null, null, null, encodeURI(item.getSocialShareUrl()));
            };

            return function() {

                item = getItem();

                $scope.shareData = {
                    iItemId: item.getParentModuleId() ? item.getItemId() : item.getId(),
                    sItemType: item.getParentModuleId() ? item.getItemType() : item.getType(),
                    sContent: ''
                };

                var buttons = [];

                buttons.push({
                    text: gettextCatalog.getString('Share on your wall'),
                    action: shareOnWall,
                });

                buttons.push({
                    text: gettextCatalog.getString('Share on a friend\'s wall'),
                    action: $scope.showShareOnFriendWallModal,
                });

                // hide this feature for version 4.01
                //if(item.getSocialShareUrl()){
                //    buttons.push({
                //        text: gettextCatalog.getString('Share via social'),
                //        action: shareOnSocial,
                //    });
                //}

                $ionicActionSheet.show({
                    buttons: buttons,
                    cancelText: gettextCatalog.getString('Cancel'),
                    buttonClicked: function(index) {
                        buttons[index].action();
                        return true;
                    }
                });
            };
        };


        $scope._report = function($scope, getItem) {

            var item;

            function doReport() {
                if (!$scope.reportData.sDescription) {
                    $modal.alert(gettextCatalog.getString('Description can not be empty'));
                    return;
                }

                $scope.reportPopup.close();

                $http2.post('report/add', $scope.reportData)
                    .success(doReportSuccess)
                    .error(doReportError)
                    .
                    finally(function() {
                        $scope.isProcessingReport = false;
                        e.currentTarget.innerHTML = 'Report';
                    });
            };

            function doReportSuccess(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message);
                }

                if (data.message) {
                    $modal.toast(data.message);
                }
                $scope.reportData = {};
            };

            function doReportError() {

                console.warn('doReportError', arguments);

                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            function getReportForm(sItemType) {

                var successCb = function(data) {

                    if (data.error_code) {
                        $scope.reportPopup && $scope.reportPopup.close();
                        return $modal.alert(data.error_message || 'Can not load data from server');
                    }

                    $scope.reportForm = data;
                    $scope.reportData.iReport = data[0].iReportId;
                };

                var errorCb = function() {

                    console.warn('getReportForm', arguments);
                    $scope.reportPopup && $scope.reportPopup.close();
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                };

                var reqData = {
                    sItemType: sItemType
                };

                $http2.post('report/reason', reqData).success(successCb).error(errorCb);
            };

            return function() {
                item = getItem();

                $scope.reportData = {
                    iItemId: item.getId(),
                    sItemType: item.getType()
                };

                $scope.reportPopup && $scope.reportPopup.close();
                $scope.reportPopup = $ionicPopup.show({
                    template: ReportTpl,
                    title: gettextCatalog.getString('Report'),
                    scope: $scope,
                    buttons: [{
                        text: gettextCatalog.getString('Cancel'),
                        onTap: function(e) {
                            $scope.reportData = {};
                        }
                    }, {
                        text: gettextCatalog.getString('Report'),
                        type: 'button-positive',
                        onTap: function(e) {
                            e.preventDefault();
                            doReport();
                        }
                    }, ]
                });

                getReportForm(item.getType());
            };
        };

        $scope._rate = function($scope, getRateData) {

            function doRate(e) {

                if ($scope.isProcessingRate || !$scope.rateData.iRating) {
                    return;
                }

                $scope.isProcessingRate = true;
                $(e.currentTarget).parent().children().addClass('disabled');

                $http2.post('core/rate', $scope.rateData)
                    .success(doRateSuccess)
                    .error(doRateError).
                    finally(function() {
                        $scope.isProcessingRate = false;
                        $(e.currentTarget).parent().children().removeClass('disabled');
                    });
            }

            function doRateSuccess(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || 'Can not load data from server');
                }

                if (data.message) {
                    $modal.toast(data.message);
                }

                $scope.item.fRating = data.fRating;
                $scope.item.bIsRating = true;

                $scope.rateData = {};
                $scope.ratePopup.close();
            };

            function doRateError() {

                console.error('doRate', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            return function() {

                if ($scope.item.isRated()) {
                    return $modal.alert(gettextCatalog.getString('You already rated'));
                }

                $scope.rateData = getRateData();

                $scope.ratePopup = $ionicPopup.show({
                    template: RateTpl,
                    title: gettextCatalog.getString('Rate'),
                    scope: $scope,
                    buttons: [{
                        text: gettextCatalog.getString('Cancel'),
                        onTap: function(e) {
                            if (!$scope.isProcessingRate) {
                                $scope.rateData = {};
                            }
                        }
                    }, {
                        text: '<b>' + gettextCatalog.getString('Rate') + '</b>',
                        type: 'button-positive',
                        onTap: function(e) {
                            e.preventDefault();
                            doRate(e);
                        }
                    }]
                });
            };
        };

        $scope._like = function($scope, getItem, reactionId) {

            var item;
            var oldData;

            function setLikeData() {
                item.bIsLiked = !item.bIsLiked;
                item.iTotalLike = parseInt(item.iTotalLike) + (item.bIsLiked ? 1 : -1);

                if (item.bIsDisliked) {
                    item.bIsDisliked = false;
                    item.iTotalDislike = parseInt(item.iTotalDislike) -1;
                }
            }

            function revertLikeData() {
                $.extend(item, {
                    bIsLiked: oldData.bIsLiked,
                    iTotalLike: oldData.iTotalLike,
                    bIsDisliked: oldData.bIsDisliked,
                    iTotalDislike: oldData.iTotalDislike
                });
            }

            function doLikeSuccess(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || 'Can not load data from server');
                    revertLikeData();
                }else{
                    if (data.message) {
                        $modal.toast(data.message);
                    }

                    $.extend(item, {
                        bIsLiked: data.bIsLiked,
                        iTotalLike: data.iTotalLike,
                        aLikes: data.aLikes,
                        aReactionEmoticons: data.aReactionEmoticons,
                        bIsDisliked: data.bIsDisliked,
                        iTotalDislike: data.iTotalDislike,
                        aDislikes: data.aDislikes
                    });
                }
            }

            function doLikeError() {
                console.warn('likeError', arguments);
                revertLikeData();
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            return function() {

                item = getItem();
                oldData = {
                    bIsLiked: item.bIsLiked,
                    iTotalLike: item.iTotalLike,
                    bIsDisliked: item.bIsDisliked,
                    iTotalDislike: item.iTotalDislike
                };

                if (item.isProcessingLike) {
                    return;
                }

                if (!item.canLike()) {
                    return $modal.alert(gettextCatalog.getString('You don\'t have permission to like this item'));
                }

                item.isProcessingLike = true;

                setLikeData();

                var api = item.bIsLiked ? 'like/like' : 'like/unlike';
                var likeData = {
                    iItemId: item.getId(),
                    sItemType: item.getType(),
                    sParentId: item.getParentModuleId(),
                // custom work
                    eid: reactionId
                //
                };

                $http2.post(api, likeData)
                    .success(doLikeSuccess)
                    .error(doLikeError)
                    .
                    finally(function() {
                        item.isProcessingLike = false;
                    });
            };

        };

        $scope._dislike = function($scope, getItem) {

            var item;
            var oldData;

            function setLikeData() {
                item.bIsDisliked = !item.bIsDisliked;
                item.iTotalDislike = parseInt(item.iTotalDislike) + (item.bIsDisliked ? 1 : -1);

                if (item.bIsLiked) {
                    item.bIsLiked = false;
                    item.iTotalLike = parseInt(item.iTotalLike) -1;
                }
            }

            function revertLikeData() {
                $.extend(item, {
                    bIsLiked: oldData.bIsLiked,
                    iTotalLike: oldData.iTotalLike,
                    bIsDisliked: oldData.bIsDisliked,
                    iTotalDislike: oldData.iTotalDislike
                });
            }

            function doDislikeSuccess(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || 'Can not load data from server');
                    revertLikeData();
                }else{
                    if (data.message) {
                        $modal.toast(data.message);
                    }

                    $.extend(item, {
                        bIsLiked: data.bIsLiked,
                        iTotalLike: data.iTotalLike,
                        aLikes: data.aLikes,
                        bIsDisliked: data.bIsDisliked,
                        iTotalDislike: data.iTotalDislike,
                        aDislikes: data.aDislikes
                    });
                }
            }

            function doDislikeError() {
                console.warn('dislikeError', arguments);
                revertLikeData();
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            return function() {

                item = getItem();
                oldData = {
                    bIsLiked: item.bIsLiked,
                    iTotalLike: item.iTotalLike,
                    bIsDisliked: item.bIsDisliked,
                    iTotalDislike: item.iTotalDislike
                };

                if (item.isProcessingDislike) {
                    return;
                }

                if (!item.canDislike()) {
                    return $modal.alert(gettextCatalog.getString('You don\'t have permission to dislike this item'));
                }

                item.isProcessingDislike = true;

                setLikeData();

                var api = item.bIsDisliked ? 'like/dislike' : 'like/removedislike';

                var dislikeData = {
                    iItemId: item.getId(),
                    sItemType: item.getType(),
                    sParentId: item.getParentModuleId()
                };

                $http2.post(api, dislikeData)
                    .success(doDislikeSuccess)
                    .error(doDislikeError)
                    .
                    finally(function() {
                        item.isProcessingDislike = false;
                    });
            };
        };

        $scope._viewDisliked = function($scope, getItem) {

            $scope.$on('$destroy',function(){
                $scope.dislikedModal && $scope.dislikedModal.remove();
            });

            $scope.showDislikedList = function() {

                $scope.dislikedModal && $scope.dislikedModal.remove();

                $scope.dislikedModal = $ionicModal.fromTemplate(dislikedListTpl, {
                    scope: $scope
                });

                $scope.dislikedModal.show();
            };

            $scope.hideDislikedList = function() {

                $scope.dislikedModal.remove();
            };

            return function() {

                $scope.obj = getItem();

                if (!$scope.obj.getTotalDislike()) {
                    return false;
                }

                if ($scope.obj.getTotalDislike() == 1) {
                    var dislikedId = $scope.obj.isDisliked() ? $viewer.get('iUserId') : $dislike.getLastDisliked($scope.obj).getId();

                    $location.path('app/user/' + dislikedId);
                    return $scope.$$phase || $scope.$apply();
                }

                $scope.showDislikedList();
            };
        };

        $scope.toggleSearchBox = function(isSimple) {
            var ngBody = angular.element('body');
            if (ngBody.hasClass('search-open')) {
                ngBody.removeClass('search-open search-advanced');
            } else {
                ngBody.addClass('search-open');
                if (!isSimple) {
                    ngBody.addClass('search-advanced');
                }
            }
        };
    };
});