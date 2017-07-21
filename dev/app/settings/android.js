/*global requirejs, document, cordova, window, navigator, console */

requirejs.config({
    baseUrl: './',
    packages: [{
        name: 'global',
        location: './app/global'
    }, {
        name: 'settings',
        location: 'app/settings',
    },{
        name: 'language',
        location: './app/language'
    }, {
        name: 'core',
        location: './app/module/core',
    }, {
        name: 'user',
        location: './app/module/user',
    }, {
        name: 'setting',
        location: './app/module/setting'
    }, {
        name: 'friend',
        location: './app/module/friend'
    }, {
        name: 'search',
        location: './app/module/search'
    }, {
        name: 'member',
        location: './app/module/member'
    }, {
        name: 'activity',
        location: './app/module/activity',
        //location: './app/module/advfeed',
    }, {
        name: 'activity-attachment',
        location: './app/module/activity-attachment',
    }, {
        name: 'share',
        location: './app/module/share',
    }, {
        name: 'comment',
        location: './app/module/comment',
    }, {
        name: 'like',
        location: './app/module/like',
    }, {
        name: 'marketplace',
        location: './app/module/marketplace',
    }, {
        name: 'video',
        location: './app/module/video',
    }, {
        name: 'videochannel',
        location: './app/module/videochannel',
    }, {
        name: 'ultimatevideo',
        location: './app/module/ultimatevideo',
    }, {
        name: 'photo',
        location: './app/module/photo',
    }, {
        name: 'message',
        location: './app/module/message',
    }, {
        name: 'notification',
        location: './app/module/notification',
    }, {
        name: 'request',
        location: './app/module/request',
    }, {
        name: 'attachment',
        location: './app/module/attachment',
        //location: './app/module/advfeed-attachment',
    }, {
        name: 'location',
        location: './app/module/location',
    }, {
        name: 'event',
        location: './app/module/event',
    }, {
        name: 'music',
        location: './app/module/music',
    }, {
        name: 'poll',
        location: './app/module/poll',
    }, {
        name: 'quiz',
        location: './app/module/quiz',
    }, {
        name: 'forum',
        location: './app/module/forum',
    }, {
        name: 'subscription',
        location: './app/module/subscription',
    }, {
        name: 'group',
        location: './app/module/group',
    }, {
        name: 'page',
        location: './app/module/page',
    }, {
        name: 'blog',
        location: './app/module/blog',
    }, {
        name: 'chat',
        location: './app/module/chat',
    }, {
        name: 'cometchat',
        location: './app/module/cometchat',
    }, {
        name: 'ynchat',
        location: './app/module/ynchat',
    }, {
        name: 'dislike',
        location: './app/module/dislike',
    }, {
        name: 'directory',
        location: './app/module/directory',
    }, {
        name: 'tpl',
        location: './app/templates/android',
    }],
    paths: {
        bootstrap: './app/global/bootstrap',
        myapp: './app/global/myapp',
        site: './app/global/site',
        angularCache: './app/lib/angular-cache/dist/angular-cache',
        angularData: './app/lib/angular-data/dist/angular-data',
        sprintfJS: './app/lib/sprintf/src/sprintf',
        angular: './app/lib/angular/angular',
        angularAnimate: './app/lib/angular-animate/angular-animate',
        angularSanitize: './app/lib/angular-sanitize/angular-sanitize',
        angularGetText: './app/lib/angular-gettext/dist/angular-gettext',
        uiRouter: './app/lib/angular-ui-router/release/angular-ui-router',
        'textarea-helper': './app/lib/textarea-helper/textarea-helper',

        ionic: './app/lib/ionic/js/ionic',
        ionicAngular: './app/lib/ionic/js/ionic-angular',
        angularTruncate: './app/lib/angular-truncate/src/truncate',
        ngIOS9UIWebViewPatch: './app/lib/patch/angular-ios9-uiwebview',
        classjs: './app/lib/classjs/class',
        jquery: './app/lib/jquery/jquery',
        typeahead: './app/lib/typeahead/typeahead',
        text: './app/lib/requirejs-text/text',
        ImgCache: './app/lib/imgcache.js/js/imgcache',
        
        CONTROLLER_PLAYER: './app/lib/htmlplayer/controller_player',
        MediaElelementPlayer: './app/lib/htmlplayer/mediaelement-and-player',
        
        jcrop: './app/lib/jcrop/js/jquery.Jcrop',
        jcolor: './app/lib/jcrop/js/jquery.color',
        moment: './app/lib/moment/min/moment-with-locales.min',
        iscroll: './app/lib/iscroll/iscroll',
        swiper: './app/lib/swiper/dist/idangerous.swiper',
        contenteditable: './app/lib/angular-contenteditable/angular-contenteditable',
        ionGooglePlace: './app/lib/ion-google-place/ion-google-place',
        requestAnimationFrame: './app/lib/jquery-pull-to-refresh/requestAnimationFrame',
        jqueryPullToRefresh: './app/lib/jquery-pull-to-refresh/jquery.plugin.pullToRefresh',
        angularSocketIO: './app/lib/angular-socket-io/socket'
    },
    shim: {
        // jquery: {exports: 'jquery'},
        CONTROLLER_PLAYER: {
            deps: ['MediaElelementPlayer'],
            exports: 'CONTROLLER_PLAYER'
        },
        ImgCache: {
            exports: 'ImgCache',
            deps: ['jquery'],
        },
        sprintfJS: {
            exports: ['sprintfJS'],
        },
        angular: {
            exports: 'angular'
        },
        angularAnimate: {
            deps: ['angular']
        },
        angularSanitize: {
            deps: ['angular']
        },
        uiRouter: {
            deps: ['angular']
        },
        angularFileUpload: {
            deps: ['angular']
        },
        ionic: {
            exports: 'ionic'
        },
        jcrop: {
            deps: ['jquery', 'jcolor'],
            exports: 'jcrop'
        },
        iscroll: {
            exports: 'iScroll',
        },
        jqueryPullToRefresh: {
            deps: ['jquery', 'requestAnimationFrame']
        },
        ionicAngular: {
            deps: ['angular', 'ngIOS9UIWebViewPatch', 'ionGooglePlace', 'sprintfJS', 'contenteditable','iscroll', 'swiper', 'angularGetText', 'angularTruncate', 'ionic', 'uiRouter', 'angularAnimate', 'angularSanitize']
        }
    },
    priority: [
        'ionic',
        'angular'
    ],
    deps: [

    ]
});

require([
    'jquery',
    'jqueryPullToRefresh',
    'textarea-helper',
    'typeahead',
    'ionic',
    'angular',
    'ionicAngular',
    'classjs',
    'angularSocketIO',
    'myapp',
    // basic module
    'global',
    'core',
    'user',
    'activity',
    'activity-attachment',
    'setting',
    'comment',
    'like',
    'marketplace',
    'video',
    'videochannel',
    'ultimatevideo',
    'search',
    'photo',
    'friend',
    'member',
    'message',
    'notification',
    'request',
    'attachment',
    'location',
    'event',
    'music',
    'forum',
    'poll',
    'quiz',
    'subscription',
    'group',
    'page',
    'blog',
    'chat',
    'cometchat',
    'ynchat',
    'dislike',
    'directory',
    'CONTROLLER_PLAYER',
    // add bootstrap at later, do not remove this code
    'bootstrap',
], function() {

});
