define([
    'poll/controller/poll-list-ipad',
    'poll/model/poll',
    'global/base/ListController'
], function(PollListIpadCtrl, Model, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog) {
        
        /**
         * extend base list controller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more polls.'),
            itemModel: Model,
            apiService: 'poll/fetch',
            listById: false,
            getQueryData: function(){
                return $scope.$parent.searchPolls;
            }
        });

        if (ionic.Platform.isIPad()) {
            $injector.invoke(PollListIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});