define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $injector, $state, $http2) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        var iUserId  = $state.params.iUserId;
 		$scope.items = {};
 		$scope.fetchData =  function(){
            
            var sendData = {
                iUserId: iUserId,
            };
            
            $http2.get('user/points',sendData)
            .success(function(data){
                if(data.error_code){
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }else{
                    $.extend($scope.items, data);
                    $scope.dataReady = true;
                }
            })
            .error(function(){})
            .finally(function(){});
        };
        $scope.fetchData();
        console.log($scope.items);
        return $scope;
    };
});