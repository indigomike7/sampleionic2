define([
	'activity/plugin/attachment-tpl',
	'global/helper',
	'moment'
], function(AttachmentTplPlugin, Helper, Moment) {

    return function($compile) {
	    
	    var linker = function(scope, element, attrs) {

	    	if (!scope.obj.aAttachments.length) {
	    		return false;
	    	}

	    	scope.Helper = Helper;
	    	scope.Moment = Moment;
	    	scope.Window = window;

	    	var template = AttachmentTplPlugin.get(scope.obj.aAttachments[0].sType);
	        
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
