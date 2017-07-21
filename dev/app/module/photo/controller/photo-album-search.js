define(function() {
    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {

        $scope.searchForm = {
            categories: []
        };
        
        $http2.get('photo/categories')
        .success(function(data){
            if (data.error_code) {
                $modal.alert(data.error_message);
            }else{
                $scope.searchForm.categoryOptions = data;
            }
        })
        .error(function(){
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        })
        .finally(function(){
            
        });
        
    };
});