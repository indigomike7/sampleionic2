define([
	'friend/controller/friend-list',
	'text!tpl/friend/friend-list.html'
], function(Controller, text) {
	return function() {
		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});