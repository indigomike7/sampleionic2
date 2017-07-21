define([
	'text!tpl/forum/forum-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			controller: 'ForumListCtrl',
			template: text,
			scope: {
				data: '=',
				obj: '=',
				onItemClick: '='
			}
		};
	};
});