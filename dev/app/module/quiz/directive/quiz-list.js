define([
    'text!tpl/quiz/quiz-list.html',
    'quiz/controller/quiz-list',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});