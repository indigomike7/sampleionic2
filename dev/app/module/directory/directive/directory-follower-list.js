define([
    'text!tpl/directory/directory-follower-list.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryFollowerListCtrl'
        };
    };
});
