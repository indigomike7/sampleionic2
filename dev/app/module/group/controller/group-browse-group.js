define([
    'group/model/group',
    'global/base/BrowseController',
    'text!tpl/group/group-search.html'
], function(GroupModel, BrowseController, searchTemplate) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $site) {
        /**
         * check reqire permission
         */
        $site.requirePerm('pf_group_browse');
        $scope.canCreate =  $site.getPerm('pf_group_add');
        $scope.canAddGroupCover =  $site.getPerm('pf_group_add_cover_photo');

        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchGroups = {
            sView: 'all',
            iGroup: 1,
            sSearch: '',
            iCategoryId: "",
            iLimit: 20,
            sOrder: 'latest',
            iPage: 1
        };
        
        return $scope;
    };
});
