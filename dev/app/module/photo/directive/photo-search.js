define([
	'photo/controller/photo-search',
	'text!tpl/photo/photo-search.html'
], function(Controller, text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});