define([
	'subscription/directive/subscription-list-dir'
], function() {

	angular.module('myapp.directives')
		.directive('subscriptionListDir', require('subscription/directive/subscription-list-dir'));
});