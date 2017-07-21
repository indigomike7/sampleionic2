define([
	'text!tpl/ynchat/ynchat-ocn-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/ynchat/ynchat-ocn-dir.html'),
			controller: 'YnchatOcnCtrl'
		};
	};
});