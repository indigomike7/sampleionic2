define([
    'global/base/BaseController'
], function(BaseController) {
    return function($injector, $scope, $rootScope, $site, $modal, $timeout, gettext, gettextCatalog, $http2, $q) {

        $scope.$on('$destroy',function(){
            // cancel all current request.
           $scope.loadMore  = function(){};
           $scope.loadMoreSuccess  = function(){};
           $scope.loadMoreError  = function(){};
           $scope.loadNew  = function(){};
           $scope.loadNewSuccess  = function(){};     
           $scope.loadNewError  = function(){};
        });
        
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $.extend($scope, {
            items: [],
            canLoadMore: true,
            isFirstLoad: true,
            noItems: false,
            noMoreText: gettextCatalog.getString('No more items'),
            itemModel: null,
            apiService: '',
            listById: false,
            onLoadMoreEnd: function(){},
            onLoadNewEnd: function(){},
            getQueryData: function(){},
            getItemExtraData: function(){ return {};},
            listData: null,
            showAdvSearch: false,
            enableLoadMore: true,
            enableLoadNew: false,
            afterResetQuery: function(){},
            $qMore: null,
            $qNew: null,
        });

        $scope.mapItems = function(data) {

            var extraData =  $scope.getItemExtraData();

            return data.map(function(item) {
                return $.extend({}, $scope.itemModel, item, extraData);
            });
        };

        $scope.loadMore = function() {
            
            $scope.$qMore = $q.defer();

            if(null == $scope.listData)
                $scope.listData =  $scope.getQueryData();

            $scope.noItems = false;

            if ($scope.listById) {
                $scope.listData.sAction = 'more';
            }
            
            var sendData =  $scope.listData;
            
            $http2.get($scope.apiService, $scope.listData,{
                // cache: true,
                timeout: $scope.$qMore.promise
            })
            .success($scope.loadMoreSuccess).error($scope.loadMoreError)
            .finally(function() {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                if (!$scope.enableLoadMore) {
                    $scope.canLoadMore = false;
                }
            });
        };

        $scope.loadMoreSuccess = function(data) {

            if (data.error_code) {
                $scope.canLoadMore = false;
                return $modal.alert(data.error_message || 'Can not load data from server');
            }
            
            var moreItems = $scope.mapItems(data);

            if (0 == moreItems.length) {
                $scope.canLoadMore = false;
            
                if (0 == $scope.items.length) {
                    $scope.noItems = true;
                } else {
                    $scope.noMoreText && $modal.toast($scope.noMoreText);
                }

                $scope.onLoadMoreEnd();

                if ($scope.isFirstLoad) {
                    $scope.isFirstLoad = false;
                }

                return;
            }

            $scope.items = $scope.items.concat(moreItems);

            if ($scope.listById) {
                $scope.updateMinMax();
            } else {
                $scope.listData.iPage++;
            }
            
            $scope.onLoadMoreEnd();

            if ($scope.isFirstLoad) {
                $scope.isFirstLoad = false;
            }
        };

        $scope.loadMoreError = function(data, status, headers, config) {

            if ($scope.cancelled || status == 0) {
                $scope.cancelled = false;
                return console.log('loadMore', 'user cancelled', arguments);
            }else{
                console.error('loadMore', arguments);
                $scope.canLoadMore = false;
                $modal.alert(gettextCatalog.getString('Can not load data from server'));    
            }
        };

        // only for list by id
        $scope.loadNew = function() {
            
            if(null == $scope.listData)
                $scope.listData =  $scope.onQueryDataReset();
                
            $scope.noItems = false;

            $scope.listData.sAction = 'new';
            
            $scope.$qMore = $q.defer();


            $http2.get($scope.apiService, $scope.listData,{
                cache: true,
                timeout: $scope.$qMore.promise,
            })
            .success($scope.loadNewSuccess).error($scope.loadNewError)
            .finally(function() {
                $scope.$broadcast('scroll.refreshComplete');
            });
            
            $scope.onLoadNewEnd();
        };

        $scope.loadNewSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || 'Can not load data from server');
            }

            var newItems = $scope.mapItems(data);

            if (0 == newItems.length) {
                return;
            }

            $scope.items = newItems.concat($scope.items);

            if ($scope.listById) {
                $scope.updateMinMax();
            }

            if (0 == $scope.items.length) {
                $scope.noItems = true;
            }
        };

        $scope.updateMinMax = function() {
            $scope.listData.iMinId = $scope.items.length ? $scope.items[0].getId() : 0;
            $scope.listData.iMaxId = $scope.items.length ? $scope.items[$scope.items.length - 1].getId() : 0;
        };

        $scope.loadNewError = function() {
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
        
        $scope.doResetQuery = function(listData){
            
            $scope.listData = $scope.getQueryData();
            
            if($scope.listById){
                $scope.listData.iMinId = 0;
                $scope.listData.iMaxId = 0;
            }else{
                $scope.listData.iPage = 1;
            }
            
            $scope.items = [];
            $scope.noItems = false;
            $scope.canLoadMore = true;
            $scope.isFirstLoad = true;
            
            $scope.afterResetQuery();

            // reload
            $scope.$broadcast('scroll.infiniteScrollComplete');
        };
        
        $scope.$parent.onSearch = function(){

            if($scope.$qMore)
                $scope.$qMore.resolve('abort');
                
            if($scope.$qNew)
                $scope.$qNew.resolve('abort');
                
            $scope.cancelled = true;

            $scope.$parent.showAdvSearch = false;
            $scope.$parent.searchPopover && $scope.$parent.searchPopover.hide();
            angular.element('body').removeClass('search-open search-advanced');

            $scope.doResetQuery();
            
            console.log('search');
            
            /**
             * hide keyboard 
             * @link: https://github.com/driftyco/ionic-plugins-keyboard
             */
            $timeout(function(){
                if(window.cordova && window.cordova.plugins.Keyboard) {
                    window.cordova.plugins.Keyboard.close();
                }
            },100);
        };

        $timeout(function() {
            if (!$scope.$qMore) {
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }
        }, 2000);
    };
});