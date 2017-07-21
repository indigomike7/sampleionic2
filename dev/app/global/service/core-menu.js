define(function() {
    return function($rootScope, $http2, $interval, $site, gettextCatalog) {
        var $coreMenu = {
            interval: null,
            updateDelay: 600000, // 10 mins
            errorDelay: 30000, // 30 secs
            storage: [],
            localMenu: [
                {
                    "name": "activity",
                    "label": gettextCatalog.getString("News Feed"),
                    "icon": "ion-card",
                    "href": "/app/newsfeed"
                }, {
                    "name": "blog",
                    "label": gettextCatalog.getString("Blogs"),
                    "icon": "ion-ios-paper-outline",
                    "href": "/app/blogs"
                }, {
                    "name": "directory",
                    "label": gettextCatalog.getString("Business Directory"),
                    "icon": "ion-cash",
                    "href": "/app/directory"
                }, {
                    "name": "event",
                    "label": gettextCatalog.getString("Events"),
                    "icon": "ion-ios-calendar-outline",
                    "href": "/app/events"
                }, {
                    "name": "forum",
                    "label": gettextCatalog.getString("Forums"),
                    "icon": "ion-ios-bookmarks-outline",
                    "href": "/app/forums"
                }, {
                    "name": "friend",
                    "label": gettextCatalog.getString("Friends"),
                    "icon": "ion-ios-people-outline",
                    "href": "/app/friends"
                }, {
                    "name": "marketplace",
                    "label": gettextCatalog.getString("Marketplace"),
                    "icon": "ion-ios-briefcase-outline",
                    "href": "/app/listings"
                }, {
                    "name": "member",
                    "label": gettextCatalog.getString("Members"),
                    "icon": "ion-ios-personadd-outline",
                    "href": "/app/members"
                }, {
                    "name": "message",
                    "label": gettextCatalog.getString("Messages"),
                    "icon": "ion-ios-email-outline",
                    "href": "/app/messages"
                }, {
                    "name": "music",
                    "label": gettextCatalog.getString("Music"),
                    "icon": "ion-ios-musical-notes",
                    "href": "/app/music_songs"
                }, {
                    "name": "page",
                    "label": gettextCatalog.getString("Pages"),
                    "icon": "ion-ios-copy-outline",
                    "href": "/app/pages"
                }, {
                    "name": "photo",
                    "label": gettextCatalog.getString("Photos"),
                    "icon": "ion-ios-photos-outline",
                    "href": "/app/albums"
                }, {
                    "name": "poll",
                    "label": gettextCatalog.getString("Polls"),
                    "icon": "ion-ios-analytics-outline",
                    "href": "/app/polls"
                }, {
                    "name": "quiz",
                    "label": gettextCatalog.getString("Quizzes"),
                    "icon": "ion-ios-help-outline",
                    "href": "/app/quizzes"
                }, {
                    "name": "setting",
                    "label": gettextCatalog.getString("Settings"),
                    "icon": "ion-ios-gear-outline",
                    "href": "/app/settings"
                }, {
                    "name": "subscription",
                    "label": gettextCatalog.getString("Memberships"),
                    "icon": "ion-ribbon-b",
                    "href": "/app/subscriptions"
                }, {
                    "name": "video",
                    "label": gettextCatalog.getString("Videos"),
                    "icon": "ion-ios-film-outline",
                    "href": "/app/videos"
                }, {
                    "name": "videochannel",
                    "label": gettextCatalog.getString("Video Channel"),
                    "icon": "ion-ios-videocam",
                    "href": "/app/videochannel"
                }
            ]
        };

        $coreMenu.set = function(aVal) {
            $coreMenu.storage = aVal || [];
            $rootScope.$broadcast('coreMenu:update', $coreMenu.storage);
        };

        $coreMenu.get = function() {
            return $coreMenu.storage;
        };

        $coreMenu.update = function() {

            var successCb = function(data) {
                if (data.error_code) {
                    return $coreMenu._updateError.apply($coreMenu, arguments);
                }
                $coreMenu._updateSuccess.apply($coreMenu, arguments);
            };
            var errorCb = function() {
                $coreMenu._updateError.apply($coreMenu, arguments);
            };
            $http2.get('core/leftMenu').success(successCb).error(errorCb);
        };

        $coreMenu._updateSuccess = function(data) {
            $site.debug > 2 && console.log('$coreMenu.update', arguments);
            $coreMenu.set(data);
            $coreMenu._setInterval($coreMenu.updateDelay);
        };

        $coreMenu._updateError = function() {
            console.warn('$coreMenu.update', arguments);
            $coreMenu._setInterval($coreMenu.errorDelay);
        };

        $coreMenu._setInterval = function(delay) {
            $coreMenu.interval && $interval.cancel($coreMenu.interval);
            $coreMenu.interval = $interval($coreMenu.update, delay);
        };

        $rootScope.$on('user:login', $coreMenu.update);

        return $coreMenu;
    };
});