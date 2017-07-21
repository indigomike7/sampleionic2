define([
    'angular',
    'friend/model/friend',
    'global/base/ListController'
], function(angular, Model, Ctrl) {
    return function($scope,$state, $rootScope, $injector, $modal, gettext, gettextCatalog) {


        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more friends'),
            itemModel: Model,
            apiService: 'friend/fetch',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchFriends;
            },
        });
        
        return $scope;
    };
});