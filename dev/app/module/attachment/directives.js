define([
	'attachment/directive/attachment-add-bar-dir',
	'attachment/directive/attachment-add-preview-dir'
], function(attachmentAddBarDir, attachmentAddPreviewDir) {

    angular.module('myapp.directives')
	    .directive('attachmentAddBarDir', attachmentAddBarDir)
	    .directive('attachmentAddPreviewDir', attachmentAddPreviewDir);
});