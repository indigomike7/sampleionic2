define(function() {
    return function($scope, $element, $timeout) {
        /**
         * Use to listen drag event
         */
        $scope.isListeningDrag = false;
        $scope.maxDragOffset = Math.floor(window.innerHeight / 3);
        window.addEventListener('orientationchange', function() {
            $scope.maxDragOffset = Math.floor(window.innerHeight / 3);
        });
        $scope.resetDragData = function() {
            $scope.dragOffset = 0;
            $scope.dragProgress = 0;
            $scope.touchData = {};
        };
        this.setRefresher = function(refresherScope, refresherElement) {
            $scope.activatePullToRefresh(function() {
                // activateCallback
                if (!$scope.isRefreshActivate) {
                    $scope.isRefreshActivate = true;
                    refresherElement.classList.add('active');
                }
                refresherScope.$onPulling && refresherScope.$onPulling();
            }, function() {
                // deactivateCallback
                $scope.isListeningDrag = false;
                $timeout(function() {
                    $scope.isRefreshActivate = false;
                    $scope.resetDragData();
                    refresherElement.classList.remove('active');
                    refresherElement.classList.remove('refreshing');
                    refresherElement.classList.remove('refreshing-tail');
                    refresherElement.classList.add('invisible');
                }, 300);
            }, function() {
                // startCallback
                $scope.isListeningDrag = false;
                refresherElement.classList.add('refreshing');
                refresherScope.$onRefresh && refresherScope.$onRefresh();
            }, function() {
                // showCallback
                refresherElement.classList.remove('invisible');
            }, function() {
                // hideCallback
                refresherElement.classList.add('invisible');
            }, function() {
                // tailCallback
                refresherElement.classList.add('refreshing-tail');
            });
        };
        this.finishPullToRefresh = function() {
            $scope.__refreshDeactivate();
        };
        /**
         * Activates pull-to-refresh. A special zone on the top of the list to start a list refresh whenever
         * the user event is released during visibility of this zone. This was introduced by some apps on iOS like
         * the official Twitter client.
         *
         * @param height {Integer} Height of pull-to-refresh zone on top of rendered list
         * @param activateCallback {Function} Callback to execute on activation. This is for signalling the user about a refresh is about to happen when he release.
         * @param deactivateCallback {Function} Callback to execute on deactivation. This is for signalling the user about the refresh being cancelled.
         * @param startCallback {Function} Callback to execute to start the real async refresh action. Call {@link #finishPullToRefresh} after finish of refresh.
         * @param showCallback {Function} Callback to execute when the refresher should be shown. This is for showing the refresher during a negative scrollTop.
         * @param hideCallback {Function} Callback to execute when the refresher should be hidden. This is for hiding the refresher when it's behind the nav bar.
         * @param tailCallback {Function} Callback to execute just before the refresher returns to it's original state. This is for zooming out the refresher.
         */
        $scope.activatePullToRefresh = function(activateCallback, deactivateCallback, startCallback, showCallback, hideCallback, tailCallback) {
            $scope.__refreshActivate = activateCallback;
            // function() {
            //     ionic.requestAnimationFrame(activateCallback);
            // };
            $scope.__refreshDeactivate = deactivateCallback;
            // function() {
            //     ionic.requestAnimationFrame(deactivateCallback);
            // };
            $scope.__refreshStart = startCallback;
            // function() {
            //     ionic.requestAnimationFrame(startCallback);
            // };
            $scope.__refreshShow = showCallback;
            // function() {
            //     ionic.requestAnimationFrame(showCallback);
            // };
            $scope.__refreshHide = hideCallback;
            // function() {
            //     ionic.requestAnimationFrame(hideCallback);
            // };
            $scope.__refreshTail = tailCallback;
            // function() {
            //     ionic.requestAnimationFrame(tailCallback);
            // };
        $scope.resetDragData();
        $scope.onGestures();
        $scope.$on('$destroy', $scope.offGestures);
        };
        $scope.onGestures = function() {
            ionic.on('touchstart', $scope.onTouchStart, $element[0]);
            ionic.on('touchmove', $scope.onTouchMove, $element[0]);
            ionic.on('touchend', $scope.onTouchEnd, $element[0]);
            ionic.on('touchcancel', $scope.onTouchEnd, $element[0]);
            ionic.on('touchleave', $scope.onTouchEnd, $element[0]);
        };
        $scope.offGestures = function() {
            ionic.off('touchstart', null, $element[0]);
            ionic.off('touchmove', null, $element[0]);
            ionic.off('touchend', null, $element[0]);
            ionic.off('touchcancel', null, $element[0]);
            ionic.off('touchleave', null, $element[0]);
        };
        $scope.onTouchStart = function(e) {
            // console.log('onTouchStart', e.changedTouches[0].pageX, e.changedTouches[0].pageY);
            if ($scope.isRefreshActivate) {
                return;
            }
            if (window.scrollY == 0) {
                $scope.isListeningDrag = true;
                $scope.touchData.firstY = $scope.touchData.lastY = e.changedTouches[0].pageY;
                $scope.__refreshShow();
            }
        };
        $scope.onTouchMove = function(e) {
            // console.log('onTouchMove', e.changedTouches[0].pageX, e.changedTouches[0].pageY);
            if (!$scope.isListeningDrag) {
                return;
            }
            if (window.scrollY != 0 || e.changedTouches[0].pageY < $scope.touchData.firstY) {
                return $scope.__refreshDeactivate();
            }
            e.preventDefault();
            $scope.touchData.lastY = e.changedTouches[0].pageY;
            $scope.dragOffset = $scope.touchData.lastY - $scope.touchData.firstY;
            $scope.dragProgress = Math.pow($scope.dragOffset / $scope.maxDragOffset, 3) * 100;
            $scope.$$phase || $scope.$apply();
            if ($scope.dragOffset >= 10) {
                $scope.__refreshActivate();
            }
            if ($scope.dragOffset >= $scope.maxDragOffset) {
                $scope.__refreshStart();
            }
        };
        $scope.onTouchEnd = function(e) {
            // console.log('onTouchEnd', e.changedTouches[0].pageX, e.changedTouches[0].pageY);
            if ($scope.dragOffset < $scope.maxDragOffset) {
                $scope.__refreshDeactivate();
            }
        };
    };
});