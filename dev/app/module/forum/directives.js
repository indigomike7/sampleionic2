define([
	'forum/directive/forum-detail-announcement-list',
	'forum/directive/forum-detail-subforum-list',
	'forum/directive/forum-detail-thread-list',
	'forum/directive/forum-list',
	'forum/directive/forum-post-search-list',
	'forum/directive/forum-search',
	'forum/directive/forum-thread-detail-post-list',
	'forum/directive/forum-thread-detail-poll-detail',
	'forum/directive/forum-thread-list',
	'forum/directive/forum-thread-search-list'
], function() {

	angular.module('myapp.directives')
		.directive('forumDetailAnnouncementListDir', require('forum/directive/forum-detail-announcement-list'))
		.directive('forumDetailSubforumListDir', require('forum/directive/forum-detail-subforum-list'))
		.directive('forumDetailThreadListDir', require('forum/directive/forum-detail-thread-list'))
		.directive('forumListDir', require('forum/directive/forum-list'))
		.directive('forumPostSearchListDir', require('forum/directive/forum-post-search-list'))
		.directive('forumSearchDir', require('forum/directive/forum-search'))
		.directive('forumThreadDetailPostListDir', require('forum/directive/forum-thread-detail-post-list'))
		.directive('forumThreadDetailPollDetailDir', require('forum/directive/forum-thread-detail-poll-detail'))
		.directive('forumThreadListDir', require('forum/directive/forum-thread-list')) 
		.directive('forumThreadSearchListDir', require('forum/directive/forum-thread-search-list'));
});