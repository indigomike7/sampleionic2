define([
    'text!tpl/directory/directory-claim-list.html'
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
