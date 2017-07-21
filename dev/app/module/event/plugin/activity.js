define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/event/event-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, EventAttachmentTpl) {

    HeadlinePlugin.add([
        'event',
        'fevent',
        'event_create',
        'ynevent_create'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s created a new event'), feed.getPosterLink());
    });

    HeadlinePlugin.add([
        'event_comment',
        'fevent_comment',
    ], function(feed, gettext, gettextCatalog) {

        if (feed.getParentModuleId() == 'feed') {
            return sprintf(gettextCatalog.getString('%1$s &rarr; %2$s'), feed.getPosterLink(), feed.getParentLink(22));
        }

        return feed.getPosterLink();
    });

    HeadlinePlugin.add([
        'event_join',
        'ynevent_join'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s joined the event %2$s'), feed.getPosterLink(), feed.getItemLink(22));
    });

    HeadlinePlugin.add([
        'event_topic_create',
        'ynevent_topic_create'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s added new topic in the event %2$s'), feed.getPosterLink(), feed.getItemLink(22));
    });

    HeadlinePlugin.add([
        'event_topic_reply',
        'ynevent_topic_reply'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s replied to a topic in the event %2$s'), feed.getPosterLink(),feed.getItemLink(22));
    });

    HeadlinePlugin.add([
        'event_video_create',
        'ynevent_video_create'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%1$s added a new video in the event %2$s'), feed.getPosterLink(),feed.getItemLink(22));
    });

    HeadlinePlugin.add([
        'event_photo_upload',
        'ynevent_photo_upload'
    ], function(feed, gettext, gettextCatalog) {

        var param = feed.getParam();
        var count = param.count || 1;

        if (count < 2) {
            return sprintf(gettextCatalog.getString('%1$s added 1 photo in the event %2$s'), feed.getPosterLink(), feed.getItemLink(22));
        } else {
            return sprintf(gettextCatalog.getString('%1$s added %2$s photos in the event %3$s'), feed.getPosterLink(), count, feed.getItemLink(22));
        }
    });

    AttachmentTplPlugin.add(['event', 'fevent'], EventAttachmentTpl);
});