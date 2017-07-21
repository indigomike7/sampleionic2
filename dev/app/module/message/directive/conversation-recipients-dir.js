define([
	'text!tpl/message/conversation-recipients-dir.html'
], function(conversationRecipientsDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: conversationRecipientsDirTpl
		};
	};
});