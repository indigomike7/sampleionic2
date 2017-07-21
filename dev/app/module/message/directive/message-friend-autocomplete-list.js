define([
    'message/controller/message-friend-autocomplete-list',
    'text!tpl/message/message-friend-autocomplete-list.html'
], function(Controller, text) {
    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});