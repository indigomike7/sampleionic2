define([
	'cometchat/controller/cometchat-detail',
    'cometchat/controller/cometchat-home',
    'cometchat/controller/cometchat-list',
    'cometchat/controller/cometchat-message-list',
    'cometchat/controller/cometchat-ocn',
    'cometchat/controller/cometchat-side-menu-list'
], function() {

    angular.module('myapp.controllers')
	    .controller('CometchatDetailCtrl', require('cometchat/controller/cometchat-detail'))
        .controller('CometchatHomeCtrl', require('cometchat/controller/cometchat-home'))
        .controller('CometchatListCtrl', require('cometchat/controller/cometchat-list'))
        .controller('CometchatMessageListCtrl', require('cometchat/controller/cometchat-message-list'))
        .controller('CometchatOcnCtrl', require('cometchat/controller/cometchat-ocn'))
        .controller('CometchatSideMenuListCtrl', require('cometchat/controller/cometchat-side-menu-list'));
});