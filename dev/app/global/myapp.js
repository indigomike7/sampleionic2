define([
    'angular', 
    'global/site',
    'global/helper', 
    'ionic', 
    'uiRouter', 
    'ImgCache',
], function(angular, Site, Helper) {
    
    var myapp = angular.module('myapp', [
        'ionic', 
        'ui.router', 
        'gettext', 
        'myapp.controllers', 
        'myapp.services', 
        'myapp.directives', 
        'myapp.filters', 
        'truncate', 
        'contenteditable',
        'ngIOS9UIWebViewPatch',
        'ion-google-place',
        'btford.socket-io'
    ]);
    myapp.factory('socket', function ($rootScope) {
        var socket;// = io.connect('http://localhost:3000');
        return {
            init: function(server) {
                socket = io.connect(server);
                console.log(socket);
            },
            on: function (eventName, callback) {
                socket.on(eventName, function () {
                    var args = arguments;
                    $rootScope.$apply(function () {
                        callback.apply(socket, args);
                    });
                });
            },
            emit: function () {
                var lastIndex = arguments.length - 1;
                var callback = arguments[lastIndex];
                if(typeof callback == 'function') {
                    callback = asyncAngularify(socket, callback);
                    arguments[lastIndex] = callback;
                }
                return socket.emit.apply(socket, arguments);
            }
        };
    });
    myapp.config(['$compileProvider', '$ionicConfigProvider',
        function($compileProvider, $ionicConfigProvider) {
            // Default imgSrcSanitizationWhitelist: /^\s*(https?|ftp|file):|data:image\//
            $compileProvider.imgSrcSanitizationWhitelist(/^\s*(https?|ftp|file|content):|data:image\//);
        }
    ]);

    myapp.run(function(gettextCatalog, $modal, $location, gettext, $ionicPlatform, $site, $http, $rootScope, $timeout, $history, $language, $coreSettings, $coreMenu, $corePhrases, $viewer, $notification, $interval, $state, $globalData) {
        
        function checkNetworkConnection() {
            if (typeof navigator.connection == 'undefined') {
                Site.isOnline = true;
            } else {
                Site.isOnline = navigator.connection.type == Connection.NONE ? false : true;
            }
            console.log('network connection: ', Site.isOnline);
            return Site.isOnline;
        }

        // Setup phrases
        $corePhrases.update();

        function updateAndroidMenu(){

            if (!window.nativeControl) {
                return;
            }
            var coreMenus = $coreMenu.get();
            var menus = [];
            for (var key in coreMenus)
            {
                if(coreMenus[key].type == "menu_item"){
                    menus.push({
                        menu: coreMenus[key].name,
                        label: gettextCatalog.getString(coreMenus[key].label),
                        icon: coreMenus[key].icon,
                        type: coreMenus[key].type,
                    });
                }
                if(coreMenus[key].type == "menu_group"){
                    if(coreMenus[key].children.length > 0){
                        menus.push({
                            menu: coreMenus[key].name,
                            label: gettextCatalog.getString(coreMenus[key].label),
                            icon: coreMenus[key].icon,
                            type: coreMenus[key].type,
                        });
                    }
                    else {
                        continue;
                    }
                    var childrenMenu = coreMenus[key].children;
                    for(var key2 in childrenMenu){
                        if (!childrenMenu.hasOwnProperty(key2)) continue;
                        menus.push({
                            menu: childrenMenu[key2].name,
                            label: gettextCatalog.getString(childrenMenu[key2].label),
                            icon: childrenMenu[key2].icon,
                            type: "menu_submenu",
                        });
                    }
                }
            }

            if ($site.lang.options.length > 1) {
                menus.push({
                    menu: 'language',
                    label: gettextCatalog.getString('Language'),
                    icon: 'ion-earth'
                });
            }

            menus.push({
                menu: 'logout',
                label: gettextCatalog.getString('Logout'),
                icon: 'ion-power'
            });

            window.nativeControl.updateMenuItems(menus);
        }

        function onSideBarMenuClick(name) {
            var menus = [];
            var coreMenus = $coreMenu.get();
            for (var key in coreMenus)
            {
                if(coreMenus[key].type == "menu_item"){
                    menus.push({
                        name: coreMenus[key].name,
                        href: coreMenus[key].href
                    });
                }
                if(coreMenus[key].type == "menu_group"){
                    if(coreMenus[key].children.length > 0){
                        menus.push({
                            name: coreMenus[key].name,
                            href: coreMenus[key].href
                        });
                    }
                    else {
                        continue;
                    }
                    var childrenMenu = coreMenus[key].children;
                    for(var key2 in childrenMenu){
                        if (!childrenMenu.hasOwnProperty(key2)) continue;
                        menus.push({
                            name: childrenMenu[key2].name,
                            href: childrenMenu[key2].href
                        });
                    }
                }
            }
            var getMenuHref = function() {
                for (var i in menus) {
                    if (menus[i].name == name && name != "") {
                        return menus[i].href;
                    }
                }

                return false;
            };
            switch (name) {
                case 'viewer':
                    $location.path('app/user-from-nav/' + $viewer.get('iUserId'));
                    $rootScope.$$phase || $rootScope.$apply();
                    break;
                case 'language':
                    $language.doSelectLanguage();
                    break;
                case 'logout':
                    $modal.confirm(gettextCatalog.getString('Do you want to log out of application?'), function(result) {
                        if (result == 1) {
                            $location.path('app/login');
                            if (ionic.Platform.isAndroid()) {
                                if (window.nativeControl) {
                                    window.nativeControl.setActiveItem('activity');
                                }
                            }
                            return $rootScope.$$phase || $rootScope.$apply();
                        } else {
                            if (ionic.Platform.isAndroid()) {
                                if (window.nativeControl) {
                                    window.nativeControl.setActiveItem($state.current.module);
                                }
                            }
                        }
                    }, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
                    break;
                default:
                    if (getMenuHref() !== false) {
                        $location.path(getMenuHref());
                        $rootScope.$$phase || $rootScope.$apply();
                    }
            }
        }

        if (ionic.Platform.isAndroid()) {
            if (window.nativeControl) {
                window.nativeControl.unlockMenu();
            }

            updateAndroidMenu();
            $rootScope.$on('coreMenu:update', updateAndroidMenu);

            window.onSideBarMenuClick = onSideBarMenuClick;
        }

        // Remove search box
        $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams, options) {
            angular.element('body').removeClass('search-open search-advanced');
        });

        // active menu when state change
        function onStateChangeSuccess(event, toState, toParams, fromState, fromParams) {
            if (window.nativeControl) {
                $timeout(function() {
                    window.nativeControl.setActiveItem($state.current.module);
                }, 1000);
            }
        };

        $rootScope.$on('$stateChangeSuccess', onStateChangeSuccess);

        function onOrientationChange() {
            $site.debug > 2 && console.log("on orientation =", window.orientation);
            var orientation = Math.abs(window.orientation) == 90 ? 'landscape' : 'portrait';
            if (orientation == 'landscape') {
                angular.element('body').removeClass('portrait').addClass('landscape');
            } else {
                angular.element('body').removeClass('landscape').addClass('portrait');
            }
        }

        $timeout(function() {
            onOrientationChange();
        }, 500);

        function updateViewPort() {}

        window.addEventListener('orientationchange', onOrientationChange);

        (function() {
            checkNetworkConnection();
            document.addEventListener('online', checkNetworkConnection);
            document.addEventListener('offline', checkNetworkConnection);
        })();

        (function() {
            // setup ImgCache options
            ImgCache.options.debug = false;
            ImgCache.options.chromeQuota = $site.imgCacheSize;
        })();

        // SETUP LANGGUE
        //(function() {
        //    var lang = localStorage.getItem('lang') || $site.lang.def;
        //    var isValid = false;
        //    // validate language options
        //    for (var i = 0; i < $site.lang.options.length; ++i) {
        //        var opt = $site.lang.options[i];
        //        if (opt.lang == lang) {
        //            isValid = true;
        //        }
        //    }
        //    if (!isValid) {
        //        console.log('language is invalid ', lang);
        //        lang = $site.lang.def;
        //    }
        //    console.log('current language', lang);
        //    gettextCatalog.setCurrentLanguage(lang);
        //    moment.locale(lang);
        //    gettextCatalog.debug = false;
        //})();

        (function() {
            // setup token viewer
            var token = localStorage.getItem('token');
            $http.defaults.timeout = 100;
            if (token) $http.defaults.headers.common.token = token;
            var viewer = $viewer.get();
            if (window.nativeControl) {
                window.nativeControl.updateMenuViewer({
                    menu: 'viewer',
                    label: Helper.unescapeHTML(viewer.sFullName),
                    img: viewer.BigUserProfileImg_Url,
                });
                $rootScope.$on('viewer:update', function(e, data) {
                    window.nativeControl.updateMenuViewer({
                        menu: 'viewer',
                        label: Helper.unescapeHTML(data.sFullName),
                        img: data.BigUserProfileImg_Url
                    });
                });
                $rootScope.$on('user:updateAvatar', function(e, data) {
                    console.log('user:updateAvatar', data.user_image);
                    window.nativeControl.updateMenuViewer({
                        menu: 'viewer',
                        img: data.user_image
                    });
                });
            }
            // call from global/service/site
            $site.updatePerms();
        })();

        $ionicPlatform.ready(function() {
            ImgCache.init(function() {
                console.log('ImgCache init: success!');
            }, function() {
                console.log('ImgCache init: error! Check the log for errors');
            });
            // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
            // for form inputs)
            // https://github.com/driftyco/ionic-plugins-keyboard
            // if (window.cordova && window.cordova.plugins.Keyboard) {
            //     cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
            // }
            if (window.StatusBar) {
                // org.apache.cordova.statusbar required
                // StatusBar.styleDefault();
            }

            var token = localStorage.getItem('token');
            if (token) {
                if (ionic.Platform.isIOS()) {
                    $notification.doRegistrationWithAPNs();
                } else if (ionic.Platform.isAndroid()) {
                    $notification.doRegistrationWithGCM();
                }
            }

            (function() {
           if(window.admob) {
                admob.createBannerView({publisherId: "ca-app-pub-6253688624927798/2149460703"});
           }
            })();
        });

        document.addEventListener('backbutton', function(e) {
            var $body = $('body');
            if ($body.hasClass('loading-active') 
            || $body.hasClass('popup-open') 
            || $body.hasClass('action-sheet-open') 
            || $body.hasClass('modal-open') 
            || $body.hasClass('menu-open') 
            || $body.hasClass('popover-open')) {
                return;
            }
            $history.back();
            e.preventDefault();
            return false;
        }, false);

        $coreSettings.update();
        // init menu
        $coreMenu.update();
    });

    angular.module('myapp.controllers', []);
    angular.module('myapp.services', []);
    angular.module('myapp.directives', []);
    angular.module('myapp.filters', []);

    return angular.module('myapp');
});