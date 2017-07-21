define([
	'text!tpl/cometchat/cometchat-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/cometchat/cometchat-list-dir.html'),
			controller: 'CometchatListCtrl'
		};
	};
});