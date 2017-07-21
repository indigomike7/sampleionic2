define([
    'activity/directive/activity-emoticon-panel',
    'activity/directive/activity-list-dir',
    'activity/directive/attachment-dir',
    'activity/directive/feed-item'
], function() {

    angular.module('myapp.directives')
        .directive('activityEmoticonPanel', require('activity/directive/activity-emoticon-panel'))
        .directive('activityListDir', require('activity/directive/activity-list-dir'))
        .directive('attachmentDir', require('activity/directive/attachment-dir'))
        .directive('feedItem', require('activity/directive/feed-item'));
});