define([
    'global/base/BrowseController'
], function(BrowseController) {

    return function($scope, $injector, $site, $location) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchVideos = {
            iParentId: $scope.item.getId(),
            sParentType: $scope.item.getType(),
            sView: 'all',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 20,
            sOrder: 'creation_date'
        };

        $scope.searchVideosApi = 'directory/fetch_videos';

        $scope.onAddVideoClick = function() {
            var path = 'app/videos/add/' + $scope.item.getType() + '/' + $scope.item.getId();
            $location.path(path);
        };

        $scope.$on('video:deleted', function(e, data) {
            $scope.item.iTotalVideos--;
        });

        return $scope;
    };
});
