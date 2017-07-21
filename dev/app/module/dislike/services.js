define([
	'dislike/service/dislike'
], function() {

	angular.module('myapp.services')
		.service('$dislike', require('dislike/service/dislike'));
});