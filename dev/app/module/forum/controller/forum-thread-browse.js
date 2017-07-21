define([
    'global/base/BaseController',
], function(BaseController) {

    return function($scope, $injector, $state, gettext, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        var sView = $state.params.sView;

        $scope.listData = {
            iPage: 1,
            iForumId: 0,
            iAmountOfThread: 10,
            sView: sView
        };
    };
});