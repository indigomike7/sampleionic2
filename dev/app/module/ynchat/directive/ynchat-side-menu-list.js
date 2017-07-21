define([
    'text!tpl/ynchat/ynchat-side-menu-list.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            template: require('text!tpl/ynchat/ynchat-side-menu-list.html'),
            controller: 'YnchatSideMenuListCtrl'
        };
    };
});