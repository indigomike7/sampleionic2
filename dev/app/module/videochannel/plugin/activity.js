define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/videochannel/videochannel-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, VideoChannelAttachmentTpl) {

	HeadlinePlugin.add([
		'comment_videochannel'
	], function(feed, gettext, gettextCatalog) {
	    feed.getItemTitle = function(){
            return gettextCatalog.getString('videochannel');
        };
        return sprintf(gettextCatalog.getString('%1$s commented on %2$s\'s %3$s'), feed.getPosterLink(), feed.getOwnerLink(), feed.getItemLink());
    });

    HeadlinePlugin.add([
		'videochannel_new'
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s posted a new video'), feed.getPosterLink());
    });

	HeadlinePlugin.add([
		'videochannel'
	], function(feed, gettext, gettextCatalog) {
		if(feed.getCount() > 1)
			return sprintf(gettextCatalog.getString('%s added new videos'), feed.getPosterLink());
		return sprintf(gettextCatalog.getString('%s shared a video'), feed.getPosterLink());

	});

    HeadlinePlugin.add([
		'videochannel_add_favorite'
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s added video %2$s to his/her favourite playlist'), feed.getPosterLink(), feed.getItemLink(22));
    });

    HeadlinePlugin.add([
		'videochannel_playlist_new'
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s posted a new video playlist'), feed.getPosterLink());
    });

	AttachmentTplPlugin.add(['videochannel'], VideoChannelAttachmentTpl);
});