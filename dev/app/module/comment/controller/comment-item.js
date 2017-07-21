define([
    'global/base/BaseController'
], function(Ctrl) {
    return function($injector, $http2, $scope, $site, $modal, gettext, gettextCatalog, $http, $coreSettings) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.doLike = function() {

        	if ($scope.isProcessingLike) {
                return;
            }

            $scope.isProcessingLike = true;
            $scope.toggleLikeData();

            var likeItemUrl = $scope.item.isLiked() ? 'like/like' : 'like/unlike';
            var postData = {
                iCommentId: $scope.item.getId(),
                iItemId: $scope.item.getItemId(),
                sItemType: $scope.item.getItemType()
            };

            $http2.post(likeItemUrl, postData)
            .success($scope.likeSuccess)
            .error($scope.likeError)
            .finally(function() {
                $scope.isProcessingLike = false;
            });
        };

        $scope.likeSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                $scope.toggleLikeData();
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.item.bIsLiked = data.bIsLiked;
            $scope.item.iTotalLike = data.iTotalLike;
        };

        $scope.likeError = function() {

            console.warn('likeError', arguments);
            
            $scope.toggleLikeData();
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.toggleLikeData = function() {

        	$scope.item.bIsLiked = !$scope.item.isLiked();
            $scope.item.isLiked() ? $scope.item.iTotalLike++ : $scope.item.iTotalLike--;
        };

        $scope.doDislike = function() {

            if ($scope.isProcessingDislike) {
                return;
            }

            $scope.isProcessingDislike = true;
            $scope.toggleDislikeData();

            var dislikeItemUrl = $scope.item.isDisliked() ? 'like/dislike' : 'like/removedislike';
            var postData = {
                iCommentId: $scope.item.getId(),
                iItemId: $scope.item.getItemId(),
                sItemType: $scope.item.getItemType()
            };

            $http2.post(dislikeItemUrl, postData)
            .success($scope.dislikeSuccess)
            .error($scope.dislikeError)
            .finally(function() {
                $scope.isProcessingDislike = false;
            });
        };

        $scope.dislikeSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                $scope.toggleDislikeData();
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.item.bIsDisliked = data.bIsDisliked;
            $scope.item.iTotalDislike = data.iTotalDislike;
        };

        $scope.dislikeError = function() {

            console.warn('dislikeError', arguments);
            
            $scope.toggleDislikeData();
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.toggleDislikeData = function() {

            $scope.item.bIsDisliked = !$scope.item.isDisliked();
            $scope.item.isDisliked() ? $scope.item.iTotalDislike++ : $scope.item.iTotalDislike--;
        };
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this comment?'),
            'comment/remove',
            function(){return {
                iCommentId: $scope.item.getId(),
                iItemId: $scope.item.getItemId(),
                sItemType: $scope.item.getItemType()
            };},
            function(data){
                $scope.obj.iTotalComment --;
                $scope.obj.comments.deleteItem($scope.item.getId());
            }
        );

        $scope.canDislike = $coreSettings.get('like_allow_dislike');
    };
});