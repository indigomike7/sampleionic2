define([
	'chat/service/chat',
], function() {

	angular.module('myapp.services')
		.service('$chat', require('chat/service/chat'));
});