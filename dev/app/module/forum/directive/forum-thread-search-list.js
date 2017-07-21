define([
	'text!tpl/forum/forum-thread-search-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: 'ForumThreadSearchListCtrl'
		};
	};
});