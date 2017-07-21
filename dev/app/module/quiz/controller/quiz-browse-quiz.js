define([
    'global/base/BrowseController',
    'text!tpl/quiz/quiz-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, gettext, gettextCatalog, $site) {
        /**
         * check require permission
         */
        $site.requirePerm('quiz.can_access_quiz');
        $scope.canCreate =  $site.getPerm('quiz.can_create_quiz');

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        
        $scope.searchTemplate = searchTemplate;
        
        $scope.searchQuizzes = {
            iPage: 1,
            iAmountOfQuiz: 10,
            sSearch: '',
            sView: 'all',
            sOrder: 'latest'
        };
        
        return $scope;
    };
});