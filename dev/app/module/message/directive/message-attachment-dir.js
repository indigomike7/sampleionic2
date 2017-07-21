define([
	'message/plugin/message-attachment-tpl',
	'moment'
], function(AttachmentTplPlugin, Moment) {

    return function($compile) {
	    
	    var linker = function(scope, element, attrs) {

	    	if (!scope.obj.aAttachments) {
	    		return false;
	    	}

	    	scope.Moment = Moment;
	    	scope.Window = window;

	    	var template = AttachmentTplPlugin.get(scope.obj.aAttachments.sType);
	        
	        element.html(template).show();
	        
	        $compile(element.contents())(scope);
	    };

	    return {
	        restrict: 'E',
	        replace: true,
	        link: linker,
	        scope: {
	            obj:'='
	        }
	    };
	};
});
