define([
    'user/model/user',
    'text!tpl/like/liked-list.html'
], function(Model, likedListTpl) {
    return function($scope, $rootScope, $site, $modal, gettext, gettextCatalog, $http2, $viewer, $ionicModal) {

        $scope.viewer = $.extend({}, Model, $viewer.get());
        
        $scope.$on('$destroy',function(){
            if($scope.likedModal){
                $scope.likedModal.remove();
            }
        });

        $scope.likedList = $scope.obj.getLikedList().filter(function(user) {
            return user.iUserId != $viewer.get('iUserId');
        });
        $scope.lastLiked = $scope.likedList.length ? $.extend({}, Model, $scope.likedList[0]) : Model;

        $scope.showLikedList = function() {

            $scope.likedModal = $ionicModal.fromTemplate(likedListTpl, {
                scope: $scope
            });

            $scope.likedModal.show();
        };

        $scope.hideLikedList = function() {
            
            $scope.likedModal.hide();
        };
    };
});