define(function() {
    return function($http2, $http, $site, $rootScope, $httpCache) {
        var self = this;

        this.getLastCssBuildNumber = function() {
            return localStorage.getItem('cssBuildNumber');
        };

        this.setLastCssBuildNumber = function(val) {
            localStorage.setItem('cssBuildNumber', val);
        };

        this.getLastCss = function() {
            return localStorage.getItem('css');
        };

        this.setLastCss = function(val) {
            try {
                localStorage.setItem('css', val);
            } catch(e) {
                // try clear cache
                $httpCache.clear('default');
                localStorage.setItem('css', val);
            }
        };

        this.loadCss = function() {
            if ($site.useLocalCss) {
                return self.getLocalCss();
            }

            var onSuccess = function(data) {
                if (data.error_code) {
                    $site.debug > 2 && console.warn('core/css_build_number', arguments);
                    return self.cssReady();
                }

                var lastCssBuildNumber = self.getLastCssBuildNumber();
                if ($site.cacheCss && data == lastCssBuildNumber) {
                    return self.cssReady();
                }

                self.getNewCss(data);
            };

            var onError = function() {
                $site.debug > 2 && console.warn('core/css_build_number', arguments);
                self.cssReady();
            };

            $http2.post('core/css_build_number').success(onSuccess).error(onError);
        };

        this.getNewCss = function(buildNumber) {
            var onSuccess = function(data) {
                if (data.error_code) {
                    return $site.debug > 2 && console.warn('getNewCss', arguments);
                }

                self.setLastCssBuildNumber(buildNumber);
                self.setLastCss(data);
                self.cssReady();
            };

            var onError = function() {
                $site.debug > 2 && console.warn('getNewCss', arguments);
                self.cssReady();
            };

            var cssFile = $site.cssUrl + $site.theme + '_' + buildNumber + '.css';
            $http.get(cssFile).success(onSuccess).error(onError);
        };

        this.getLocalCss = function() {
            var onSuccess = function(data) {
                if (data.error_code) {
                    return $site.debug > 2 && console.warn('getLocalCss', arguments);
                }

                self.setLastCss(data);
                self.cssReady();
            };

            var onError = function() {
                $site.debug > 2 && console.warn('getLocalCss', arguments);
                self.cssReady();
            };

            var cssFile = './css/main.css';
            $http.get(cssFile).success(onSuccess).error(onError);
        };

        this.cssReady = function() {
            $rootScope.$broadcast('cssReady');
        };

        return this;
    };
});
