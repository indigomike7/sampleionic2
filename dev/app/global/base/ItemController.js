define([
    'global/Base/BaseController'
], function(BaseController) {
    
    return function($injector, gettext, $scope, $location, $rootScope, $http2, $modal, gettext, $ionicActionSheet, $site, $ionicModal, $ionicPopup, $timeout) {
        
        $injector.invoke(BaseController, this, {
            $scope: $scope
        });
        
        $scope.onItemCommentPopup = $scope._commentPopup($scope, function(){
            return $scope.item;
        });
        
        $scope.onItemComment = function() {

            var ngCommentInput = angular.element('#comment_' + $scope.item.getType() + '_' + $scope.item.getId());
            
            if (ngCommentInput.length) {
                $timeout(function() {
                    ngCommentInput.focus();
                    if (typeof(cordova) != 'undefined' && !cordova.plugins.Keyboard.isVisible) {
                        cordova.plugins.Keyboard.show();
                    }
                }, 100);

                $scope.closeKeyboard = function() {
                    if (typeof(cordova) != 'undefined' && cordova.plugins.Keyboard.isVisible) {
                        cordova.plugins.Keyboard.close();
                    }
                };

                ngCommentInput.on('blur', $scope.closeKeyboard);

                $scope.$on('$destroy', function() {
                    ngCommentInput.off('blur', $scope.closeKeyboard);
                });
            } else {
                window.location.href = $scope.item.getUrl();
            }
        };
        
        $scope.onClickItemLike = function() {
            if ($scope.item.isLiked()) {
                $scope._like($scope, function(){return $scope.item;}, 0)();
            } else {
                $scope.toggleReaction();
            }
        };

        $scope.onItemLike = function(reactionId) {
            $scope._like($scope, function(){return $scope.item;}, reactionId)();
            $scope.toggleReaction();
        };

        $scope.toggleReaction = function(){
            if (typeof $scope.item.isShowEmoticons === 'undefined' || !$scope.item.isShowEmoticons) {
                $scope.item.isShowEmoticons = 1;
            } else {
                $scope.item.isShowEmoticons = 0;
            }
        };

        $scope.onItemReport  = $scope._report($scope, function(){return $scope.item;});

        $scope.onItemShare =  $scope._share($scope, function(){return $scope.item;});
        
        $scope.onItemDislike  =  $scope._dislike($scope, function(){return $scope.item;});

        $scope.onViewDisliked = $scope._viewDisliked($scope, function(){return $scope.item;});
          
        $scope.onItemSendMessage = function(){
            $location.path('app/messages/add/'+ $scope.item.getType() + '/'+ $scope.item.getId()+ '/'+encodeURI($scope.item.getTitle()));
        };
    };
});