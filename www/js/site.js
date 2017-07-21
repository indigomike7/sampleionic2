define('settings/site', function() {
    var 
        
        // Demo
        host = 'http://www.ringsidetalk.net',
        path = '/',

        device = 'android';

    return {
        siteHost:           host,
        siteUrl:            host + path,
        apiUrl:             host + path + 'PF.Base/module/mfox/api.php/',
        cssUrl:             host + path + 'PF.Base/file/mfox/css/',
        cometchatApiUrl:    host + path + 'PF.Base/cometchat/cometchat_api_mysqli.php?q=',

        lang: {
            def: 'en',
            options: []
        },

        home: '/app/newsfeed',

        debug: 0, // available 1: error,2: info, 3: verbose
        imgCacheSize: 50 * 1024 * 1024,
        imgTargetSize: 1280,
        isOnline: true,
        isTablet: false,
        cachedSettingInterval: 600000, //10 minutes
        useLocalCss: true,
        cacheCss: true,

        theme: device,
        template: device,
        platform: device == 'android' ? 'android' : 'ios',
        pushNotificationPlatform: device == 'android' ? 'android' : (device == 'ipad' ? 'ipad' : 'ios'),
        googleCloudMessageSenderId: '122520028144',
        googleApiKey: 'AIzaSyAbT_waGAuZ-LqLjcTQWzY3dJ8RJbovPeI',
        token: ''
    };
});
