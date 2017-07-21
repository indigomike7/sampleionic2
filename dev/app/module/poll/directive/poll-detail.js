define([
    'text!tpl/poll/poll-detail.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'PollDetailCtrl',
        };
    };
});