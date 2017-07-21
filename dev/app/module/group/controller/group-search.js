define([
    'group/model/group',
    'global/base/BaseController'
], function(GroupModel, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {
        
        // $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.groupCategories= [];
        $scope.dataReady = false;
        
        $scope.initForm = function(){
            $http2.get('groups/categories',{},{cache:true})
            .success(function(data){
                if(data.error_code){
                    // skip error
                }else{
                    $scope.groupCategories = data;
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