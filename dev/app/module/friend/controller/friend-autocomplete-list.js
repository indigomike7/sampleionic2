define([
    'angular',
    'friend/model/friend',
    'global/base/ListController'
], function(angular, FriendModel, ListCtrl) {
    return function($scope,$state, $rootScope, $injector, $modal, gettext, gettextCatalog, $timeout) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: '',
            itemModel: FriendModel,
            apiService: 'friend/fetch',
            listById: false,
            getQueryData: function (){
                return $scope.$parent.searchFriends;
            }
        });

        $scope.$parent.onSearch = function(){

            if($scope.$qMore)
                $scope.$qMore.resolve('abort');
                
            if($scope.$qNew)
                $scope.$qNew.resolve('abort');
                
            $scope.cancelled = true;

            $scope.$parent.showAdvSearch = false;
            $scope.$parent.searchPopover && $scope.$parent.searchPopover.hide();
            angular.element('body').removeClass('search-open search-advanced');

            $scope.doResetQuery();
        };

        $scope.$parent.onSubmitSearch = function() {

            $scope.$parent.onSearch();

            if (window.cordova && window.cordova.plugins.Keyboard) {
                window.cordova.plugins.Keyboard.close();
            }

            $timeout(function() {
                var $trigger_content = $('#trigger_content');
                if ($trigger_content.length) {
                    $trigger_content.trigger('scroll');
                }
            }, 1000);
        };
    };
});