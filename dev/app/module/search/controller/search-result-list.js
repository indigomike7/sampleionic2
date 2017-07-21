define([
    'angular',
    'search/model/result',
    'global/base/ListController'
], function(angular, Model, Ctrl) {
    return function($scope,$state, $rootScope, $injector, $modal, gettext, gettextCatalog) {


        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more result'),
            itemModel: Model,
            apiService: 'search/fetch',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchCriteria;
            },
            afterResetQuery: $scope.$parent.afterResetQuery,
        });
        
        return $scope;
    };
});