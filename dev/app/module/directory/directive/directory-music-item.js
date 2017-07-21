define([
    'text!tpl/directory/directory-music-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text
        };
    };
});
