define([
    'text!tpl/quiz/quiz-search.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text
        };
    };
});