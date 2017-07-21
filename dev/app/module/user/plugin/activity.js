define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/user/user-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, UserAttachment) {

    HeadlinePlugin.add([
            'signup'
        ]
        ,function(feed, gettext, gettextCatalog){
            return sprintf(gettextCatalog.getString('%s has just signed up. Say hello!'), feed.getPosterLink());
        });

    HeadlinePlugin.add([
            'user_status',
            'user_photo',
            'custom',
            'post_self',
            'status']
        , function(feed, gettext, gettextCatalog) {
            return feed.getPosterLink();
        });


    HeadlinePlugin.add('profile_photo_update', function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s has added a new profile photo.'),feed.getPosterLink());
    });


    HeadlinePlugin.add([
        'post',
        'feed_comment'
    ], function(feed, gettext, gettextCatalog) {

        if (feed.hasParentUser()) {

            return sprintf(gettextCatalog.getString('%1$s &rarr; %2$s'), feed.getPosterLink(), feed.getParentLink(22));
        }

        return feed.getPosterLink();
    });
    AttachmentTplPlugin.add('user_photo', UserAttachment);
});
