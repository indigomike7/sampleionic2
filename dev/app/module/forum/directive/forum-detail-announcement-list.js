define([
    'text!tpl/forum/forum-detail-announcement-list.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text
        };
    };
});