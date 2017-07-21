define([
    'text!tpl/directory/directory-all-list.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryListCtrl'
        };
    };
});