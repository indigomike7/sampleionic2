define([
    'global/base/BaseController',
], function(BaseController) {
    return function($scope, $injector, $http2, $modal, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.form = {};

        $scope.fetchData = function() {

            $scope.isLoading = true;

            $http2.post('setting/fetch').success($scope.fetchDataSuccess).error($scope.fetchDataError).
            finally(function() {

                $scope.isLoading = false;
            });
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                console.warn('fetchDataSuccess', arguments);
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.form = data;
            $scope.form.invisible = $scope.form.invisible ? true : false;
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchDataError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.encodeURI = window.encodeURI;

        $scope.fetchData();
    };
});