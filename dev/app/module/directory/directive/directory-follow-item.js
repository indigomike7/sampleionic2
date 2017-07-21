define([
    'text!tpl/directory/directory-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryFollowItemCtrl'
        };
    };
});
