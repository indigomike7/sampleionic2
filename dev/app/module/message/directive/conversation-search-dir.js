define([
	'text!tpl/message/conversation-search-dir.html'
], function(conversationSearchDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: conversationSearchDirTpl
		};
	};
});