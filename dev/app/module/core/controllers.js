define([
    'core/controller/core-action-list',
    'core/controller/core-header-stat',
    'core/controller/core-init',
    'core/controller/core-menu',
    'core/controller/core-pagination',
    'core/controller/core-private-page',
    'core/controller/core-share-on-wall',
], function() {

    angular.module('myapp.controllers')
        .controller('AppCtrl', require('core/controller/core-menu'))
        .controller('CoreActionListCtrl', require('core/controller/core-action-list'))
        .controller('CoreHeaderStatCtrl', require('core/controller/core-header-stat'))
        .controller('CoreInitCtrl', require('core/controller/core-init'))
        .controller('CorePaginationCtrl', require('core/controller/core-pagination'))
        .controller('CorePrivatePageCtrl',require('core/controller/core-private-page'))
        .controller('CoreShareOnWallCtrl',require('core/controller/core-share-on-wall'))
        ;
});