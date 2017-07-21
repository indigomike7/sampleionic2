define([
    'global/base/BaseController',
    'marketplace/model/listing',
    'photo/model/photo'
], function(BaseController, ListingModel, PhotoModel) {

    return function($scope, $state, $injector, $http2, $site, $modal, gettext, gettextCatalog, $location, $timeout,$ionicSideMenuDelegate) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        /**
         * Disable status bar and drag content
         */
        if (window.StatusBar && ionic.Platform.isIOS()) {
            StatusBar.hide();
        }
        if (window.nativeControl) {
            window.nativeControl.lockMenu();
        }
        $ionicSideMenuDelegate.canDragContent(false);

        $scope.$on('$destroy', function() {
            if (window.StatusBar && ionic.Platform.isIOS()) {
                StatusBar.show();
            }
            if (window.nativeControl) {
                window.nativeControl.unlockMenu();
            }
            $ionicSideMenuDelegate.canDragContent(true);
        });

        /**
         * Fetch data
         */
        $scope.obj = $.extend({}, ListingModel, {
            iListingId: $state.params.id
        });

        $scope.initPhotoId = $state.params.photoId;
        $scope.items = [];

        $scope.fetchData = function() {

            var postData = {
                iListingId: $scope.obj.getId()
            };

            $http2.get('marketplace/detail', postData)
                .success($scope.fetchDataSuccess)
                .error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.obj, data);

            $scope.items = $.map($scope.obj.getPhotos(), function(photo) {
                return $.extend({}, PhotoModel, photo);
            });

            $scope.dataReady = true;
            
            $timeout($scope.initSwiper, 300);
        };

        $scope.fetchDataError = function() {

            console.warn('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        /**
         * Swiper
         */
        $scope.wrapperHeightInPx = window.innerHeight + 'px';

        $scope.initSwiper = function() {
            
            var element = angular.element('#photo-slider');
            var initialSlide = $scope.findItemIndex($scope.items, $scope.initPhotoId, 'iImageId');
            
            $scope.oSwiper = element.swiper({
                loop: false,
                noSwiping: true, // So swiping can be disabled with a class,
                initialSlide: initialSlide,
                calculateHeight: true, // Set to true and Swiper will calculate container's height depending on slides content. Useful in repsonsive layout or when you don't know height of your slides (like with responsive images).
                onSlideChangeStart: function() {},
                onSlideChangeEnd: function() {
                    $scope.enableZoom();
                }
            });
        };

        $scope.findItemIndex = function(items, val, key) {

            key = key || 'id';

            for (var i = 0; i < items.length; i++) {
                if (items[i][key] == val) {
                    return i;
                }
            };

            return -1;
        };

        $scope.enableZoom = function() {

            var activeSlide = $scope.oSwiper.activeSlide();
            var inScroll = $(activeSlide).find('.photo_display').get(0);

            $scope.scroller = new iScroll(inScroll, {
                hideScrollbar: true,
                hScrollbar: false,
                vScrollbar: false,
                zoom: true,
                // topOffset: 0,
                // So Swiper will not swipe/slide when zooming is enabled
                onZoomEnd: function(e) {

                    this.fixOrigin();
                    this.refresh();

                    if (parseInt(this.scale) == 1) {
                        $(this.wrapper).removeClass('swiper-no-swiping');

                        // redraw dom
                        $(this.scroller).hide().show(0);
                    }
                },
                // Since the images are inside of the swiper slide it
                // got a huge left offset, but the offset isn't really
                // part of the page/image since the page is completely
                // shown within the viewable area of the viewport. So
                // simply remove the wrapperOffsetLeft from the
                // calculation and be happy.
                //
                // touchstart: When pinch-zooming
                // touchend: When double-tap zooming
                onZoomStart: function(e) {

                    $(this.wrapper).addClass('swiper-no-swiping');

                    if(e.type === 'touchstart') {
                        this.originX = Math.abs(e.touches[0].pageX + e.touches[1].pageX) / 2 - this.x;
                    } else if(e.type === 'touchend') {
                        this.wrapperOffsetLeft = 0;
                    }
                }
            });

            $scope.scroller.fixOrigin();
            $scope.scroller.refresh();
        };

        $scope.updateHeight = function(e) {
            // this feature can not work out device.
            $timeout(function() {

                console.log('updateHeight window.innerHeight', window.innerHeight);

                var ngWrapper = angular.element('.photo_display');
                ngWrapper.removeClass('swiper-no-swiping');
                ngWrapper.css({
                    height: window.innerHeight + 'px'
                });

                $scope.oSwiper.reInit();

                $scope.enableZoom();
            }, 500);
        };

        window.addEventListener('orientationchange', $scope.updateHeight);

        $scope.$on('$destroy', function() {
           window.removeEventListener('orientationchange', $scope.updateHeight);
        });

        $scope.fetchData();
    };
});