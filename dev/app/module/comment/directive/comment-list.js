define([
    'text!tpl/comment/comment-list.html',
    'comment/controller/comment-list',
], function(text, Ctrl) {
    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Ctrl,
            scope: {
                obj: '=',
                inModal: '@'
            }
        };
    };
});