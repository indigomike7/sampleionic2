define([
    'text!tpl/activity/activity-list.html',
    'activity/controller/activity-list-dir'
], function(text, Controller) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Controller
		};
	};
});