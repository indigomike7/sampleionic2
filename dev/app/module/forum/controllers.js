define([
	'forum/controller/forum-browse',
	'forum/controller/forum-detail',
	'forum/controller/forum-home',
	'forum/controller/forum-list',
	'forum/controller/forum-poll-add',
	'forum/controller/forum-post-edit',
	'forum/controller/forum-post-form',
	'forum/controller/forum-post-item',
	'forum/controller/forum-post-search',
	'forum/controller/forum-post-search-list',
	'forum/controller/forum-search',
	'forum/controller/forum-thread-add',
	'forum/controller/forum-thread-browse',
	'forum/controller/forum-thread-detail',
	'forum/controller/forum-thread-edit',
	'forum/controller/forum-thread-list',
	'forum/controller/forum-thread-reply',
	'forum/controller/forum-thread-search',
	'forum/controller/forum-thread-search-list'
], function() {

	angular.module('myapp.controllers')
		.controller('ForumBrowseCtrl', require('forum/controller/forum-browse'))
		.controller('ForumDetailCtrl', require('forum/controller/forum-detail'))
		.controller('ForumHomeCtrl', require('forum/controller/forum-home'))
		.controller('ForumListCtrl', require('forum/controller/forum-list'))
		.controller('ForumPollAddCtrl', require('forum/controller/forum-poll-add'))
		.controller('ForumPostEditCtrl', require('forum/controller/forum-post-edit'))
		.controller('ForumPostFormCtrl', require('forum/controller/forum-post-form'))
		.controller('ForumPostItemCtrl', require('forum/controller/forum-post-item'))
		.controller('ForumPostSearchCtrl', require('forum/controller/forum-post-search'))
		.controller('ForumPostSearchListCtrl', require('forum/controller/forum-post-search-list'))
		.controller('ForumSearchCtrl', require('forum/controller/forum-search'))
		.controller('ForumThreadAddCtrl', require('forum/controller/forum-thread-add'))
		.controller('ForumThreadBrowseCtrl', require('forum/controller/forum-thread-browse'))
		.controller('ForumThreadDetailCtrl', require('forum/controller/forum-thread-detail'))
		.controller('ForumThreadEditCtrl', require('forum/controller/forum-thread-edit'))
		.controller('ForumThreadListCtrl', require('forum/controller/forum-thread-list'))
		.controller('ForumThreadReplyCtrl', require('forum/controller/forum-thread-reply'))
		.controller('ForumThreadSearchCtrl', require('forum/controller/forum-thread-search'))
		.controller('ForumThreadSearchListCtrl', require('forum/controller/forum-thread-search-list'));
});