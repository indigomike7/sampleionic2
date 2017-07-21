define([
    'global/base/BaseController'
], function(Ctrl) {
    return function($scope, $injector) {
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.searchMusic = {
            iLimit: 10,
            iPage: 1,
            iParentId: $scope.item.getId(),
            sParentType: $scope.item.getType(),
        };

        $scope.searchMusicApi = 'directory/fetch_songs';
    };
});
