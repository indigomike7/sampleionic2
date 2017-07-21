define([
    'global/base/Model',
    'user/model/user'
], function(Model, UserModel) {

    return Model.extend({
        idAttribute: 'iTopicId',
        sModelType: 'directory_topic',
        iForumId: 0,
        iListingId: 0,
        getTitle: function() {
            return this.sTitle || this.sTopicTitle || '';
        },
        getReplyCount: function() {
            return this.iReplyCount || 0;
        },

        getViewCount: function() {
            return this.iViewCount || 0;
        },

        getURL: function() {
            return '#/app/directory_topic/' + this.getId();
        },
        getLastUserLink: function () {
            return this.getLastPoster().getUrl();
        },
        getLastPoster: function() {
            return $.extend({}, UserModel, {
                iUserId: this.aLastPoster.id || 0,
                sUserName: this.aLastPoster.title || ''
            });
        },
        getLastPostedDate: function() {
            return this.sLastPostedDate || '';
        },
        getTotalReply: function() {
            return this.iReplyCount || 0;
        },
        getTotalView: function() {
            return this.iViewCount || 0;
        },
        isWatching: function() {
            return this.bIsWatching || false;
        },
        isSticky: function() {
            return this.bIsSticky || false;
        },
        isClosed: function() {
            return this.bIsClosed || false;
        },
        canPost: function() {
            return this.bCanPost || false;
        },
        getForumId: function() {
            return this.iForumId || 0;
        },
        getGroupId: function(){
            return this.iListingId;
        }
    });
});