define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/blog/blog-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, BlogAttachmentTpl) {

    HeadlinePlugin.add([
        'blog',
		'blog_new',
		'ynblog_new'
	], function(feed, gettext, gettextCatalog) {
	    return sprintf(gettextCatalog.getString('%s wrote a new blog entry'), feed.getPosterLink());
    });
    
    HeadlinePlugin.add([
        'comment_blog',
        'comment_ynblog',
    ], function(feed, gettext, gettextCatalog) {
        feed.getItemTitle = function(){
            return gettextCatalog.getString('blog entry');
        };
        return sprintf(gettextCatalog.getString('%1$s commented on %2$s\'s %3$s'), feed.getPosterLink(), feed.getOwnerLink(), feed.getItemLink());
    });

    
	AttachmentTplPlugin.add(['blog','ynblog'], BlogAttachmentTpl);
});