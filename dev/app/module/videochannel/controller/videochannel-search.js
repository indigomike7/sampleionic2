define(function() {
    return function($scope, $http2, $site, gettext, gettextCatalog) {
        $scope.videoCategories = [];

        $http2.get('videochannel/categories')
        .success(function(data) {
            if (data.error_code) {
                return $modal.alert(data.error_message);
            }else{
                $scope.videoCategories = data;
            }            
        }).error(function() {
            console.error('doGetSearchForm', arguments);
        }).finally(function(){
            
        });
    };
}); 