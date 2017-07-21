define([
	'text!tpl/forum/forum-thread-detail-poll-detail.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});