define([
    'group/model/group',
    'global/base/ListController'
], function(Model, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more groups'),
            itemModel: Model,
            apiService: 'groups/fetch',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchGroups;
            },
        });
        
        return $scope;
    };
});