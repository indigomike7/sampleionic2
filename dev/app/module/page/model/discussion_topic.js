define([
    'global/base/Model',
    'user/model/user'
], function(Model, Model) {

    return Model.extend({
        idAttribute: 'iTopicId',
        sModelType: 'page_topic',
        iForumId: 0,
        iPageId: 0,
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
            return '#/app/page_topic/' + this.getId();
        },
        getLastUserLink: function () {
            return '<a href="#/app/user/' + this.get('iLastUserId') + '">' + this.get('sLastUserName') + '</a>';
        },
        getLastPoster: function() {
            return $.extend({}, Model, {
                iUserId: this.iLastUserId || 0,
                sUserName: this.sLastUserName || ''
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
        getPageId: function(){
            return this.iPageId;
        }
    });
});