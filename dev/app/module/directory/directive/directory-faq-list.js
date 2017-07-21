define([
    'text!tpl/directory/directory-faq-list.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryFaqListCtrl'
        };
    };
});
