define([
	'text!tpl/chat/chat-message-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/chat/chat-message-list-dir.html'),
			controller: 'ChatMessageListCtrl'
		};
	};
});