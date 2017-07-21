define(function() {
    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {

        $scope.searchForm = {
            filterOptions: [],
        };
        
        $scope.loadInit = function(){
            $http2.get('search/available_types')
            .success(function(data){
                $scope.searchForm.filterOptions = data;
            })
            .error($http2.defaultErrorHandler);
        };
        
        $scope.loadInit();
    };
});