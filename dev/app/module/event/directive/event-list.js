define([
	'text!tpl/event/event-list.html',
	'event/controller/event-list',
], function(text, Controller) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Controller,
		};
	};
});