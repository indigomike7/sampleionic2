define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {
        
        // $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.pageCategories= [];
        $scope.dataReady = false;
        
        $scope.initForm = function(){
            $http2.get('pages/categories',{},{cache:true})
            .success(function(data){
                if(data.error_code){
                    // skip error
                }else{
                    $scope.pageCategories = data;
                    $scope.dataReady = true;
                }
            })
            .error(function(){
                // handle error .
            })
            .finally(function(){
            });
        };
        
        $scope.initForm();
        
        return $scope;
    };
});