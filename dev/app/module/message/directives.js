define([
	'message/directive/conversation-list-dir',
	'message/directive/conversation-recipients-dir',
	'message/directive/conversation-search-dir',
	'message/directive/message-attachment-dir',
	'message/directive/message-friend-autocomplete-list',
	'message/directive/message-list-dir'
], function() {

	angular.module('myapp.directives')
		.directive('conversationListDir', require('message/directive/conversation-list-dir'))
		.directive('conversationRecipientsDir', require('message/directive/conversation-recipients-dir'))
		.directive('conversationSearchDir', require('message/directive/conversation-search-dir'))
		.directive('messageAttachmentDir', require('message/directive/message-attachment-dir'))
		.directive('messageFriendAutocompleteList', require('message/directive/message-friend-autocomplete-list'))
		.directive('messageListDir', require('message/directive/message-list-dir'));
});