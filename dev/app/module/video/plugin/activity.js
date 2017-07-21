define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/video/video-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, VideoAttachmentTpl) {

	HeadlinePlugin.add([
		'comment_video',
		'comment_ynvideo',
		'ynvideo_comment_video',
	], function(feed, gettext, gettextCatalog) {
	    feed.getItemTitle = function(){
            return gettextCatalog.getString('video');
        };
        return sprintf(gettextCatalog.getString('%1$s commented on %2$s\'s %3$s'), feed.getPosterLink(), feed.getOwnerLink(), feed.getItemLink());
    });

    HeadlinePlugin.add([
		'video_new',
		'ynvideo_new'
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s posted a new video'), feed.getPosterLink());
    });

	HeadlinePlugin.add([
		'video',
	], function(feed, gettext, gettextCatalog) {
		return sprintf(gettextCatalog.getString('%s shared a video'), feed.getPosterLink());
	});

    HeadlinePlugin.add([
		'video_add_favorite',
		'ynvideo_add_favorite',
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s added video %2$s to his/her favourite playlist'), feed.getPosterLink(), feed.getItemLink(22));
    });

    HeadlinePlugin.add([
		'video_playlist_new',
		'ynvideo_playlist_new',
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s posted a new video playlist'), feed.getPosterLink());
    });

	AttachmentTplPlugin.add(['video'], VideoAttachmentTpl);
});