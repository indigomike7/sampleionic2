define([
	'message/controller/message-list',
	'text!tpl/message/message-list-dir.html'
], function(MessageListCtrl, messageListDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: messageListDirTpl,
			controller: MessageListCtrl
		};
	};
});