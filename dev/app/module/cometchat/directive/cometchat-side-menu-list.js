define([
    'text!tpl/cometchat/cometchat-side-menu-list.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            template: require('text!tpl/cometchat/cometchat-side-menu-list.html'),
            controller: 'CometchatSideMenuListCtrl'
        };
    };
});