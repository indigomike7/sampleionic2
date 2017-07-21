define([
	'global/base/DetailController',
	'photo/model/album',
	'global/viewer'
],function(DetailController, AlbumModel, Viewer){
    
    return function($scope, $site, $injector, gettext, gettextCatalog, $http2, $modal, $location){
        
        $scope.obj  =  $.extend({}, AlbumModel);
        
        $injector.invoke(DetailController, this, {$scope: $scope});
        
    };
});
