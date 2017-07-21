define([
	'text!tpl/chat/chat-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/chat/chat-list-dir.html'),
			controller: 'ChatListCtrl'
		};
	};
});