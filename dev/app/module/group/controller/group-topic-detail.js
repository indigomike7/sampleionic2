define([
    'group/model/discussion_topic',
    'global/base/BaseController'
], function(Model, Ctrl) {

    return function($scope, $injector, $state, $http2, $site, gettext, gettextCatalog, $modal, $location) {
    
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.item = $.extend({}, Model, {
            iTopicId: $state.params.iTopicId
        });

        $scope.searchDiscussionPost = {
            iTopicId: $state.params.iTopicId,
            iGroup: 1,
            iAmountOfPost: 10  
        };

        $scope.getTopicInfo = function() {

            var postData = {
                iTopicId: $scope.item.getId()
            };

            $http2.get('group/topic_info', postData)
            .success($scope.getTopicInfoSuccess)
            .error($scope.getTopicInfoError);
        };

        $scope.getTopicInfoSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);
            $scope.dataReady = true;
        };

        $scope.getTopicInfoError = function() {

            console.error('getTopicInfo', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($scope.item.isWatching()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Stop Watching'),
                    action: function() {
                        $scope.setWatch(0);
                    }
                });
            } else {
                settingBtns.push({
                    text: gettextCatalog.getString('Watch Topic'),
                    action: function() {
                        $scope.setWatch(1);
                    }
                });
            }

            if ($scope.item.canPost()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Reply'),
                    action: function() {
                        $location.path('app/group_topic_reply/' + $scope.item.getId());
                    }
                });
            }
            
            if ($scope.item.canDelete()) {
            	settingBtns.push({
                    text: gettextCatalog.getString('Delete'),
                    action: $scope.onItemDelete
                });
            }

            return settingBtns;
        });
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this topic?'),
            'group/topic_delete',
            function(){return {iTopicId: $scope.item.getId()};},
            function(data){
                $scope.goBack();
            }
        );

        $scope.setWatch = function(iWatch) {

            if ($scope.isProcessingWatch) {
                return;
            }

            $scope.isProcessingWatch = true;

            var postData = {
                iTopicId: $scope.item.getId(),
                iWatch: iWatch
            };

            $http2.post('group/topic_watch', postData)
            .success($scope.setWatchSuccess)
            .error($scope.setWatchError).
            finally(function() {
                $scope.isProcessingWatch = false;
            });
        };

        $scope.setWatchSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.hideSheet();

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.item.bIsWatching = !$scope.item.isWatching();
        };

        $scope.setWatchError = function() {

            console.warn('setWatch', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.getTopicInfo();
    };
});