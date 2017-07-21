define([
    'angular',
    'photo/model/album',
    'global/base/ListController'
], function(angular, AlbumModel, ListCtrl) {
    
    return function($scope, $injector, $modal, gettext, gettextCatalog) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more albums'),
            itemModel: AlbumModel,
            apiService: function(){
                if(typeof $scope.$parent.searchAlbumsApi == 'function'){
                    return $scope.$parent.searchAlbumsApi();
                }
                // return 'photo/filter_album';
                return 'photo/filter_album';
            },
            listById: false,
            getQueryData: function (){return $scope.$parent.searchAlbums;}
        });
    };
});