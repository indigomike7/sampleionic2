define([
	'search/controller/search-form',
	'text!tpl/search/search-form.html'
], function(Ctrl, text) {

	return function() {
		return {
			restrict: 'E',
			template: text,
			controller: Ctrl
		};
	};
});