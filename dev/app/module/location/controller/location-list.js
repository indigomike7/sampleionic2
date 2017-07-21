define([
    'global/base/ListController',
    'location/model/location'
], function(ListCtrl, LocationModel) {

    return function($scope, $injector, $http, $modal, gettext, gettextCatalog, $site, $http2) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            apiUrl: 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?',
            noMoreText: gettextCatalog.getString('No more locations'),
            itemModel: LocationModel,
            listData: {
                key: $site.googleApiKey,
                sensor: true,
                radius: 2000
            }
        });

        $scope.getCoords = function() {

            var success = function(position) {
                $scope.$$phase || $scope.$apply();
                $scope.coords = position.coords;
                $scope.canLoadMore = true;
                $scope.loadMore();
            };

            var error = function(err) {
                $scope.canLoadMore = false;
                $scope.hideLocationListModal();
                $modal.alert(err.message || gettextCatalog.getString('Device cannot get your current position'));
            };

            navigator.geolocation.getCurrentPosition(success, error, {
                enableHighAccuracy: false
            });
        };

        $scope.getInnerWidth = function() {

            return window.innerWidth;
        };

        $scope.loadMore = function() {

            if (!$scope.coords) {
                $scope.canLoadMore = false;
                return $scope.getCoords();
            }

            $scope.listData.location = $scope.coords.latitude + ',' + $scope.coords.longitude;

            var apiUrl = $scope.apiUrl + $site.buildQueryString($scope.listData);

            $http.get(apiUrl)
            .success($scope.loadMoreSuccess)
            .error($scope.loadMoreError)
            .finally(function() {
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
        };

        $scope.loadMoreSuccess = function(data) {

            if (!data.results) {
                $scope.canLoadMore = false;
                return $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            var moreItems = data.results.map(function(item) {
                return $.extend({}, $scope.itemModel, item);
            });

            if (0 == moreItems.length) {
                $scope.canLoadMore = false;

                if (0 == $scope.items.length) {
                    return $scope.noItems = true;
                } else {
                    return $modal.toast($scope.noMoreText);
                }
            }

            $scope.items = $scope.items.concat(moreItems);

            $scope.listData.pagetoken = data.next_page_token;
        };

        $scope.onSearch = function() {

            // reset data
            $scope.listData.pagetoken = '';

            $scope.items = [];
            $scope.noItems = false;
            $scope.canLoadMore = true;

            // reload
            $scope.$broadcast('scroll.infiniteScrollComplete');
        };

        window.addEventListener('native.keyboardhide', $scope.onSearch);

        $scope.$on('$destroy', function() {
            window.removeEventListener('native.keyboardhide', $scope.onSearch);
        });
    };
});