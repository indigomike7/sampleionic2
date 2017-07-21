define([
    'setting/controller/setting-home',
    'setting/controller/setting-fullname',
    'setting/controller/setting-username',
    'setting/controller/setting-email',
    'setting/controller/setting-password',
    'setting/controller/setting-language',
    'setting/controller/setting-timezone',
    'setting/controller/setting-defaultcurrency',
    'setting/controller/setting-profile',
    'setting/controller/setting-items',
    'setting/controller/setting-notifications',
    'setting/controller/setting-blockedusers',
    'setting/controller/setting-invisiblemode'
], function() {
    angular.module('myapp.controllers')
        .controller('SettingHomeCtrl', require('setting/controller/setting-home'))
        .controller('SettingFullnameCtrl', require('setting/controller/setting-fullname'))
        .controller('SettingUsernameCtrl', require('setting/controller/setting-username'))
        .controller('SettingEmailCtrl', require('setting/controller/setting-email'))
        .controller('SettingPasswordCtrl', require('setting/controller/setting-password'))
        .controller('SettingLanguageCtrl', require('setting/controller/setting-language'))
        .controller('SettingTimezoneCtrl', require('setting/controller/setting-timezone'))
        .controller('SettingDefaultcurrencyCtrl', require('setting/controller/setting-defaultcurrency'))
        .controller('SettingProfileCtrl', require('setting/controller/setting-profile'))
        .controller('SettingItemsCtrl', require('setting/controller/setting-items'))
        .controller('SettingNotificationsCtrl', require('setting/controller/setting-notifications'))
        .controller('SettingBlockedusersCtrl', require('setting/controller/setting-blockedusers'))
        .controller('SettingInvisiblemodeCtrl', require('setting/controller/setting-invisiblemode'));
});