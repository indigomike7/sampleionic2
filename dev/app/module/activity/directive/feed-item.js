define([
    'text!tpl/activity/feed-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'C',
            template: text,
            controller: 'FeedItemController',
            link: function(scope, element, attributes) {
                scope.element = element;
            }
        };
    };
});