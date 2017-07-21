define([
    'group/model/discussion_topic',
    'global/base/ListController'
], function(Model, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more topics.'),
            itemModel: Model,
            apiService: 'groups/discussions',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchDiscussionTopics;
            },
        });
        
        return $scope;
    };
});