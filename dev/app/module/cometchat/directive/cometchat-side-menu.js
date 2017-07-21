define([
    'text!tpl/cometchat/cometchat-side-menu.html'
], function() {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: require('text!tpl/cometchat/cometchat-side-menu.html'),
            controller: 'CometchatHomeCtrl'
        };
    };
});