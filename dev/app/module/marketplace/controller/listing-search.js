define([], function() {

    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {

        $scope.searchForm = {
            aCategory: [{
                category_id: '',
                name: gettextCatalog.getString('All Categories')
            }],
            aLocation: [{
                country_iso: '',
                name: gettextCatalog.getString('Anywhere')
            }]
        };

        $scope.doGetSearchForm = function() {

            $http2.get('marketplace/formsearch')
            .success($scope.doGetSearchFormSuccess)
            .error($scope.doGetSearchFormError);
        };

        $scope.doGetSearchFormSuccess = function(data) {

        	if (data.error_code) {
        		return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
        	}

        	$scope.searchForm.aCategory = $scope.searchForm.aCategory.concat(data.aCategory);
            $scope.searchForm.aLocation = $scope.searchForm.aLocation.concat(data.aLocation);
        };

        $scope.doGetSearchFormError = function() {

        	console.warn('doGetSearchForm', arguments);
        	$modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.doGetSearchForm();
    };
});