define([
	'message/plugin/message-attachment-tpl',
	'text!tpl/videochannel/videochannel-message-attachment.html'
], function(AttachmentTplPlugin, VideoAttachmentTpl) {

	AttachmentTplPlugin.add(['videochannel'], VideoAttachmentTpl);
});