define([
	'text!tpl/chat/chat-ocn-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/chat/chat-ocn-dir.html'),
			controller: 'ChatOcnCtrl'
		};
	};
});