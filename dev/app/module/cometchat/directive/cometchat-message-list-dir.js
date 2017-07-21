define([
	'text!tpl/cometchat/cometchat-message-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/cometchat/cometchat-message-list-dir.html'),
			controller: 'CometchatMessageListCtrl'
		};
	};
});