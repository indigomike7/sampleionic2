define([
    'global/base/BaseController'
], function(Ctrl) {
    return function($scope, $injector, $state) {
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.searchEvents = {
            iLimit: 10,
            iPage: 1,
            iParentId: $scope.item.getId(),
            sParentType: $scope.item.getType(),
        };

        $scope.searchEventsApi = 'directory/fetch_events';

        $scope.$on('event:deleted', function(e, data) {
            $scope.item.iTotalEvents--;
        });
    };
});
