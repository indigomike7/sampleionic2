define([
    'angular',
    'global/base/BaseController'
], function(angular, BaseController) {
    return function($scope, $injector, $coreSettings, $site, gettext, gettextCatalog, $state, $timeout, $ionicScrollDelegate, $ionicSideMenuDelegate) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.unlockMenu = function() {
            $ionicSideMenuDelegate.canDragContent(true);
            if (window.nativeControl) {
                window.nativeControl.unlockMenu();
            }
        };

        $scope.unlockMenu();

        $scope.sHashTag = $state.params.hashtag || '';

        $scope.searchFeeds = {
            iAmountOfFeed: 10,
            iMinId: 0,
            iMaxId: 0,
            sHashTag: $scope.sHashTag
        };

        $scope.chatModule = $coreSettings.get('chat_module');

        $scope.$on('coreSettings:update', function(e, data) {
            $scope.chatModule = $coreSettings.get('chat_module');
        });

        $scope.isScrolling = false;
        $scope.lastScrollTop = 0; // for iOS
        $scope.stateName = $state.current.name;

        $scope.onStateChangeSuccess = function(event, toState, toParams, fromState, fromParams) {
            $site.debug > 2 && console.log('onStateChangeSuccess', toState);
            if (toState.name == $scope.stateName) {
                $scope.$broadcast('state-change-success');
            }
        };

        $scope.$on('$stateChangeSuccess', $scope.onStateChangeSuccess);

        $scope.onScrollComplete = function() {
            $site.debug > 2 && console.log('onScrollComplete');
            $scope.$broadcast('scroll-complete');
        };

        $scope.onIOSScroll = function() {
            $scope.isScrolling = true;
        };

        $scope.onIOSScrollComplete = function() {
            setTimeout(function() {
                $scope.isScrolling = false;
            }, 250);

            var $handle = $ionicScrollDelegate.$getByHandle('feed-scroll-content');
            var newScrollTop = $handle.getScrollPosition().top;
            if (newScrollTop != $scope.lastScrollTop) {
                $scope.lastScrollTop = newScrollTop;
                $scope.onScrollComplete();
            }
        };

        $scope.onAndroidScroll = function() {
            $scope.isScrolling = true;
            clearTimeout($.data(this, 'scrollTimer'));
            $.data(this, 'scrollTimer', setTimeout(function() {
                // Haven't scrolled in 250 ms
                $scope.isScrolling = false;
                $scope.onScrollComplete();
            }, 250));
        };

        if (ionic.Platform.isAndroid()) {
            $timeout(function() {
                $('#feed-scroll-content').on('scroll', $scope.onAndroidScroll);
            }, 500);

            $scope.$on('$destroy', function() {
                $('#feed-scroll-content').off('scroll', $scope.onAndroidScroll);
            });
        }
    };
});