define([
    'text!tpl/poll/poll-search.html'
], function(text) {

    return function() {
        return {
            restrict: 'E',
            template: text
        };
    };
});