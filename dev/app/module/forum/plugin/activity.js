define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/forum/forum-post-attachment.html',
	'text!tpl/forum/forum-thread-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, ForumPostAttachmentTpl, ForumThreadAttachmentTpl) {

	HeadlinePlugin.add([
		'forum'
	], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s posted a thread.'), feed.getPosterLink());
    });

    HeadlinePlugin.add([
		'forum_post'
	], function(feed, gettext, gettextCatalog) {
		return feed.getPosterLink() + ' ' + feed.getHeadLine() + ' "' + feed.getItemLink() + '"';
    });

	AttachmentTplPlugin.add(['forum'], ForumThreadAttachmentTpl);
	AttachmentTplPlugin.add(['forum_post'], ForumPostAttachmentTpl);
});