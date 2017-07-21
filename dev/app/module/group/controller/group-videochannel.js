define([
    'global/base/BrowseController',
    'text!tpl/videochannel/videochannel-search.html'
], function(Ctrl, searchTemplate) {
    return function($scope, $injector) {

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.searchTemplate = searchTemplate;

        $scope.searchVideos = {
            iGroup: 1,
            iItemId: $scope.item.getId(),
            sModule: 'groups',
            sView: 'all',
            iAmountOfVideo: 10,
            sOrder: 'creation_date'
        };

        return $scope;
    };
});