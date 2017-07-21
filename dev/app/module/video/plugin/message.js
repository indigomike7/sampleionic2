define([
	'message/plugin/message-attachment-tpl',
	'text!tpl/video/video-message-attachment.html'
], function(AttachmentTplPlugin, VideoAttachmentTpl) {

	AttachmentTplPlugin.add(['video','ynvideo'], VideoAttachmentTpl);
});