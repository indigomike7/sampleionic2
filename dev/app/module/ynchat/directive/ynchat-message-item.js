define([
	'text!tpl/ynchat/ynchat-message-item-date.html',
	'text!tpl/ynchat/ynchat-message-item-file.html',
	'text!tpl/ynchat/ynchat-message-item-link.html',
	'text!tpl/ynchat/ynchat-message-item-separator.html',
	'text!tpl/ynchat/ynchat-message-item-sticker.html',
	'text!tpl/ynchat/ynchat-message-item-text.html',
	'text!tpl/ynchat/ynchat-message-item-video.html'
], function() {

	return function($compile) {

		var linker = function(scope, element, attrs) {

	    	if (!scope.item.getType()) {
	    		return false;
	    	}

	    	var template = require('text!tpl/ynchat/ynchat-message-item-' + scope.item.getType() + '.html');
	        element.html(template).show();
	        $compile(element.contents())(scope);
	    };

	    return {
	        restrict: 'E',
	        replace: true,
	        link: linker,
	        scope: {
	            item: '=',
	            action: '@'
	        }
	    };
	};
});