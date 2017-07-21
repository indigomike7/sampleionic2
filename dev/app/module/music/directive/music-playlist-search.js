define([
	'text!tpl/music/music-playlist-search.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});