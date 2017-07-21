define([
    'directory/model/discussion_post',
    'global/base/ListController'
], function(Model, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more topics.'),
            itemModel: Model,
            apiService: 'directory/view_topic',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchDiscussionPost;
            },
        });
        return $scope;
    };
});