define([
	'text!tpl/ynchat/ynchat-search-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/ynchat/ynchat-search-dir.html')
		};
	};
});