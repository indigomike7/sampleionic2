define([
    'global/Base/BaseController'
], function(BaseController) {
    
    return function($injector, gettext, $scope, $rootScope) {
        
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });
        
        $scope.onDetailLike  =  $scope._like($scope, function(){return $scope.obj;});
          
        $scope.onDetailReport  = $scope._report($scope, function(){return $scope.obj;});

        $scope.onDetailShare =  $scope._share($scope, function(){return $scope.obj;});
        
        $rootScope.$on('comment:add', function(e, args) {
            $scope.obj.iTotalComment++;
        });
        
        $rootScope.$on('comment:delete', function(e, args) {
            $scope.obj.iTotalComment--;
        });
    };
});