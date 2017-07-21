define([
    'comment/model/comment'
], function(CommentModel) {
    return function($scope, $q, $site, $modal, gettext, gettextCatalog, $http, $http2) {

        $scope.itemLimit = 10;
        $scope.obj.comments = [];
        $scope.lastItemId = null;
        $scope.canLoadMore = true;
        $scope.data = {};
        $scope.$qMore = null;
        
        $scope.$on('$destroy', function(){
           $scope.loadMore  = function(){};
           $scope.loadMoreSuccess  = function(){};
           $scope.loadMoreError  = function(){}; 
        });

        $scope.loadMore = function() {
            
            $scope.$qMore =  $q.defer();

            var lastCommentId = $scope.obj.comments.length ? $scope.obj.comments[$scope.obj.comments.length - 1].iCommentId : 0;
            
            var sendData = {
                iItemId: $scope.obj.getId(),
                sItemType: $scope.obj.getType(),
                sParentId: $scope.obj.getParentModuleId(),
                iLastCommentIdViewed: $scope.lastItemId || lastCommentId,
                iAmountOfComment: $scope.itemLimit
            };



            $http2.get('comment/listallcomments',sendData,{
                timeout: $scope.$qMore.promise,
            })
            .success($scope.loadMoreSuccess)
            .error($scope.loadMoreError)
            .finally(function() {
            	$scope.$broadcast('scroll.infiniteScrollComplete');
                $scope.dataReady = true;
            });
        };

        $scope.loadMoreSuccess = function(data) {

            if (data.error_code) {
                $scope.canLoadMore = false;
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            var moreItems = data.map(function(item) {
                return $.extend({}, CommentModel, item);
            });

            var length = moreItems.length;

            if (length > 0) {
                $scope.lastItemId = moreItems[length - 1].getId();
            } else {
            	$scope.canLoadMore = false;
            }

            $scope.obj.comments = $scope.obj.comments.concat(moreItems);
        };

        $scope.loadMoreError = function() {
            $scope.canLoadMore = false;
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
        
        $scope.deleteItem = function(args) {
            $scope.obj.iTotalComment--;
            $scope.obj.comments.deleteItem(args.iItemId);
        };
    };
});