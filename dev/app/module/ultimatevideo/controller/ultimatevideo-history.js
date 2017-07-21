define([
    'global/base/BrowseController',
    'text!tpl/ultimatevideo/ultimatevideo-search-video.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site, gettext, gettextCatalog, $http2, $modal) {
        $site.debug > 2 && console.log('UltimateVideoBrowseVideoController');
        /**
         * check require permission
         */
        $site.requirePerm('ynuv_can_view_video');
        $scope.canCreateVideo =  $site.getPerm('ynuv_can_upload_video');

        /**
         * init data load
         */
        $scope.dataReady = false;

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.searchHistory = {
            sView: 'all',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 20,
            sOrder: 'creation_date',
        };

        $scope.showHistorySettings = $scope._setting($scope, function() {
            var btns = [];
            btns.push({
                text: gettextCatalog.getString('Remove all items'),
                action: $scope.onDeleteAllHistory,
                destructive: true
            });
            return btns;
        });

        $scope.videoItemOptions = ['watch_later', 'favorite', 'add_to_playlist', 'delete_history'];
        $scope.extraPlaylistOptions = ['delete_history'];

        $scope.onDeleteAllHistory = function() {
            $http2.post('ultimatevideo/deleteHistory', {iType: 2})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.$broadcast('ultimatevideo:delete-all-history');
                        $modal.toast('All items in history were removed successfully!');
                    }
                }).error(function () {
                console.error('removeAllHistory', arguments);
            }).finally(function () {

            });
        };
    };
});
