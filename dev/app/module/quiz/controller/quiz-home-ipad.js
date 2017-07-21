define([
    'global/base/BrowseController',
    'quiz/model/quiz',
    'text!tpl/quiz/quiz-search.html'
], function(BrowseController, QuizModel, searchTemplate) {

    return function($scope, $injector, $ionicTabsDelegate, $state, $site) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.initQuery = function() {

            $scope.searchQuizzes  =  {
                iPage: 1,
                iAmountOfQuiz: 10,
                sSearch: '',
                sView: 'all',
                sOrder: 'latest'
            };
        };

        // click on 'All' and 'My' quizzes
        $scope.onClickTab = function(tabIndex) {
            $scope.showAdvSearch = false;
            angular.element('body').removeClass('search-open search-advanced');
            var sView = (tabIndex == 1) ? 'my' : 'all';
            if (sView != $scope.searchQuizzes.sView) {
                $ionicTabsDelegate.select(tabIndex);

                $scope.initQuery();
                $scope.searchQuizzes.sView = sView;
                $scope.$broadcast('quiz:viewChange', $scope.searchQuizzes.sView);
            }
        };

        $scope.onSubmitSearch = function() {

            $scope.$broadcast('quiz:queryChange', $scope.searchQuizzes);
        };

        // set object to be shown in detail pane
        $scope.setActiveObj = function(obj) {

            $scope.activeObj = obj;

            $scope.$broadcast('quiz:objChange', $scope.activeObj);
        };

        // when click on an item in quiz list
        $scope.onItemClick = function(item) {
            // check if clicked item is not activated
            if (!$scope.activeObj || $scope.activeObj.getId() != item.getId()) {
                $scope.setActiveObj(item);
            }
        };

        // navigate from another page to a specific quiz, no set active quiz automatically
        if ($state.params.iQuizId) {
            var item = $.extend({}, QuizModel, {
                iQuizId: $state.params.iQuizId
            });

            $scope.setActiveObj(item);
        }

        $scope.initQuery();
    };
});