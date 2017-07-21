define([
    'global/myapp',
    'global/service/core-settings',
    'global/service/core-menu',
    'global/service/file',
    'global/service/global-data',
    'global/service/history',
    'global/service/http-cache',
    'global/service/in-app-purchase',
    'global/service/language',
    'global/service/modal',
    'global/service/site',
    'global/service/notification',
    'global/service/viewer',
    'global/service/http2',
    'global/service/request-queue',
    'global/service/core-phrases',
    'global/service/core-theme'
], function() {

    angular.module('myapp.services')
        .service('$coreSettings', require('global/service/core-settings'))
        .service('$coreMenu', require('global/service/core-menu'))
        .service('$http2', require('global/service/http2'))
        .service('$file', require('global/service/file'))
        .service('$globalData', require('global/service/global-data'))
        .service('$history', require('global/service/history'))
        .service('$httpCache', require('global/service/http-cache'))
        .service('$iap', require('global/service/in-app-purchase'))
        .service('$language', require('global/service/language'))
        .service('$modal', require('global/service/modal'))
        .service('$site', require('global/service/site'))
        .service('$notification', require('global/service/notification'))
        .service('$viewer', require('global/service/viewer'))
        .service('$requestQueue', require('global/service/request-queue'))
        .service('$corePhrases', require('global/service/core-phrases'))
        .service('$coreTheme', require('global/service/core-theme'))
    ;
});