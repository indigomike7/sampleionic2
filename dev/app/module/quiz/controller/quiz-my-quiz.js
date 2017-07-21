define([
    'global/base/BrowseController',
    'text!tpl/quiz/quiz-search.html'
], function(Ctrl, searchTemplate) {
    return function($scope, $injector, gettext, gettextCatalog, $site) {
        /**
         * check require permission
         */
        $site.requirePerm('quiz.can_access_quiz');
        $scope.canCreate =  $site.getPerm('quiz.can_create_quiz');

        /**
         * extend list controller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        /**
         * get search template
         */
        $scope.searchTemplate = searchTemplate;
        
        /**
         * search query data.
         * this options is required for manage quiz-list-controllers
         */
        $scope.searchQuizzes = {
            iPage: 1,
            iAmountOfQuiz: 10,
            sSearch: '',
            sView: 'my',
            sOrder: 'recent',
            fields: 'id,title,desc,stats,user,canEdit,canVote'
        };
        
        return $scope;
    };
});