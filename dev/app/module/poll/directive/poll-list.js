define([
    'text!tpl/poll/poll-list.html',
    'poll/controller/poll-list',
], function(text, Controller) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});