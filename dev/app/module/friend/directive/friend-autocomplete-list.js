define([
	'friend/controller/friend-autocomplete-list',
	'text!tpl/friend/friend-autocomplete-list.html'
], function(Controller, text) {
	return function() {
		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});