define([
    'global/base/BrowseController',
    'text!tpl/group/group-music-search.html'
], function(Ctrl, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site, $location) {

        $site.requirePerm('music.can_access_music');
        
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.searchTemplate = searchTemplate;

        $scope.searchMusic = {
            iGroup: 1,
            iItemId: $scope.item.getId(),
            sModule: 'groups',
            sView: 'all',
            iLimit: 20,
            sOrder: 'latest',
            sSearch: ''
        };

        $scope.musicExtraData = {
            sParentType: $scope.item.getType(),
            iParentId: $scope.item.getId()
        };
        
        $scope.onItemClick = function(item, e) {

            if ($(e.currentTarget).has($(e.target).closest('a')).length
            || $(e.currentTarget).has($(e.target).closest('[href-dir]')).length 
            || $(e.currentTarget).has($(e.target).closest('[ng-click]')).length) {
                return;
            }

            $location.path(item.getUrl().substr(2));
        };

        return $scope;
    };
});