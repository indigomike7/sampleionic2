define([
    'text!tpl/global/ptr-pull-to-refresh.html',
], function(template) {
    return function() {
        return {
            restrict: 'C',
            template: template
        };
    };
});