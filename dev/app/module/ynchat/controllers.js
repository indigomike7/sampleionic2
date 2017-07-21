define([
    'ynchat/controller/ynchat-base',
	'ynchat/controller/ynchat-detail',
    'ynchat/controller/ynchat-home',
    'ynchat/controller/ynchat-list',
    'ynchat/controller/ynchat-message-list',
    'ynchat/controller/ynchat-ocn',
    'ynchat/controller/ynchat-history',
    'ynchat/controller/ynchat-side-menu-list'
], function() {

    angular.module('myapp.controllers')
        .controller('YnchatBaseCtrl', require('ynchat/controller/ynchat-base'))
	    .controller('YnchatDetailCtrl', require('ynchat/controller/ynchat-detail'))
        .controller('YnchatHomeCtrl', require('ynchat/controller/ynchat-home'))
        .controller('YnchatListCtrl', require('ynchat/controller/ynchat-list'))
        .controller('YnchatMessageListCtrl', require('ynchat/controller/ynchat-message-list'))
        .controller('YnchatHistoryCtrl', require('ynchat/controller/ynchat-history'))
        .controller('YnchatOcnCtrl', require('ynchat/controller/ynchat-ocn'))
        .controller('YnchatSideMenuListCtrl', require('ynchat/controller/ynchat-side-menu-list'));
});