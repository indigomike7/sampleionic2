define([
	'text!tpl/forum/forum-detail-subforum-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});