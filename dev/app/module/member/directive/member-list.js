define([
	'member/controller/member-list',
	'text!tpl/member/member-list.html'
], function(Controller, text) {
	return function() {
		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});