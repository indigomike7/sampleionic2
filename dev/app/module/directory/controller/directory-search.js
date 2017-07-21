define([], function() {
    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {
        $scope.searchForm = {
            categoryOptions: []
        };

        $scope.getSearchForm = function() {
            $http2.get('directory/form_search').success($scope.getSearchFormSuccess).error($scope.getSearchFormError);
        };

        $scope.getSearchFormSuccess = function(data) {
            if (data.error_code) {
                return $modal.alert(data.error_message || 'Can not load data from server');
            }

            $scope.searchForm.categoryOptions = data.categoryOptions;
        };

        $scope.getSearchFormError = function() {
            console.error('getSearchForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.removeLocation = function() {
            delete($scope.listData.aLocation);
        };

        $scope.getSearchForm();
    };
});