define([], function() {

    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {

        $scope.searchForm = {
            categoryOptions: []
        };

        $scope.doGetSearchForm = function() {

            $http2.get('blog/categories')
            .success($scope.doGetSearchFormSuccess)
            .error($scope.doGetSearchFormError)
            .finally(function(){});
        };

        $scope.doGetSearchFormSuccess = function(data) {
        	if (data.error_code) {
        		return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
        	}

        	$scope.searchForm.categoryOptions = data;
        };

        $scope.doGetSearchFormError = function() {

        	console.error('doGetSearchForm', arguments);
        	$modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.doGetSearchForm();
    };
});