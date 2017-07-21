define(function() {
    return function($scope, $site, $http, $location, $viewer, $coreTheme, $ionicViewSwitcher, $ionicSideMenuDelegate) {
        $scope.lockMenu = function() {
            $ionicSideMenuDelegate.canDragContent(false);
            window.nativeControl && window.nativeControl.lockMenu();
        };

        $scope.unlockMenu = function() {
            $ionicSideMenuDelegate.canDragContent(true);
            window.nativeControl && window.nativeControl.unlockMenu();
        };

        $scope.init = function() {
            $scope.lockMenu();
            $scope.loadCss();
        };

        $scope.loadCss = function() {
            $scope.cssError = '';
            $scope.bIsLoadingCss = true;
            $coreTheme.loadCss();
        };

        $scope.onCssReady = function() {
            $scope.bIsLoadingCss = false;

            var css = $coreTheme.getLastCss();
            if (!css) {
                return $scope.onCssError();
            }

            angular.element('#main_css').remove();
            angular.element('head').append('<style type="text/css" id="main_css">' + css + '</style>');
            $scope.onCssSuccess();
        };

        $scope.onCssSuccess = function() {
            $scope.unlockMenu();

            $ionicViewSwitcher.nextTransition('none');

            var token = localStorage.getItem('token');
            if (!token) {
                return $location.path('app/login');
            }

            $http.defaults.headers.common.token = localStorage.getItem('token');

            // check subscription
            if ($viewer.get('bSubscription') === false) {
                return $location.path('app/subscriptions/browse');
            }

            // for delay redirect in other services
            if ($site.delayPath) {
                $location.path($site.delayPath);
                return $site.delayPath = null;
            }

            $location.path($site.home);
        };

        $scope.onCssError = function() {
            $scope.cssError = !$site.isOnline ? 'no_connection' : 'data_error';
        };

        $scope.$on('cssReady', $scope.onCssReady);

        $scope.init();
    };
});
