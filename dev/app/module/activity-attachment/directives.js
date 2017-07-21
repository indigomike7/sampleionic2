define([
	'activity-attachment/directive/attachment-add-bar-dir',
	'activity-attachment/directive/attachment-add-preview-dir'
], function() {

    angular.module('myapp.directives')
	    .directive('activityAttachmentAddBarDir', require('activity-attachment/directive/attachment-add-bar-dir'))
	    .directive('activityAttachmentAddPreviewDir', require('activity-attachment/directive/attachment-add-preview-dir'));
});