define([
    'ynchat/model/ynchat'
], function(YnchatModel) {

    return function($scope, $state, $site, $ynchat, $ionicScrollDelegate) {

        if (!$scope.ynchatObj) {
            $scope.ynchatObj = $.extend({}, YnchatModel, {
                user_id: $state.params.id || 0
            });
        }

        /**
         * Data for message list directive
         */
        $scope.messageListApi = 'ynchat/getOldConversation';
        $scope.messageListData = {
            action: 'getOldConversation',
            sType: 'yesterday',
            sUserIdHash: $ynchat.getConfig('sUserIdHash'),
            iUserId: $scope.ynchatObj.getId()
        };

        /**
         * Change history type
         */
        $scope.setHistoryType = function(type) {

            $scope.messageListData.sType = type || 'yesterday';
            $scope.$broadcast('ynchat:messageListDataUpdate');
            
            $scope.scrollTop();
        };

        /**
         * Scroll view to top
         */
        $scope.scrollTop = function() {

            if (ionic.Platform.isIOS()) {
                $ionicScrollDelegate.$getByHandle('ynchat-history-list').scrollTop();
            } else {
                $("html, body").scrollTop(0);
            }
        };

        $scope.loadMessageComplete = function() {

            $scope.scrollTop();
        };
    };
});