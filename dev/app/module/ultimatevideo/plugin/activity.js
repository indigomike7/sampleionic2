define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/ultimatevideo/ultimatevideo-attachment-video.html',
	'text!tpl/ultimatevideo/ultimatevideo-attachment-playlist.html'
], function(AttachmentTplPlugin, HeadlinePlugin, UltimateVideoAttachmentVideoTpl, UltimateVideoAttachmentPlaylistTpl) {
	console.log('ultimatevideo/plugin/activity');
	HeadlinePlugin.add([
		'ultimatevideo_video'
	], function(feed, gettext, gettextCatalog) {
		return sprintf(gettextCatalog.getString('%s posted a new video'), feed.getPosterLink());
	});

	HeadlinePlugin.add([
		'ultimatevideo_playlist'
	], function(feed, gettext, gettextCatalog) {
		console.log('headlineplugin', feed);
		return sprintf(gettextCatalog.getString('%s created a new playlist'), feed.getPosterLink());
	});
	AttachmentTplPlugin.add(['ultimatevideo/video', 'ultimatevideo_video'], UltimateVideoAttachmentVideoTpl);
	AttachmentTplPlugin.add(['ultimatevideo/playlist', 'ultimatevideo_playlist'], UltimateVideoAttachmentPlaylistTpl);
});