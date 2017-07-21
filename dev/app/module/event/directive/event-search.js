define([
	'text!tpl/event/event-search.html'
], function(eventSearchDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: eventSearchDirTpl,
			controller: 'EventSearchCtrl'
		};
	};
});