define([
    'text!tpl/directory/directory-follower-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text
        };
    };
});
