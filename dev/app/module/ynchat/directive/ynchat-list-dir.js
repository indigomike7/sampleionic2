define([
	'text!tpl/ynchat/ynchat-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/ynchat/ynchat-list-dir.html'),
			controller: 'YnchatListCtrl'
		};
	};
});