define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/group/group-attachment.html',
    'text!tpl/group/group-post-attachment.html',
    'text!tpl/group/group-topic-attachment.html',
], function(AttachmentTplPlugin, HeadlinePlugin, GroupAttachmentView, GroupPostAttachmentTpl,GroupTopicAttachmentTpl) {

    HeadlinePlugin.add([ 'group_join', 'advgroup_join' ],function(feed, gettext, gettextCatalog){
        return sprintf(gettextCatalog.getString('%1$s joined the group %2$s'), feed.getPosterLink(), feed.getItemLink());
    });

    HeadlinePlugin.add([ 'group_promote', 'advgroup_promote'],function(feed, gettext, gettextCatalog){
        return sprintf(gettextCatalog.getString('%1$s has been made an officer for the group %2$s'), feed.getPosterLink(), feed.getItemLink());
    });

    HeadlinePlugin.add([ 'group_create', 'advgroup_create', 'groups'],function(feed, gettext, gettextCatalog){
        return sprintf(gettextCatalog.getString('%s created a group'), feed.getPosterLink());
    });

    HeadlinePlugin.add([
        'group_topic_create',
        'advgroup_topic_create'
    ],function(feed, gettext, gettextCatalog){

        feed.getItemUrl =  function(){
            if(feed.sParams && feed.sParams.child_id) {
                return '#/app/group_topic/' + feed.sParams.child_id;
            }
            if(feed.aAttachments.length && feed.aAttachments[0].iId) {
                return '#/app/group_topic/' + feed.aAttachments[0].iId;
            }
            return '#/app/' + this.getItemType() + '/' + this.getItemId();
        };

        feed.getItemTitle = function(){
            return gettextCatalog.getString('topic');
        };

        return sprintf(gettextCatalog.getString('%1$s posted a %2$s in the group %3$s'), feed.getPosterLink(), feed.getItemLink(), feed.getObjectLink());
    });

    HeadlinePlugin.add([
        'groups_comment',
    ], function(feed, gettext, gettextCatalog, $state) {

        if (feed.hasParentUser()) {

            if ($state.current.name == 'app.groupDetailActivity') {

                return sprintf(gettextCatalog.getString('%s'), feed.getPosterLink());
            } else {

                return sprintf(gettextCatalog.getString('%1$s &rarr; %2$s'), feed.getPosterLink(), feed.getParentLink(22));
            }
        }

        return feed.getPosterLink();
    });

    HeadlinePlugin.add([
        'group_topic_reply',
        'advgroup_topic_reply'
    ],function(feed, gettext, gettextCatalog){
        feed.getItemUrl =  function(){
            if(feed.sParams && feed.sParams.child_id) {
                return '#/app/group_topic/' + feed.sParams.child_id;
            }
            if(feed.aAttachments.length && feed.aAttachments[0].iTopicId) {
                return '#/app/group_topic/' + feed.aAttachments[0].iId;
            }
            return '#/app/' + this.getItemType() + '/' + this.getItemId();
        };

        feed.getItemTitle = function(){
            return gettextCatalog.getString('topic');
        };

        return sprintf(gettextCatalog.getString('%1$s replied to a %2$s in the group %3$s'), feed.getPosterLink(), feed.getItemLink(), feed.getObjectLink());
    });

    AttachmentTplPlugin.add(['groups', 'advgroup'], GroupAttachmentView);
    AttachmentTplPlugin.add(['group_post', 'advgroup_post'], GroupPostAttachmentTpl);
    AttachmentTplPlugin.add(['group_topic', 'advgroup_topic'], GroupTopicAttachmentTpl);

    // missing advgroup_topic
});