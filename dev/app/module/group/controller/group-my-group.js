define([
    'group/model/group',
    'global/base/BrowseController',
    'text!tpl/group/group-search.html'
], function(GroupModel, BrowseController, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site) {
        /**
         * check reqire permission
         */
        //$site.requirePerm('group.view');
        $scope.canCreate =  $site.getPerm('groups.can_add_new_groups');
        $scope.canAddGroupCover =  $site.getPerm('groups.can_add_cover_photo_groups');

        /**
         * some other case
         */
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchGroups = {
            sView: 'my',
            iGroup: 1,
            sSearch: '',
            iCategoryId: 0,
            iLimit: 5,
            sOrder: 'latest'
        };
        
        return $scope;
    };
});
