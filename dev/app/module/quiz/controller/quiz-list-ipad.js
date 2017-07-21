define([
    'quiz/model/quiz'
], function(QuizModel) {

    return function($scope, $state) {

        $scope.onLoadMoreEnd = function() {

            if ($scope.isFirstLoad && !$state.params.iQuizId && !$scope.noItems) {
                var item = $.extend({}, QuizModel, $scope.items[0]);
                $scope.setActiveObj(item);
            }
        };

        $scope.$on('quiz:viewChange', $scope.onSearch);

        $scope.$on('quiz:queryChange', $scope.onSearch);

        // catch delete event to remove current quiz and reload next quiz, if it's the last quiz, load the previous one
        $scope.$on('quiz:itemDeleted', function(event, args){

            // get current quiz index
            var currentItemIndex = $scope.items.map(function(item){
                return item.iQuizId;
            }).indexOf(args.iQuizId);
            $scope.items.deleteItem(args.iQuizId);

            // set active quiz after deleting
            $scope.setActiveObj($scope.items[currentItemIndex] || $scope.items[currentItemIndex-1] || {});
        });
    };
});