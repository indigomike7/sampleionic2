define([
	'text!tpl/forum/forum-post-search-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: 'ForumPostSearchListCtrl'
		};
	};
});