define([
    'text!tpl/quiz/quiz-detail.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'QuizDetailCtrl',
        };
    };
});