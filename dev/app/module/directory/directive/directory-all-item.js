define([
    'text!tpl/directory/directory-all-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryAllItemCtrl'
        };
    };
});
