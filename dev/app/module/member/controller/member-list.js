define([
    'angular',
    'member/model/member',
    'global/base/ListController'
], function(angular, Model, Ctrl) {
    return function($scope, $state, $injector, $modal, gettext, gettextCatalog) {


        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            
            noMoreText: gettextCatalog.getString('No more members'),
            itemModel: Model,
            apiService: 'member/fetch',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchMembers;
            }
        });
        
        return $scope;
    };
});