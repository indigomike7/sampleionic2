define([
    'global/base/BaseController',
    'text!tpl/photo/photo-view.html'
], function(BaseController, PhotoViewTpl) {

    return function($scope, $injector, $ionicModal, $timeout, $ionicSideMenuDelegate) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.$on('$destroy',function() {
            if (window.nativeControl) {
                window.nativeControl.unlockMenu();
            }
            $ionicSideMenuDelegate.canDragContent(true);
        });

        $scope.wrapperHeightInPx = window.innerHeight + 'px';

        $scope.enableZoom = function() {

            if ($scope.scroller) {
                $scope.scroller.destroy();
                delete($scope.scroller);
            }

            var inScroll = $('.photo_display').get(0);

            $scope.scroller = new iScroll(inScroll, {
                hideScrollbar: true,
                hScrollbar: false,
                vScrollbar: false,
                zoom: true,
                bounce: true,
                topOffset: 0,
                // topOffset: 100,
                // touchstart: When pinch-zooming
                // touchend: When double-tap zooming
                onZoomStart: function(e) {
                    if (e.type === 'touchstart') {
                        this.originX = Math.abs(e.touches[0].pageX + e.touches[1].pageX) / 2 - this.x;
                    } else if (e.type === 'touchend') {
                        this.wrapperOffsetLeft = 0;
                    }
                },
                onZoomEnd: function(e) {

                    this.fixOrigin();
                    this.refresh();

                    if (parseInt(this.scale) == 1) {
                        // redraw dom
                        $(this.scroller).hide().show(0);
                    }
                }
            });

            $scope.scroller.fixOrigin();
            $scope.scroller.refresh();
        };

        $scope.showView = function() {

            if (window.StatusBar && ionic.Platform.isIOS()) {
                StatusBar.hide();
            }

            $scope.photoViewModal = $ionicModal.fromTemplate(PhotoViewTpl, {
                scope: $scope
            });

            $scope.photoViewModal.show();

            if (window.nativeControl) {
                window.nativeControl.lockMenu();
            }
            $ionicSideMenuDelegate.canDragContent(false);
        
            $scope.updateHeight();
        };

        $scope.hideView = function() {

            $scope.photoViewModal.remove();

            if (window.nativeControl) {
                window.nativeControl.unlockMenu();
            }
            $ionicSideMenuDelegate.canDragContent(true);
            
            if (window.StatusBar && ionic.Platform.isIOS()) {
                StatusBar.show();
            }
        };

        /**
         * process key orientation changed we need to change line height.
         */
        $scope.updateHeight = function(e) {
            // this feature can not work out device.
            $timeout(function() {
                
                console.log('updateHeight window.innerHeight', window.innerHeight);

                var ngWrapper = angular.element('.photo_display');
                ngWrapper.css({
                    height: window.innerHeight + 'px'
                });

                if (!$scope.disableZoom) {
                    $scope.enableZoom();
                }
            }, 500);
        };
        
        window.addEventListener('orientationchange', $scope.updateHeight);

        $scope.$on('$destroy', function() {

            if (window.StatusBar && ionic.Platform.isIOS()) {
                StatusBar.show();
            }

            $scope.photoViewModal && $scope.photoViewModal.remove();
            window.removeEventListener('orientationchange', $scope.updateHeight);
        });
    };
});