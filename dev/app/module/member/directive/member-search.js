define([
	'member/controller/member-search',
	'text!tpl/member/member-search.html'
], function(Controller, text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});