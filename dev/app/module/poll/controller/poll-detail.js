define([
    'poll/controller/poll-detail-ipad',
    'poll/model/poll',
    'poll/controller/poll-item',
    'text!tpl/poll/poll-results-modal.html',
    'moment'
], function(PollDetailIpadCtrl, Model, Ctrl, PollResultsModal, Moment) {
    return function($scope, $site, $injector, $state, $location, $http2, gettext, gettextCatalog, $q, $modal, $coreSettings, $dislike, $ionicModal) {

        /**
         * check permission
         */
        // $site.requirePerm('poll.view');

        var iPollId = $state.params.iPollId;

        /**
         * extend item controller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.Moment = Moment;

        /**
         * init data
         */
        //$scope.dataReady = false;

        $scope.item = $.extend({
            iPollId: iPollId,
            aVoters: []
        }, Model);

        $scope.bShowResult = false;

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure that you want to delete this poll?'),
            'poll/delete',
            function() {
                return {
                    iPollId: $scope.item.getId()
                };
            }, function(data) {

                // if iPad, broadcast delete event to remove current item in poll list, and update previous poll
                if (ionic.Platform.isIPad()){
                    $scope.$broadcast('poll:itemDeleted', {
                        iPollId: $scope.item.getId()
                    });
                } else {
                    $scope.goBack();
                }
            });

        /**
         * when user check an options
         * @param {iAnswerId, sAnswer, ... } option
         */
        $scope.onCheckOption = function(option) {

            if ($scope.isProcessing) {
                return;
            }

            $scope.isProcessing = true;

            var postData = {
                iPollId: $scope.item.getId(),
                iAnswerId: option.iAnswerId
            };

            $http2.post('poll/vote', postData)
                .success(function(data) {
                    if (data.error_code) {
                        $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                        $scope.loadInit();
                        return;
                    }else{
                        $.extend($scope.item, data);
                        $modal.toast(gettextCatalog.getString('Voted successfully.'));

                        // update voters list
                        $scope.getVoters();

                        // show result
                        $scope.bShowResult = true;
                    }
                }).error($http2.defaultErrorHandler)
                .finally(function() {
                    $scope.isProcessing = false;
                });
        };

        $scope.loadInit = function() {
            if (typeof($scope.item.getId) == 'undefined') {
                return;
            }

            var sendData = {
                iPollId: $scope.item.getId()
            };

            $scope.isLoading = true;

            $http2.get('poll/detail', sendData).success(function(data) {
                if (data.error_code) {
                    $scope.isLoading = false;
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }

                $.extend($scope.item, data);

                // show result if user voted
                if ($scope.item.isVoted()) {
                    $scope.bShowResult = true;
                }

                $scope.getVoters();

            }).error(function() {
                console.warn('poll/detail', arguments);
                $scope.isLoading = false;
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.goBack();
            });
        };

        // get voter list to use in user stripe and user result modal
        $scope.getVoters = function(){

            var sendData = {
                iPollId: $scope.item.getId()
            };

            $http2.post('poll/voter', sendData).success(function(data) {
                if (data.error_code) {
                }
                $scope.item.aVoters = data;
                $scope.dataReady = true;
            }).error(function() {}).finally(function() {
                $scope.isLoading = false;
            });
        };

        // show result modal
        $scope.showResults = function() {

            if (!$scope.item.canViewUsersResults()) {
                return false;
            }

            if (!$scope.pollResultsModal) {
                $scope.pollResultsModal = $ionicModal.fromTemplate(PollResultsModal, {
                    scope : $scope
                });
            }
            $scope.pollResultsModal.show();
        };

        $scope.hideResults = function() {
            $scope.pollResultsModal.hide();
        };

        $scope.onItemSetting = $scope._setting($scope, function(){

            var btns  = [];

            if ($coreSettings.get('like_allow_dislike')) {
                if ($scope.item.getTotalDislike() > 0) {
                    btns.push({
                        text: $dislike.getDislikeStat($scope.item),
                        action: $scope.onViewDisliked
                    });
                }
                if ($scope.item.canDislike()) {
                    btns.push({
                        text: $scope.item.isDisliked() ? gettextCatalog.getString('Remove Dislike') : gettextCatalog.getString('Dislike'),
                        action: $scope.onItemDislike
                    });
                }
            }

            // only allow sharing if the poll is approved
            if($scope.item.isApproved()){
                btns.push({
                    text: gettextCatalog.getString('Share'),
                    action: $scope.onItemShare
                });
            }

            if(!$scope.item.isOwner()){
                btns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport
                });
            }

            if($scope.item.canEdit()) {
                btns.push({
                    text: gettextCatalog.getString('Edit Poll'),
                    action: $scope.onItemEdit
                });
            }

            if($scope.item.canDelete()) {
                btns.push({
                    text: gettextCatalog.getString('Delete Poll'),
                    destructive: true,
                    action: $scope.onItemDelete
                });
            }

            return btns;
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(PollDetailIpadCtrl, this, {
                $scope: $scope
            });
        } else {
            $scope.loadInit();
        }

        $scope.$on('$destroy', function(){

            $scope.pollResultsModal && $scope.pollResultsModal.remove();
        });

        return $scope;
    };
});