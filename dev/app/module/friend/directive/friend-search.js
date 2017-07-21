define([
	'friend/controller/friend-search',
	'text!tpl/friend/friend-search.html'
], function(Controller, text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});