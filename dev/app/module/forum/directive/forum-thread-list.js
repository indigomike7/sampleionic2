define([
	'text!tpl/forum/forum-thread-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: 'ForumThreadListCtrl'
		};
	};
});