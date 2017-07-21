define([
    'text!tpl/chat/chat-side-menu.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: require('text!tpl/chat/chat-side-menu.html'),
            controller: 'ChatHomeCtrl'
        };
    };
});