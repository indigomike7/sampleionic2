define([
	'text!tpl/forum/forum-thread-detail-post-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});