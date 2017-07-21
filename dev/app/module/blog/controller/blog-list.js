define([
    'blog/model/blog',
    'global/base/ListController'
], function(BlogModel, ListCtrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site, $state, $timeout) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            notFoundText: gettextCatalog.getString('No one has written any blog entries yet.'),
            noMoreText: gettextCatalog.getString('No more blogs'),
            itemModel: BlogModel,
            apiService: 'blog/fetch',
            getQueryData: function() {
                return $scope.$parent.blogListData;
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

            $scope.notFoundText = gettextCatalog.getString('No blogs found.');
            $scope.doResetQuery();
            
            $timeout(function(){
                if(window.cordova && window.cordova.plugins.Keyboard) {
                    window.cordova.plugins.Keyboard.close();
                }
            },100);
        };
    };
});