define([
    'event/directive/event-search',
    'event/directive/event-list',
], function() {
	angular.module('myapp.directives')
	    .directive('eventListDir', require('event/directive/event-list'))
		.directive('eventSearchDir', require('event/directive/event-search'));
});