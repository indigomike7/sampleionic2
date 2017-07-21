define([
	'text!tpl/comment/comment-form-subfooter.html',
	'comment/controller/comment-form',
], function(text, Controller) {
    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Controller,
            scope: {
                obj: '='
            }
        };
    };
});