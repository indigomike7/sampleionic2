define([
	'message/plugin/message-attachment-tpl',
	'text!tpl/music/music-message-attachment.html'
], function(AttachmentTplPlugin, MusicAttachmentTpl) {

	AttachmentTplPlugin.add(['music_playlist_song'], MusicAttachmentTpl);
});