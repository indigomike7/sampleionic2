define([
    'global/site'
], function(Site) {
    return function($http, $state, $rootScope, $timeout) {

        Site.getHomePath = function() {
            return Site.home;
        };

        //store member permission data
        //store user group data.
        var _allowData = {};

        // fech from ache
        var cached = localStorage.getItem('_allowData');

        // parse storage data.
        if (cached) {
            _allowData = JSON.parse(cached) || {};
        }

        /**
         *
         * @param String action
         * @return 1|0
         */
        Site.getPerm = function(action) {
            if (typeof action == 'undefined') {
                return false;
            }
            return _allowData[action] ? true : false;
        };

        var _updates = new Array();


        Site.updatePerms = function() {

            // skip this method on false.
            if (Site.isOnline == false) {
                return;
            }

            $http.post(Site.getApiUrl('core/allows'))
                .success(function(data) {
                    if (data.error_code) {
                        console.warn('can not upate permission core/allows', data);
                    } else {
                        _allowData = data;
                        // cached to later use.
                        localStorage.setItem('_allowData', JSON.stringify(data));
                    }
                });
        };

        Site.requirePerm = function(resource) {
            if (false == Site.getPerm(resource)) {
                $state.go('app.PrivatePage');
            }
        };


        Site._dump = function() {
            console.log(_allowData);
        };

        // help to debug
        if (Site.debug > 2) {
            window._dumpPerm = Site._dump;
        }

        /**
         * interval update permissions
         */
        $timeout(Site.updatePerms, 500);

        window.setInterval(function() {
            Site.updatePerms();
        }, Site.cachedSettingInterval);

        $rootScope.$on('user:login', function() {
            $timeout(Site.updatePerms, 500);
        });

        return Site;
    };
});