define([
    'photo/controller/photo-album-search',
	'text!tpl/photo/photo-album-search.html'
], function(Controller, text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: 'PhotoAlbumSearchCtrl'
		};
	};
});