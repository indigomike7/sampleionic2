define([
    'text!tpl/directory/directory-my-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryMyItemCtrl'
        };
    };
});
