define([
    'activity/plugin/headline',
    'activity/plugin/attachment-tpl',
    'text!tpl/core/core-link-attachment.html'
], function(HeadlinePlugin, AttachmentTplPlugin, CoreLinkAttachmentTpl) {

    HeadlinePlugin.add([
        'network_join',
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s joined the network %2$s'), feed.getPosterLink(), feed.getItemTitle());
    });

    HeadlinePlugin.add([
        'share',
    ], function(feed, gettext, gettextCatalog) {
        if (feed.getSharedUserLink()) {
            return sprintf(gettextCatalog.getString('%1$s shared %2$s\'s %3$s'), feed.getPosterLink(), feed.getSharedUserLink(), feed.getAttachmentLink(gettextCatalog));
        } else {
            return sprintf(gettextCatalog.getString('%s shared...'), feed.getPosterLink());
        }
    });

    AttachmentTplPlugin.add(['core_link', 'link'], CoreLinkAttachmentTpl);
});