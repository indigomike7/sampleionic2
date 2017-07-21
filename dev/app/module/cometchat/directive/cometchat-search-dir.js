define([
	'text!tpl/cometchat/cometchat-search-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/cometchat/cometchat-search-dir.html')
		};
	};
});