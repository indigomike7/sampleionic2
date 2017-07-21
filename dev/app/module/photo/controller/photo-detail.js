define([
    'photo/model/photo',
    'photo/controller/photo-item'
], function(Model, Ctrl) {

    return function($scope,$q, $site, $http2, $state, $injector, $rootScope, $modal, gettext, gettextCatalog, $ionicSideMenuDelegate, $timeout, $coreSettings, $dislike) {

        if (window.StatusBar && ionic.Platform.isIOS()) {
            StatusBar.hide();
        }
        if (window.nativeControl) {
            window.nativeControl.lockMenu();
        }
        $ionicSideMenuDelegate.canDragContent(false);

        $scope.$on('$destroy',function(){
            if (window.StatusBar && ionic.Platform.isIOS()) {
                StatusBar.show();
            }
            if (window.nativeControl) {
                window.nativeControl.unlockMenu();
            }
            $ionicSideMenuDelegate.canDragContent(true);
        });

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        // using loop mode to stop loading others.


        var iPhotoId = $state.params.iPhotoId;
        var sParentType =  $state.params.sParentType || '';
        var iParentId = $state.params.iParentId || 0;
        var sItemType = $state.params.sItemType || $state.current.sItemType || 'album_photo';

        var iLoadLimit  = 10;
        var dataReady =false;
        var bCanLoadNext =  false;
        var bCanLoadPrev =  false;
        var bIsLoadingNext = false;
        var bIsLoadingPrev = false;
        var bInSlideChangeEnd = false;
        var bCanGoNext =  false;
        var bCanGoPrev = false;
        var iLoadOffset =  8; // remain 10 items to load next/prevs
        var aPrevItems = [];
        var aNextItems = [];

        var _debug = true;

        var oSwiper;

        // init swiper
        /**
         * @link http://www.idangero.us/sliders/swiper/api.php
         */
        $timeout(function(){
            var element = angular.element('#photo-slider');
            oSwiper = element.swiper({
                loop: true,
                noSwiping: true, // So swiping can be disabled with a class,
                initialSlide: 0,
                calculateHeight: true, // Set to true and Swiper will calculate container's height depending on slides content. Useful in repsonsive layout or when you don't know height of your slides (like with responsive images).
                onSlideChangeStart: onSlideChangeStart,
                onSlideChangeEnd: onSlideChangeEnd
            });
        },0);

        $scope.iPhotoId =  $state.params.iPhotoId;
        $scope.item = false;
        $scope.items  = []; // start item storage.
        $scope.sApiUrl =  'photo/fullphotoslide';

        // original of phpfox
        // sType: null,
        // iInDetails: 1,
        // serviceUrl: null,
        // iAmountOfPhoto: 100,
        // iItemId: null,
        // sModule: null

        $scope.aQuery = {
            sItemType: sItemType,
            iCurrentPhotoId: iPhotoId,

            sParentType: sParentType,
            iParentId: iParentId,

            sOrder: 'recent',
            iAmountOfPhoto: 100,
            iInDetails: 1,
            iNextPage:1,
            iPrevPage: 1,
            fields: 'id,type,imgNormal,imgFull,title,desc,stats,canEdit,canDelete,user'
        };

        $scope.wrapperHeightInPx = window.innerHeight + 'px';

        switch (sParentType) {
            case 'photo':
            case 'advancedphoto':
            case 'photo_album':
            case 'advancedphoto_album':
            case 'album':
                $scope.sApiUrl = 'photo/fullalbumslide';
                $scope.aQuery.iAlbumId = iParentId;
                break;
            case 'event_photo':
            case 'fevent_photo':
                $scope.sApiUrl = 'event/listphotos';
                $scope.aQuery.iEventId = iParentId;
                break;
            case 'profile_photo':
                $scope.sApiUrl = 'photo/fullphotoslide';
                $scope.aQuery.iUserId = iParentId;
                break;
            case 'my_photos':
                $scope.sApiUrl = 'photo/fullphotoslide';
                $scope.aQuery.sView = 'my';
                break;
            case 'user':
                $scope.sApiUrl = 'photo/onephotoslide';
                $scope.aQuery.iAmountOfPhoto = 1;
                $scope.aQuery.iUserId = iParentId;
                $scope.aQuery.sAction = 'current';
                break;
            default:
                break;
        }

        function loadInit(){

            $http2.get($scope.sApiUrl, $.extend({},$scope.aQuery, {}), {})
            .success(function(data){
                _debug && console.log('load slide data', data);
                if(data.error_code){
                    $modal.alert(gettextCatalog.getString('can not get data from server'));
                    $scope.goBack();
                }else{
                    for(index =0; index < data.length; ++index){
                        $scope.items.push($.extend({}, Model, data[index]));
                    }   
                }
                initSlides();
            })
            .error(function(){});
        }

        function loadNext(){
            if(bIsLoadingNext) return ;
            if(!bCanLoadNext) return;

            bIsLoadingNext = true;
          $http2.get($scope.sApiUrl, $.extend($scope.aQuery, {sAction:'next'}), {})
          .success(loadNextSuccess)
          .error($http2.defaultErrorHandler)
          .finally(function(){});
        };



        function loadPrev(){
            if(bIsLoadingPrev) return ;
            if(!bCanLoadPrev) return;

            bIsLoadingPrev = true;

            $http2.get($scope.sApiUrl, $.extend($scope.aQuery,{'sAction':'prev'}), {})
              .success(loadPrevSuccess)
              .error($http2.defaultErrorHandler)
              .finally(function(){});
        };

        function loadNextSuccess(data){
            if(data.error_code){
                $modal.alert(data.error_message);
                return ;
            }
            aNextItems = data;

            $scope.aQuery.iNextPage +=1;

            if(data.length < iLoadLimit){
                bCanLoadNext = false;
            }
            bIsLoadingNext = false;
            onSlideChangeEnd(oSwiper, 'next');
        };

        function loadPrevSuccess(data){
            if(data.error_code){
                $modal.alert(data.error_message);
                return ;
            }
            aPrevItems=  data;
            $scope.aQuery.iPrevPage +=1;

            if(data.length < iLoadLimit){
                bCanLoadPrev = false;
            }
            bIsLoadingPrev = false;
            onSlideChangeEnd(oSwiper, 'next');
        };

        function processItemQueues(){
            if(aNextItems.length){
                for(var index = 0; index<aNextItems.length; ++index){
                    $scope.items.push($.extend({}, Model, aNextItems[index]));
                }
                aNextItems = [];
            }

            if(aPrevItems.length){
                for(var index = 0; index<aPrevItems.length; ++index){
                    $scope.items.unshift($.extend({}, Model, aPrevItems[index]));
                }
                aPrevItems=[];
            }

        }

        function initSlides(){
            _debug &&  console.log('init slides', {
                iCurrentPhoto: iPhotoId,
                items: $scope.items,
            });

            var currentIndex =  $scope.items.findItemIndex(iPhotoId);

            // match data id for some issue.
            if(false === currentIndex){
                $modal.alert(gettextCatalog.getString('Photo not found'));
                return ;
            };


            var item  =  $scope.items[currentIndex];
            var activeSlide  =  oSwiper.activeSlide();

            $('.photo-slide-0')
                .attr('src', item.sPhotoUrl)
                .attr('item', item.getId())
                .data('item', item.getId());

            // add 3 param to prevent $digest already in progress
            onSlideChangeEnd(oSwiper, 'next');

        };

        function onSlideChangeStart(swiper, direction){
        }

        function onSlideChangeEnd(swiper, direction){

            if(bInSlideChangeEnd){
                processItemQueues();
                console.log('still in slide change end');
                return;
            }

            bInSlideChangeEnd = true;

            if($scope.items.length == 0){
                processItemQueues();
                // skip first init.
                bInSlideChangeEnd = false;
                return;
            }


            var activeSlide  = swiper.activeSlide();

            if(!activeSlide){

                bInSlideChangeEnd = false;
                return ;
            }

            var imgIndex =  activeSlide.data('index');
            var imgs     =  $('.photo-slide-' + imgIndex);
            var itemId   =  imgs.data('item');
            var nextIndex  = 0;
            var prevIndex =  1;

            // _debug && console.log('swipe img index', imgIndex, 'item id', itemId);

            if(typeof itemId =='undefined'  || !itemId){
                // process exception here
                bInSlideChangeEnd = false;
                return;
            }

            switch(parseInt(imgIndex)){
                case 0:
                    nextIndex =  1;
                    prevIndex = 2;
                    break;
                case 1:
                    nextIndex =  2;
                    prevIndex = 0;
                break;
                case 2:
                    nextIndex =  0;
                    prevIndex = 1;
                break;
                default:
                    console.warn(gettextCatalog.getString('can not get data from server'));
                    // $scope.goBack();
            };

            var currentItemIndex  =  $scope.items.findItemIndex(itemId);

            if(currentItemIndex === false){
                $modal.alert(gettextCatalog.getString('Photo is deleted'));
            }

            var item = $scope.items[currentItemIndex];

            // bind to initial.
            // reset show full description
            $scope.bShowFullDescription = false;
            $scope.item  =  item;


            var prevId  = 0;
            var nextId = 0;
            if(currentItemIndex > 0){
                var prevItem = $scope.items[currentItemIndex -1];
                prevId = prevItem.getId();
                $('.photo-slide-' + prevIndex)
                    .attr('src',prevItem.sPhotoUrl)
                    .attr('item', prevItem.getId())
                    .data('item', prevItem.getId());
                // enable swipe prev
                swiper.params.swipeToPrev = true;
                bCanGoPrev= true;
            }else{
                // disable swipe prev
                bCanGoPrev= false;
                swiper.params.swipeToPrev = false;
            }


            if(currentItemIndex +1 < $scope.items.length){
                var nextItem = $scope.items[currentItemIndex +1];
                $('.photo-slide-' + nextIndex)
                    .attr('src',nextItem.sPhotoUrl)
                    .attr('item', nextItem.getId())
                    .data('item', nextItem.getId());
                nextId = nextItem.getId();
                swiper.params.swipeToNext = true;
                bCanGoNext = true;
            }else{
                swiper.params.swipeToNext = false;
                bCanGoNext = false;

            }

            _debug &&  console.log('item=', item.getId(),currentItemIndex,'/length', $scope.items.length, 'prev=', prevId, 'next=', nextId);

            if(currentItemIndex < iLoadOffset && bCanLoadPrev){
                loadPrev();
            }else if($scope.items.length - currentItemIndex < iLoadOffset && bCanLoadNext){
                loadNext();
            }

            bInSlideChangeEnd = false;

            processItemQueues();

            _enableZoom(swiper);

            // $scope.$$phrase || $scope.$apply();
        };

        var _enableZoom = function(swiper) {

            if ($scope.scroller) {
                $scope.scroller.destroy();
                delete($scope.scroller);
            }

            var activeSlide = swiper.activeSlide();
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

        $scope.onToggleShowFullDescription = function(){
            $scope.bShowFullDescription = true;
        };

        /**
         * make apply more frenquency
         */
        var iIntervalId  = setInterval(function(){ $scope.$$phase || $scope.$apply(); }, 250);

        /**
         * process key orientation changed we need to change line height.
         */
        $scope.updateHeight = function(e) {
            // this feature can not work out device.
            $timeout(function() {

                console.log('updateHeight window.innerHeight', window.innerHeight);

                var ngWrapper = angular.element('.photo_display');
                ngWrapper.removeClass('swiper-no-swiping');
                ngWrapper.css({
                    height: window.innerHeight + 'px'
                });

                oSwiper.reInit();

                _enableZoom(oSwiper);
            }, 500);
        };

        window.addEventListener('orientationchange', $scope.updateHeight);

        $scope.$on('$destroy', function() {
           window.clearInterval(iIntervalId);
           window.removeEventListener('orientationchange', $scope.updateHeight);
        });

        $scope.onItemSetting = $scope._setting($scope, function(){
            var btns = [];
            var item = $scope.item;
            
            if ($coreSettings.get('like_allow_dislike')) {

                if ($scope.item.getTotalDislike() > 0) {
                    btns.push({
                        text: $dislike.getDislikeStat($scope.item),
                        action: $scope.onViewDisliked
                    });
                }

                if ($scope.item.canDislike()) {
                    btns.push({
                        text: $scope.item.isDisliked() ? gettextCatalog.getString('Remove Dislike') : gettextCatalog.getString('Dislike'),
                        action: $scope.onItemDislike
                    });
                }
            }

            if(item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit Photo'),
                    action: function(){
                        $scope.onItemEdit();
                    }
                });
            }
                
            if(item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete Photo'),
                    action: function(){
                        $scope.onItemDelete();
                    },
                    destructive: true,
                });
            }

            if(item.bCanMakeProfilePicture){
                btns.push({
                    text: gettextCatalog.getString('Make Profile Photo'),
                    action: function(){
                        $scope.onItemMakeProfilePicture();
                    },
                });
            }
            
            if(!item.isOwner()){
                btns.push({
                    text: gettextCatalog.getString('Report this photo'),
                    action: function(){
                        $scope.onItemReport();
                    }
                });
            }
            return btns;
        });

        loadInit();
    };
});
