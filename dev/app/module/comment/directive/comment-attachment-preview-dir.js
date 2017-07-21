define([
    'text!tpl/comment/comment-attachment-preview.html'
], function() {

    return function() {

        return {
            restrict: 'E',
            template: require('text!tpl/comment/comment-attachment-preview.html')
        };
    }
});