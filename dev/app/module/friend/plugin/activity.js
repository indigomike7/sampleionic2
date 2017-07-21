define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/event/event-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, EventAttachmentTpl) {

    HeadlinePlugin.add([
        'friends',
    ], function(feed, gettext, gettextCatalog) {
        
        feed.sContent  = '';
        
        feed.getContent = function(){
            return '';
        };
        
        return sprintf(gettextCatalog.getString('%1$s is now friend with %2$s'), feed.getPosterLink(), feed.getObjectLink());
    });
});