define([
	'message/plugin/message-attachment-tpl',
	'text!tpl/photo/photo-message-attachment.html'
], function(AttachmentTplPlugin, PhotoAttachmentTpl) {

	AttachmentTplPlugin.add(['album_photo','advalbum_photo'], PhotoAttachmentTpl);
});