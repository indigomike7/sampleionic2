define([
    'message/model/conversation',
    'global/base/ListController'
], function(ConversationModel, ListCtrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site, $state) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more messages'),
            itemModel: ConversationModel,
            apiService: 'message/fetch',
            getQueryData: function() {
                return $scope.$parent.searchConversations;
            }
        });
    };
});