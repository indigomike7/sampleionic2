define([
    'marketplace/controller/listing-item',
    'marketplace/model/listing',
    'photo/model/photo'
], function(ListingItemView, ListingModel, PhotoModel) {

    return function($scope, $state, $injector, $http2, $site, $modal, gettext, gettextCatalog, $location, $timeout) {

        $injector.invoke(ListingItemView, this, {
            $scope: $scope
        });

        $scope.item = $.extend({}, ListingModel, {
            iListingId: $state.params.id,
            sModelType: $state.current.sModelType || 'marketplace'
        });

        $scope.fetchData = function() {

            var postData = {
                iListingId: $scope.item.getId(),
                sModelType: $scope.item.getType()
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

            $.extend($scope.item, data);

            $scope.itemPhotos = $.map($scope.item.getPhotos(), function(photo) {
                return $.extend({}, PhotoModel, photo);
            });

            $scope.dataReady = true;

            $timeout(function() {
                $scope.calculatePhotoMoreBtn();
            }, 300);
        };

        $scope.fetchDataError = function() {

            console.warn('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.doDeleteSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $location.path('app/listings/my');
        };

        $scope.onContactSeller = function() {

            if (!$scope.item.canSendMessage()) {
                return $modal.alert(gettextCatalog.getString('Unable to send a private message to this user at the moment.'));
            }

            $location.path('app/messages/add/user/' + $scope.item.getPosterId() + '/' + encodeURI($scope.item.getPosterTitle()));
        };

        $scope.calculatePhotoMoreBtn = function() {

            if ($scope.isShowMorePhotos) {
                return;
            }

            var $holder = angular.element('.listing-photo');
            var $photoItem = angular.element('.item-listing-photo');

            var holderWidth = window.innerWidth - parseInt($holder.css('padding-left'));
            var photoItemWidth = parseInt($photoItem.css('width')) + parseInt($photoItem.css('margin-right'));
            var maxPhotoInline = Math.floor(holderWidth / photoItemWidth);

            $scope.photoMoreBtnIndex = ($scope.itemPhotos.length > maxPhotoInline) ? (maxPhotoInline - 1) : -1;
        };

        window.addEventListener('orientationchange', $scope.calculatePhotoMoreBtn);

        $scope.$on('$destroy', function() {

            window.removeEventListener('orientationchange', $scope.calculatePhotoMoreBtn);
        });

        $scope.showMorePhotos = function() {

            $timeout(function() {
                $scope.photoMoreBtnIndex = -1;
                $scope.isShowMorePhotos = true;
            }, 200);
        };

        $scope.viewPhotoDetail = function($index) {

            if ($scope.photoMoreBtnIndex != $index) {
                $location.path('app/listing/' + $scope.item.getId() + '/' + $scope.itemPhotos[$index].iImageId);
            }
        };

        $scope.fetchData();
    };
});