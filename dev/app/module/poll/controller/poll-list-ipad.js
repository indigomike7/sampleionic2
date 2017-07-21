define([
    'poll/model/poll'
], function(PollModel) {

    return function($scope, $state) {

        $scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad && !$state.params.iPollId && !$scope.noItems) {
                var item = $.extend({}, PollModel, $scope.items[0]);
                $scope.setActiveObj(item);
            }
        };

        $scope.$on('poll:viewChange', $scope.onSearch);

        $scope.$on('poll:queryChange', $scope.onSearch);

        // catch delete event to remove current poll and reload next poll, if it's the last poll, load the previous one
        $scope.$on('poll:itemDeleted', function(event, args){

            // get current poll index
            var currentItemIndex = $scope.items.map(function(item){
                return item.iPollId;
            }).indexOf(args.iPollId);
            $scope.items.deleteItem(args.iPollId);

            // set active poll after deleting
            $scope.setActiveObj($scope.items[currentItemIndex] || $scope.items[currentItemIndex-1] || {});
        });
    };
});