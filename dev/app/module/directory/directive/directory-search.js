define([
    'text!tpl/directory/directory-search.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectorySearchCtrl'
        };
    };
});