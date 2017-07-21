define([
    'text!tpl/ynchat/ynchat-side-menu.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: require('text!tpl/ynchat/ynchat-side-menu.html'),
            controller: 'YnchatHomeCtrl'
        };
    };
});