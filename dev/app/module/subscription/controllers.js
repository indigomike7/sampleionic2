define([
	'subscription/controller/subscription-browse',
	'subscription/controller/subscription-confirm',
	'subscription/controller/subscription-list',
	'subscription/controller/subscription-upgrade'
], function() {

	angular.module('myapp.controllers')
		.controller('SubscriptionBrowseCtrl', require('subscription/controller/subscription-browse'))
		.controller('SubscriptionConfirmCtrl', require('subscription/controller/subscription-confirm'))
		.controller('SubscriptionListCtrl', require('subscription/controller/subscription-list'))
		.controller('SubscriptionUpgradeCtrl', require('subscription/controller/subscription-upgrade'));
});