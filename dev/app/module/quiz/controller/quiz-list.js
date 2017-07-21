define([
    'quiz/controller/quiz-list-ipad',
    'quiz/model/quiz',
    'global/base/ListController'
], function(QuizListIpadCtrl, Model, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog) {
        
        /**
         * extend base list controller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more quizzes.'),
            itemModel: Model,
            apiService: 'quiz/fetch',
            listById: false,
            getQueryData: function(){
                return $scope.$parent.searchQuizzes;
            }
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(QuizListIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});