define([
    'setting/controllers',
    'setting/directives',
    'setting/services',
    'text!tpl/setting/setting-home.html',
    'text!tpl/setting/setting-fullname.html',
    'text!tpl/setting/setting-username.html',
    'text!tpl/setting/setting-email.html',
    'text!tpl/setting/setting-password.html',
    'text!tpl/setting/setting-language.html',
    'text!tpl/setting/setting-timezone.html',
    'text!tpl/setting/setting-defaultcurrency.html',
    'text!tpl/setting/setting-profile.html',
    'text!tpl/setting/setting-items.html',
    'text!tpl/setting/setting-notifications.html',
    'text!tpl/setting/setting-blockedusers.html',
    'text!tpl/setting/setting-invisiblemode.html'
], function() {

    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {

        $stateProvider.state('app.settingHome', {
            module: 'setting',
            url: '/settings',
            cache: false,
            history: {
                title: gettext('Settings'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-home.html'),
                    controller: 'SettingHomeCtrl',
                }
            }
        }).state('app.settingFullname', {
            module: 'setting',
            url: '/settings/fullname',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-fullname.html'),
                    controller: 'SettingFullnameCtrl',
                }
            }
        }).state('app.settingUsername', {
            module: 'setting',
            url: '/settings/username',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-username.html'),
                    controller: 'SettingUsernameCtrl',
                }
            }
        }).state('app.settingEmail', {
            module: 'setting',
            url: '/settings/email/:oldvalue',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-email.html'),
                    controller: 'SettingEmailCtrl',
                }
            }
        }).state('app.settingPassword', {
            module: 'setting',
            url: '/settings/password',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-password.html'),
                    controller: 'SettingPasswordCtrl',
                }
            }
        }).state('app.settingLanguage', {
            module: 'setting',
            url: '/settings/language',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-language.html'),
                    controller: 'SettingLanguageCtrl',
                }
            }
        }).state('app.settingTimezone', {
            module: 'setting',
            url: '/settings/timezone',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-timezone.html'),
                    controller: 'SettingTimezoneCtrl',
                }
            }
        }).state('app.settingDefaultcurrency', {
            module: 'setting',
            url: '/settings/defaultcurrency',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-defaultcurrency.html'),
                    controller: 'SettingDefaultcurrencyCtrl',
                }
            }
        }).state('app.settingProfile', {
            module: 'setting',
            url: '/settings/profile',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-profile.html'),
                    controller: 'SettingProfileCtrl',
                }
            }
        }).state('app.settingItems', {
            module: 'setting',
            url: '/settings/items',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-items.html'),
                    controller: 'SettingItemsCtrl',
                }
            }
        }).state('app.settingNotifications', {
            module: 'setting',
            url: '/settings/notifications',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-notifications.html'),
                    controller: 'SettingNotificationsCtrl',
                }
            }
        }).state('app.settingBlockedusers', {
            module: 'setting',
            url: '/settings/blockedusers',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-blockedusers.html'),
                    controller: 'SettingBlockedusersCtrl',
                }
            }
        }).state('app.settingInvisiblemode', {
            module: 'setting',
            url: '/settings/invisiblemode/:oldvalue',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/setting/setting-invisiblemode.html'),
                    controller: 'SettingInvisiblemodeCtrl',
                }
            }
        });
    });
});