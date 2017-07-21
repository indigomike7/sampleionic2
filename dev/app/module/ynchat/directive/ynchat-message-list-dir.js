define([
	'text!tpl/ynchat/ynchat-message-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/ynchat/ynchat-message-list-dir.html'),
			controller: 'YnchatMessageListCtrl'
		};
	};
});