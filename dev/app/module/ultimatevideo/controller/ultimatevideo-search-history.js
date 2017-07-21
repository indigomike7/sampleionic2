define(function() {
    return function($scope, $http2, $site, gettext, gettextCatalog) {
        $site.debug > 2 && console.log('UltimateVideoSearchHistoryController');
        $scope.aCategories = [];

        $http2.get('ultimatevideo/categories')
            .success(function(data) {
                if (data.error_code) {
                    return $modal.alert(data.error_message);
                }else{
                    $scope.aCategories = data;
                }
            }).error(function() {
            console.error('doGetSearchForm', arguments);
        }).finally(function(){

        });
    };
});