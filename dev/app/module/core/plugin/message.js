define([
	'message/plugin/message-attachment-tpl',
	'text!tpl/core/core-link-message-attachment.html'
], function(AttachmentTplPlugin, CoreLinkAttachmentTpl) {

	AttachmentTplPlugin.add(['core_link'], CoreLinkAttachmentTpl);
});