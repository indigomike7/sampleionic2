define([
    'global/base/BaseController'
], function(BaseController) {
    return function($scope, $injector, $state) {
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.listData = {
            iLimit: 10,
            iPage: 1,
            iBusinessId: $state.params.id
        };
    };
});
