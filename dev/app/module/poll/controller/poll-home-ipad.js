define([
    'global/base/BrowseController',
    'poll/model/poll',
    'text!tpl/poll/poll-search.html'
], function(BrowseController, PollModel, searchTemplate) {

    return function($scope, $injector, $ionicTabsDelegate, $state, $site) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.initQuery = function() {

            $scope.searchPolls  =  {
                iPage: 1,
                iAmountOfPoll: 10,
                sSearch: '',
                sView: 'all',
                sOrder: 'latest'
            };
        };

        // click on 'All' and 'My' polls
        $scope.onClickTab = function(tabIndex) {
            $scope.showAdvSearch = false;
            angular.element('body').removeClass('search-open search-advanced');
            var sView = (tabIndex == 1) ? 'my' : 'all';
            if (sView != $scope.searchPolls.sView) {
                $ionicTabsDelegate.select(tabIndex);

                $scope.initQuery();
                $scope.searchPolls.sView = sView;
                $scope.$broadcast('poll:viewChange', $scope.searchPolls.sView);
            }
        };

        $scope.onSubmitSearch = function() {

            $scope.$broadcast('poll:queryChange', $scope.searchPolls);
        };

        // set object to be shown in detail pane
        $scope.setActiveObj = function(obj) {

            $scope.activeObj = obj;

            $scope.$broadcast('poll:objChange', $scope.activeObj);
        };

        // when click on an item in poll list
        $scope.onItemClick = function(item) {
            // check if clicked item is not activated
            if (!$scope.activeObj || $scope.activeObj.getId() != item.getId()) {
                $scope.setActiveObj(item);
            }
        };

        // navigate from another page to a specific poll, no set active poll automatically
        if ($state.params.iPollId) {
            var item = $.extend({}, PollModel, {
                iPollId: $state.params.iPollId
            });

            $scope.setActiveObj(item);
        }

        $scope.initQuery();
    };
});