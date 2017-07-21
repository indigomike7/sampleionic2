define([
    'directory/controller/directory-item',
    'directory/controller/directory-claim-item',
    'directory/model/directory-business'
], function(ItemCtrl, ClaimItemCtrl, ItemModel) {
    return function($scope, $injector, gettext, gettextCatalog, $location, 
        $state, $site, $modal, $http2, $ionicHistory, $timeout) {
        $injector.invoke(ItemCtrl, this, {
            $scope: $scope
        });

        $scope.selectedTab = $state.params.tab || 'overview';

        $scope.obj = $scope.item = $.extend({}, ItemModel, {
            iBusinessId: $state.params.id
        });

        $scope.fetchData = function() {
            $scope.isProcessingFetch = true;

            var sendData = {
                iBusinessId: $scope.item.getId()
            };

            $http2.get('directory/detail', sendData).success($scope.fetchDataSuccess).error($scope.fetchDataError).finally(function() {
                $scope.isProcessingFetch = false;
            });
        };

        $scope.fetchDataSuccess = function(data) {
            if (data.error_code) {
                $site.debug > 2 && console.warn('fetchData', arguments);
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);
            /*Invoke claim item ctrl*/
            if($scope.item.sType == 'claiming') {
                $injector.invoke(ClaimItemCtrl, this, {
                    $scope: $scope
                });
            }
            $scope.dataReady = true;
            
            $scope.setVisibleTabs();
        };

        $scope.fetchDataError = function() {
            $site.debug > 2 && console.warn('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        /**
         * Callback for child scope
         */
        $scope.updateObj = function(data) {
            angular.extend($scope.item, data);
        };

        $scope.isAvailabelModule = function(module) {
            if (typeof($scope.item.aAvailableModules) == 'undefined' || !angular.isArray($scope.item.aAvailableModules)) {
                return false;
            }

            if ($scope.item.aAvailableModules.indexOf(module) == -1) {
                return false;
            }

            return true;
        };

        $scope.setVisibleTabs = function() {
            $scope.is_show_overview = true;
            $scope.is_show_activity = true;
            
            var tabs = ['members', 'followers', 'reviews', 'photos', 'videos', 'videochannel', 'musics', 'events'];
            for (var i = 0; i < tabs.length; i++) {
                if ($scope.isAvailabelModule(tabs[i])) {
                    $scope['is_show_' + tabs[i]] = true;
                }
            }
        };

        $scope.goToPath = function(path) {
            $ionicHistory.nextViewOptions({
                disableAnimate: true
            });
            $location.path(path);
        };

        $scope.fetchData();
    };
});
