define([
    'text!tpl/directory/directory-member-list.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryMemberListCtrl'
        };
    };
});
