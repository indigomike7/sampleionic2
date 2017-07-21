define([
    'angular',
    'photo/model/photo',
    'global/base/ListController'
], function(angular, PhotoModel, ListCtrl) {
    
    return function($scope, $injector, $modal, gettext, gettextCatalog) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more photos'),
            itemModel: PhotoModel,
            apiService: function(){
                if (typeof $scope.$parent.searchPhotosApi != 'undefined') {
                    return $scope.$parent.searchPhotosApi;
                }
                
                return 'photo/filter';
            },
            listById: false,
            getItemExtraData: function(){
                return $scope.$parent.getPhotoItemExtraData();
            },
            getQueryData: function(){return $scope.$parent.searchPhotos;},
        });
    };
});