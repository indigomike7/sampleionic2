define([
    'text!tpl/forum/forum-search.html'
], function(text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: 'ForumSearchCtrl'
        };
    };
});