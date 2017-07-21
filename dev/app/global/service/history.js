define([
    'global/site'
], function(Site) {

    return function($location, $rootScope, gettextCatalog, $timeout) {

        function _preparePath(path) {
            return '/' + path.replace(/^(\/)+/, '');
        }

        var $history = {
            _data: [],
            _maxLength: 3,
            _root: {
                path: _preparePath(Site.home),
                title: ''
            },
            isTransisting: false
        };

        $history.clear = function() {

            $history._data = [];
            $history._root = {
                path: _preparePath(Site.home),
                title: ''
            };

            return $history;
        };

        $history.back = function() {

            if ($history.isTransisting) {
                return;
            }

            $history.isTransisting = true;

            var last = $history._data.pop();

            if (last && last.path == $location.path()) {
                last = $history._data.pop();
            };

            if (last) {
                $location.path(last.path);
                $rootScope.$$phase || $rootScope.$apply();
            } else if ($history._root.path != $location.path()) {
                $location.path($history._root.path);
                $rootScope.$$phase || $rootScope.$apply();
            } else {
                ionic.Platform.exitApp();
                ionic.isWebViewDetached = true;
            }

            $timeout(function(){

                $history.isTransisting = false;
            }, 1000);
        };

        $history.go = function(seek) {

            var index = $history._data.length - 1 + seek;

            if (index < 0 || index >= len) {
                return;
            }

            $location.path($history._data[index].path);
            $rootScope.$$phase || $rootScope.$apply();
        };
        $history.backToRoot = function() {
            $location.path($history._root.path);
            $rootScope.$$phase || $rootScope.$apply();
        };
        $history.getRoot = function() {
            return $history._root;
        };
        $history.push = function(title, isRoot, replace) {

            var current = {
                path: $location.path(),
                title: gettextCatalog.getString(title) || ''
            };

            if (isRoot) {
                $history._root = current;
                $history._data = [];

                return $history;
            }

            var len = $history._data.length;

            if (!len || $history._data[len - 1].path != current.path) { // add
                if (len == $history._maxLength) {
                    $history._data.shift();
                }
            } else { // update
                $history._data.pop();
            }

            $history._data.push(current);

            return $history;
        };

        $history.splice = function() {

            var path = $location.path();

            var len = $history._data.length;

            if (!len) {
                return $history;
            }

            for (var i = len - 1; i >= 0; i--) {
                if ($history._data[i].path == path) {
                    $history._data.splice(i, 1);
                }
            }

            return $history;
        };

        $history.getPrev = function() {

            var len = $history._data.length;
            var prev = $history._data[len - 1];

            if (prev && prev.path == $location.path()) {
                prev = $history._data[len - 2];
            }

            if (prev) {
                return prev;
            }

            return $history._root;
        };

        $history.pop = function() {
            if ($history._data.length) {
                return $history._data.pop();
            } else {
                $history.clear();
            }
        };

        $history.getLevel = function() {
            return $history._data.length;
        };

        $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
            if (toState.history === false) {
                return;
            } else if (typeof(toState.history) == 'undefined') {
                $history.push();
            } else {
                $history.push(toState.history.title, toState.history.isRoot);
            }
        });

        return $history;
    };
});