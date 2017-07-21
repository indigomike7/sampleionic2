define([
	'text!tpl/music/music-playlist-detail-content.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});