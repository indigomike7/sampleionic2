define([
	'cometchat/service/cometchat'
], function() {

	angular.module('myapp.services')
		.service('$cometchat', require('cometchat/service/cometchat'));
});