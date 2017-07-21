define([
    'page/model/page',
    'global/base/ListController'
], function(Model, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more pages'),
            itemModel: Model,
            apiService: 'pages/fetch',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchPages;
            },
        });
        
        return $scope;
    };
});