define([
    'text!tpl/directory/directory-faq-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text
        };
    };
});
