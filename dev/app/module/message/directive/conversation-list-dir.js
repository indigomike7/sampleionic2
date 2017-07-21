define([
	'message/controller/conversation-list',
	'text!tpl/message/conversation-list-dir.html'
], function(ConversationListCtrl, ConversationListDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: ConversationListDirTpl,
			controller: ConversationListCtrl
		};
	};
});