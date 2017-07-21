define([
	'text!tpl/forum/forum-detail-thread-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});