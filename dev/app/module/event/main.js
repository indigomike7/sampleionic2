define([
    'event/controllers',
    'event/directives',
    'event/plugin/activity',
    
    'text!tpl/event/event-activity.html',
    'text!tpl/event/event-add.html',
    // 'text!tpl/event/event-attachment.html',
    'text!tpl/event/event-detail.html',
    'text!tpl/event/event-edit.html',
    'text!tpl/event/event-guest-list.html',
    'text!tpl/event/event-home.html',
    'text!tpl/event/event-info.html',
    'text!tpl/event/event-invite-list.html',
    // 'text!tpl/event/event-list.html',
    'text!tpl/event/event-my-event.html',
    'text!tpl/event/event-past-event.html',
    'text!tpl/event/event-photo.html',
    // 'text!tpl/event/event-search.html',
    'text!tpl/event/event-upcoming-event.html',
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider
            .state('app.EventAdd', {
                module: 'event',
                url: '/events/add',
                cache: false,
                history: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-add.html'),
                        controller: 'EventAddCtrl',
                    }
                }
            })
            .state('app.EventAddType', {
                module: 'event',
                url: '/events/add/:sParentType/:iParentId',
                cache: false,
                history: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-add.html'),
                        controller: 'EventAddCtrl',
                    }
                }
            })
            .state('app.EventEdit', {
                module: 'event',
                url: '/event/:id/edit',
                cache: false,
                history: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-edit.html'),
                        controller: 'EventEditCtrl',
                    }
                }
            })
            .state('app.FeventEdit', {
                module: 'event',
                modelType: 'fevent',
                url: '/fevent/:id/edit',
                cache: false,
                history: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-edit.html'),
                        controller: 'EventEditCtrl',
                    }
                }
            })
            .state('app.EventHome', {
                module: 'event',
                url: '/events',
                cache: false,
                history: {
                    title: gettext('Events'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-home.html'),
                        controller: 'EventHomeCtrl',
                    },
                    'tabContent@app.EventHome':{
                        template: require('text!tpl/event/event-upcoming-event.html'),
                        controller: 'EventUpcomingEventCtrl',
                    }
                }
            })
            .state('app.EventMy', {
                module: 'event',
                url: '/events/my',
                cache: false,
                history: {
                    title: gettext('Events'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-home.html'),
                        controller: 'EventHomeCtrl',
                    },
                    'tabContent@app.EventMy':{
                        template: require('text!tpl/event/event-my-event.html'),
                        controller: 'EventMyEventCtrl',
                    }
                }
            })
            .state('app.EventPast', {
                module: 'event',
                url: '/events/past',
                cache: false,
                history: {
                    title: gettext('Events'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-home.html'),
                        controller: 'EventHomeCtrl',
                    },
                    'tabContent@app.EventPast':{
                        template: require('text!tpl/event/event-past-event.html'),
                        controller: 'EventPastEventCtrl',
                    }
                }
            })
            .state('app.EventDetail', {
                module: 'event',
                url: '/event/:id',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-detail.html'),
                        controller: 'EventDetailCtrl',
                    },
                    'tabContent@app.EventDetail':{
                        template: require('text!tpl/event/event-info.html'),
                        controller: 'EventInfoCtrl',
                    }
                }
            })
            .state('app.EventPhoto', {
                module: 'event',
                url: '/event/:id/photos',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-detail.html'),
                        controller: 'EventDetailCtrl',
                    },
                    'tabContent@app.EventPhoto':{
                        template: require('text!tpl/event/event-photo.html'),
                        controller: 'EventPhotoCtrl',
                    }
                }
            })
            .state('app.EventActivity', {
                module: 'event',
                url: '/event/:id/activity',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-detail.html'),
                        controller: 'EventDetailCtrl',
                    },
                    'tabContent@app.EventActivity':{
                        template: require('text!tpl/event/event-activity.html'),
                        controller: 'EventActivityCtrl',
                    }
                }
            })
            .state('app.FeventDetail', {
                module: 'event',
                modelType: 'fevent',
                url: '/fevent/:id',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-detail.html'),
                        controller: 'EventDetailCtrl',
                    },
                    'tabContent@app.FeventDetail':{
                        template: require('text!tpl/event/event-info.html'),
                        controller: 'EventInfoCtrl',
                    }
                }
            })
            .state('app.FeventPhoto', {
                module: 'event',
                modelType: 'fevent',
                url: '/fevent/:id/photos',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-detail.html'),
                        controller: 'EventDetailCtrl',
                    },
                    'tabContent@app.FeventPhoto':{
                        template: require('text!tpl/event/event-photo.html'),
                        controller: 'EventPhotoCtrl',
                    }
                }
            })
            .state('app.FeventActivity', {
                module: 'event',
                modelType: 'fevent',
                url: '/fevent/:id/activity',
                cache: false,
                views: {
                    'menuContent': {
                        template: require('text!tpl/event/event-detail.html'),
                        controller: 'EventDetailCtrl',
                    },
                    'tabContent@app.FeventActivity':{
                        template: require('text!tpl/event/event-activity.html'),
                        controller: 'EventActivityCtrl',
                    }
                }
            })
            ;
    });
});