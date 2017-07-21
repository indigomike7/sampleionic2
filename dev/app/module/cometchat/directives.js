define([
    'cometchat/directive/cometchat-list-dir',
    'cometchat/directive/cometchat-message-list-dir',
    'cometchat/directive/cometchat-search-dir',
    'cometchat/directive/cometchat-ocn-dir',
    'cometchat/directive/cometchat-side-menu',
    'cometchat/directive/cometchat-side-menu-list'
], function() {

    angular.module('myapp.directives')
        .directive('cometchatListDir', require('cometchat/directive/cometchat-list-dir'))
        .directive('cometchatMessageListDir', require('cometchat/directive/cometchat-message-list-dir'))
        .directive('cometchatSearchDir', require('cometchat/directive/cometchat-search-dir'))
        .directive('cometchatOcnDir', require('cometchat/directive/cometchat-ocn-dir'))
        .directive('cometchatSideMenu', require('cometchat/directive/cometchat-side-menu'))
        .directive('cometchatSideMenuList', require('cometchat/directive/cometchat-side-menu-list'));
});