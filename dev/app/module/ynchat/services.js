define([
	'ynchat/service/ynchat',
	'ynchat/service/ynchat-websocket'
], function() {

	angular.module('myapp.services')
		.service('$ynchat', require('ynchat/service/ynchat'))
		.service('$ynchatWebsocket', require('ynchat/service/ynchat-websocket'));
});