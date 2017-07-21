define([
	'poll/directive/poll-detail',
    'poll/directive/poll-list',
    'poll/directive/poll-search',
],function(){
    angular.module('myapp.directives')
    .directive('pollDetailDir', require('poll/directive/poll-detail'))
    .directive('pollSearchDir', require('poll/directive/poll-search'))
    .directive('pollListDir', require('poll/directive/poll-list'))
    ;
});
