define([
    'directory/directive/directory-all-item',
    'directory/directive/directory-all-list',
    'directory/directive/directory-claim-item',
    'directory/directive/directory-claim-list',
    'directory/directive/directory-faq-item',
    'directory/directive/directory-faq-list',
    'directory/directive/directory-favourite-item',
    'directory/directive/directory-favourite-list',
    'directory/directive/directory-follow-item',
    'directory/directive/directory-follow-list',
    'directory/directive/directory-follower-item',
    'directory/directive/directory-follower-list',
    'directory/directive/directory-member-item',
    'directory/directive/directory-member-list',
    'directory/directive/directory-music-item',
    'directory/directive/directory-music-list',
    'directory/directive/directory-my-item',
    'directory/directive/directory-my-list',
    'directory/directive/directory-review-item',
    'directory/directive/directory-review-list',
    'directory/directive/directory-search',
    'directory/directive/directory-topic-list',
    'directory/directive/directory-post-list',
], function() {
    angular.module('myapp.directives')
        .directive('directoryAllItem', require('directory/directive/directory-all-item'))
        .directive('directoryAllList', require('directory/directive/directory-all-list'))
        .directive('directoryClaimItem', require('directory/directive/directory-claim-item'))
        .directive('directoryClaimList', require('directory/directive/directory-claim-list'))
        .directive('directoryFaqItem', require('directory/directive/directory-faq-item'))
        .directive('directoryFaqList', require('directory/directive/directory-faq-list'))
        .directive('directoryFavouriteItem', require('directory/directive/directory-favourite-item'))
        .directive('directoryFavouriteList', require('directory/directive/directory-favourite-list'))
        .directive('directoryFollowItem', require('directory/directive/directory-follow-item'))
        .directive('directoryFollowList', require('directory/directive/directory-follow-list'))
        .directive('directoryFollowerItem', require('directory/directive/directory-follower-item'))
        .directive('directoryFollowerList', require('directory/directive/directory-follower-list'))
        .directive('directoryMemberItem', require('directory/directive/directory-member-item'))
        .directive('directoryMemberList', require('directory/directive/directory-member-list'))
        .directive('directoryMusicItem', require('directory/directive/directory-music-item'))
        .directive('directoryMusicList', require('directory/directive/directory-music-list'))
        .directive('directoryMyItem', require('directory/directive/directory-my-item'))
        .directive('directoryMyList', require('directory/directive/directory-my-list'))
        .directive('directoryReviewItem', require('directory/directive/directory-review-item'))
        .directive('directoryReviewList', require('directory/directive/directory-review-list'))
        .directive('directorySearch', require('directory/directive/directory-search'))
        .directive('directoryTopicListDir', require('directory/directive/directory-topic-list'))
        .directive('directoryPostListDir', require('directory/directive/directory-post-list'))
    ;
});