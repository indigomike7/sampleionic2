define([
    'ynchat/directive/ynchat-list-dir',
    'ynchat/directive/ynchat-message-item',
    'ynchat/directive/ynchat-message-list-dir',
    'ynchat/directive/ynchat-search-dir',
    'ynchat/directive/ynchat-ocn-dir',
    'ynchat/directive/ynchat-side-menu',
    'ynchat/directive/ynchat-side-menu-list'
], function() {

    angular.module('myapp.directives')
        .directive('ynchatListDir', require('ynchat/directive/ynchat-list-dir'))
        .directive('ynchatMessageItem', require('ynchat/directive/ynchat-message-item'))
        .directive('ynchatMessageListDir', require('ynchat/directive/ynchat-message-list-dir'))
        .directive('ynchatSearchDir', require('ynchat/directive/ynchat-search-dir'))
        .directive('ynchatOcnDir', require('ynchat/directive/ynchat-ocn-dir'))
        .directive('ynchatSideMenu', require('ynchat/directive/ynchat-side-menu'))
        .directive('ynchatSideMenuList', require('ynchat/directive/ynchat-side-menu-list'));
});