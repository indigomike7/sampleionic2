define([
	'message/controller/conversation-add',
	'message/controller/conversation-inbox',
	'message/controller/conversation-sent',
	'message/controller/conversation-detail',
	'message/controller/conversation-item',
	'message/controller/conversation-list',
	'message/controller/conversation-reply',
	'message/controller/message-friend-autocomplete-list',
	'message/controller/message-home'
], function() {

	angular.module('myapp.controllers')
	    .controller('MessageHomeCtrl', require('message/controller/message-home'))
		.controller('ConversationAddCtrl', require('message/controller/conversation-add'))
		.controller('ConversationInboxCtrl', require('message/controller/conversation-inbox'))
		.controller('ConversationSentCtrl', require('message/controller/conversation-sent'))
		.controller('ConversationDetailCtrl', require('message/controller/conversation-detail'))
		.controller('ConversationItemCtrl', require('message/controller/conversation-item'))
		.controller('ConversationListCtrl', require('message/controller/conversation-list'))
		.controller('ConversationReplyCtrl', require('message/controller/conversation-reply'))
		.controller('MessageFriendAutocompleteListCtrl', require('message/controller/message-friend-autocomplete-list'))
	;
});