define([
	'text!tpl/subscription/subscription-list-dir.html'
], function(subscriptionListDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: subscriptionListDirTpl,
			controller: 'SubscriptionListCtrl'
		};
	};
});