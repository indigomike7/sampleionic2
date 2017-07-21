define([
    'quiz/controller/quiz-detail-ipad',
    'quiz/model/quiz',
    'quiz/controller/quiz-item',
    'text!tpl/quiz/quiz-results-modal.html',
    'text!tpl/quiz/quiz-user-result-modal.html',
    'moment'
], function(QuizDetailIpadCtrl, Model, Ctrl, QuizResultsModal, QuizUserResultModal, Moment) {
    return function($scope, $site, $injector, $state, $location, $http2, gettext, gettextCatalog, $q, $modal, $coreSettings, $dislike, $ionicModal) {

        var iQuizId = $state.params.iQuizId;

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
        $scope.dataReady = false;

        $scope.item = $.extend({
            iQuizId: iQuizId,
            aTakers: []
        }, Model);

        $scope.bShowResult = false;

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure that you want to delete this quiz?'),
            'quiz/delete',
            function() {
                return {
                    iQuizId: $scope.item.getId()
                };
            }, function(data) {

                // if iPad, broadcast delete event to remove current item in quiz list, and update previous quiz
                if (ionic.Platform.isIPad()){
                    $scope.$broadcast('quiz:itemDeleted', {
                        iQuizId: $scope.item.getId()
                    });
                } else {
                    $scope.goBack();
                }
            });

        /**
         * when user check an options
         * @param {iAnswerId, sAnswer, ... } option
         */

        $scope.loadInit = function() {
            if (typeof($scope.item.getId) == 'undefined') {
                return;
            }

            var sendData = {
                iQuizId: $scope.item.getId()
            };

            $scope.isLoading = true;

            $http2.get('quiz/detail', sendData).success(function(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }

                $.extend($scope.item, data);

                // show result if user voted
                if ($scope.item.isVoted()) {
                    $scope.bShowResult = true;
                }

                $scope.dataReady = true;

            }).error(function() {
                console.warn('quiz/detail', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.goBack();
            }).finally(function() {
                $scope.isLoading = false;
            });
        };

        // show result modal
        $scope.showResults = function() {
            if (!$scope.quizResultsModal) {
                $scope.quizResultsModal = $ionicModal.fromTemplate(QuizResultsModal, {
                    scope : $scope
                });
            }
            $scope.quizResultsModal.show();
        };

        $scope.showUserResult = function(iTakerId) {

            $scope.iTakerId = iTakerId;
            $scope.quizUserResultModal = $ionicModal.fromTemplate(QuizUserResultModal, {
                scope : $scope
            });
            $scope.quizUserResultModal.show();
        };

        $scope.hideResults = function() {
            $scope.quizResultsModal.hide();
        };

        $scope.hideUserResult = function() {
            $scope.quizUserResultModal.hide();
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

            // only allow sharing if the quiz is approved
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
                    text: gettextCatalog.getString('Edit Quiz'),
                    action: $scope.onItemEdit
                });
            }

            if($scope.item.canDelete()) {
                btns.push({
                    text: gettextCatalog.getString('Delete Quiz'),
                    destructive: true,
                    action: $scope.onItemDelete
                });
            }

            return btns;
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(QuizDetailIpadCtrl, this, {
                $scope: $scope
            });
        } else {
            $scope.loadInit();
        }

        $scope.$on('$destroy', function(){

            $scope.quizResultsModal && $scope.quizResultsModal.remove();
            $scope.quizUserResultModal && $scope.quizUserResultModal.remove();
        });

        return $scope;
    };
});