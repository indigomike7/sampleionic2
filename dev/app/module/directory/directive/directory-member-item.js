define([
    'text!tpl/directory/directory-member-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text
        };
    };
});
