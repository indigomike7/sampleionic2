define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/poll/poll-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, PollAttachmentTpl) {

	HeadlinePlugin.add([
		'comment_poll',
	], function(feed, gettext, gettextCatalog) {
	    feed.getItemTitle = function(){
	        return gettextCatalog.getString('poll');
	    };
	    
        return sprintf(gettextCatalog.getString('%1$s commented on %2$s\'s %3$s'), feed.getPosterLink(), feed.getOwnerLink(), feed.getItemLink(22));
    });

    HeadlinePlugin.add([
        'poll_new',
		'poll'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s created a new poll'), feed.getPosterLink());
    });
    

	AttachmentTplPlugin.add(['poll'], PollAttachmentTpl);
});