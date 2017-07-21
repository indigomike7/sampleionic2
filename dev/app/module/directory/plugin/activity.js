define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/directory/business-attachment.html',
], function(AttachmentTplPlugin, HeadlinePlugin, AttachmentTpl) {

    HeadlinePlugin.add([
        'directory'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s created a business.'), feed.getPosterLink());
    });

    HeadlinePlugin.add([
        'directory_checkinhere'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s checked in this business.'), feed.getPosterLink());
    });

    AttachmentTplPlugin.add(['directory'], AttachmentTpl);
});