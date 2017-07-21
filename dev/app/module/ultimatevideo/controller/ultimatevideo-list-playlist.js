define([
    'angular',
    'ultimatevideo/model/playlist',
    'global/base/ListController'
], function(angular, PlaylistModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location, $site) {
        $site.debug > 2 && console.log('UltimateVideoListPlaylistController');
        $scope.dataReady = false;

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more playlists'),
            itemModel: PlaylistModel,
            apiService: 'ultimatevideo/fetch',
            listById: false,
            getQueryData: function(){return $scope.$parent.searchPlaylists;},
        });
    };
});