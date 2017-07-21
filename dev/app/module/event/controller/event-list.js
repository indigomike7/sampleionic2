define([
    'event/model/event',
    'global/base/ListController'
], function(EventModel, ListCtrl) {
    return function($scope, $injector, gettext, gettextCatalog) {
        
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more events'),
            itemModel: EventModel,
            apiService: function(){
                if(typeof $scope.$parent.searchEventsApi != 'undefined')
                    return $scope.$parent.searchEventsApi;
                    
                return 'event/get';
            },
            listById: false,
            getQueryData: function(){
                return $scope.$parent.searchEvents;
            }
        });
        
        return $scope;
    };
});