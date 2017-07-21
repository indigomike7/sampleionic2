define([
	'text!tpl/music/music-playlist-list.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: 'MusicPlaylistListCtrl',
		};
	};
});