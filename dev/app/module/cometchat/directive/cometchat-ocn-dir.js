define([
	'text!tpl/cometchat/cometchat-ocn-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/cometchat/cometchat-ocn-dir.html'),
			controller: 'CometchatOcnCtrl'
		};
	};
});