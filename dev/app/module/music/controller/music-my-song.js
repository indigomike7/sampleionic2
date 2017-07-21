define([
    'global/base/BaseController'
], function(BaseController) {
    return function($scope, $injector) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.searchMusic = {
            sSearch: '',
            sView: 'my',
            sOrder: 'latest',
            iLimit: 20,
            iPage: 1,
        };

        return $scope;
    };
});