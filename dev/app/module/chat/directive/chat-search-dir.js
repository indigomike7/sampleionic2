define([
	'text!tpl/chat/chat-search-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/chat/chat-search-dir.html')
		};
	};
});