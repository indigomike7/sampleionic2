define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/page/page-attachment.html',
    'text!tpl/page/page-post-attachment.html',
    'text!tpl/page/page-topic-attachment.html',
], function(AttachmentTplPlugin, HeadlinePlugin, PageAttachmentView, PagePostAttachmentTpl,PageTopicAttachmentTpl) {

    HeadlinePlugin.add([ 'page_join', 'advpage_join' ],function(feed, gettext, gettextCatalog){
        return sprintf(gettextCatalog.getString('%1$s joined the page %2$s'), feed.getPosterLink(), feed.getItemLink());
    });

    HeadlinePlugin.add([ 'page_promote', 'advpage_promote'],function(feed, gettext, gettextCatalog){
        return sprintf(gettextCatalog.getString('%1$s has been made an officer for the page %2$s'), feed.getPosterLink(), feed.getItemLink());
    });

    HeadlinePlugin.add([ 'page_create', 'advpage_create', 'pages'],function(feed, gettext, gettextCatalog){
        return sprintf(gettextCatalog.getString('%s created a page'), feed.getPosterLink());
    });

    HeadlinePlugin.add([
        'page_topic_create',
        'advpage_topic_create'
    ],function(feed, gettext, gettextCatalog){

        feed.getItemUrl =  function(){
            if(feed.sParams && feed.sParams.child_id) {
                return '#/app/page_topic/' + feed.sParams.child_id;
            }
            if(feed.aAttachments.length && feed.aAttachments[0].iId) {
                return '#/app/page_topic/' + feed.aAttachments[0].iId;
            }
            return '#/app/' + this.getItemType() + '/' + this.getItemId();
        };

        feed.getItemTitle = function(){
            return gettextCatalog.getString('topic');
        };

        return sprintf(gettextCatalog.getString('%1$s posted a %2$s in the page %3$s'), feed.getPosterLink(), feed.getItemLink(), feed.getObjectLink());
    });

    HeadlinePlugin.add([
        'pages_comment',
    ], function(feed, gettext, gettextCatalog, $state) {

        if (feed.hasParentUser()) {

            if ($state.current.name == 'app.pageDetailActivity') {

                return sprintf(gettextCatalog.getString('%s'), feed.getPosterLink());
            } else {

                return sprintf(gettextCatalog.getString('%1$s &rarr; %2$s'), feed.getPosterLink(), feed.getParentLink(22));
            }
        }

        return feed.getPosterLink();
    });

    HeadlinePlugin.add([
        'page_topic_reply',
        'advpage_topic_reply'
    ],function(feed, gettext, gettextCatalog){
        feed.getItemUrl =  function(){
            if(feed.sParams && feed.sParams.child_id) {
                return '#/app/page_topic/' + feed.sParams.child_id;
            }
            if(feed.aAttachments.length && feed.aAttachments[0].iTopicId) {
                return '#/app/page_topic/' + feed.aAttachments[0].iId;
            }
            return '#/app/' + this.getItemType() + '/' + this.getItemId();
        };

        feed.getItemTitle = function(){
            return gettextCatalog.getString('topic');
        };

        return sprintf(gettextCatalog.getString('%1$s replied to a %2$s in the page %3$s'), feed.getPosterLink(), feed.getItemLink(), feed.getObjectLink());
    });

    AttachmentTplPlugin.add(['pages', 'advpage'], PageAttachmentView);
    AttachmentTplPlugin.add(['page_post', 'advpage_post'], PagePostAttachmentTpl);
    AttachmentTplPlugin.add(['page_topic', 'advpage_topic'], PageTopicAttachmentTpl);

    // missing advpage_topic
});