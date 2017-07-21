define([
    'event/controller/event-activity',
    'event/controller/event-add',
    'event/controller/event-detail',
    'event/controller/event-edit',
    'event/controller/event-guest-list',
    'event/controller/event-home',
    'event/controller/event-info',
    'event/controller/event-invite-list',
    'event/controller/event-item',
    // 'event/controller/event-list',
    'event/controller/event-my-event',
    'event/controller/event-past-event',
    'event/controller/event-photo',
    'event/controller/event-search',
    'event/controller/event-upcoming-event',
    
], function() {
    angular.module('myapp.controllers')
        .controller('EventActivityCtrl', require('event/controller/event-activity'))
        .controller('EventAddCtrl', require('event/controller/event-add'))
        .controller('EventDetailCtrl', require('event/controller/event-detail'))
        .controller('EventEditCtrl', require('event/controller/event-edit'))
        .controller('EventGuestListCtrl', require('event/controller/event-guest-list'))
        .controller('EventHomeCtrl', require('event/controller/event-home'))
        .controller('EventInfoCtrl', require('event/controller/event-info'))
        .controller('EventInviteListCtrl', require('event/controller/event-invite-list'))
        .controller('EventItemCtrl', require('event/controller/event-item'))
        .controller('EventMyEventCtrl', require('event/controller/event-my-event'))
        .controller('EventPastEventCtrl', require('event/controller/event-past-event'))
        .controller('EventPhotoCtrl', require('event/controller/event-photo'))
        .controller('EventSearchCtrl', require('event/controller/event-search'))
        .controller('EventUpcomingEventCtrl', require('event/controller/event-upcoming-event'))
        ;
});