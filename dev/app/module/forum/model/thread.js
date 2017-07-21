define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {

    return Model.extend({
        idAttribute: 'iThreadId',
        sModelType: 'forum_thread',
        canDelete: function() {
            return this.bCanDeleteThread || false;
        },
        canEdit: function() {
            return this.bCanEditThread || false;
        },
        getAttachmentLinks: function() {
            return this.getAttachments().filter(function(att) {
                return att.type == 'link';
            });
        },
        getAttachmentPhotos: function() {
            return this.getAttachments().filter(function(att) {
                return att.type == 'image';
            });
        },
        getAttachments: function() {
            return this.aAttachments || [];
        },
        getForumId: function() {
            return this.iForumId || false;
        },
        getLastPosterId: function() {
            return parseInt(this.iLastUserId) || this.getPosterId();
        },
        getLastPosterTitle: function() {
            return parseInt(this.iLastUserId) ? this.sLastFullname : this.getPosterTitle();
        },
        getLastPosterUrl: function() {
            return parseInt(this.iLastUserId) ? ('#/app/user/' + this.getLastPosterId()) : this.getPosterUrl();
        },
        getOrderId: function() {
            return parseInt(this.iOrderId) || 0;
        },
        getText: function() {
            return this.sText || '';
        },
        getTextClean: function() {
            return this.getText().replace(/(<([^>]+)>)/ig,"");
        },
        getTextNotParsed: function() {
            return Helper.unescapeHTML(this.sTextNotParsed || '');
        },
        getTextParsed: function() {
            return Helper.prepareHTMLText(this.getText());
        },
        getTitle: function() {
            return this.sTitle || '';
        },
        getTotalPost: function() {
            return parseInt(this.iTotalPost) || 0;
        },
        getTotalView: function() {
            return parseInt(this.iTotalView) || 0;
        },
        isAnnouncement: function() {
            return this.bIsAnnouncement || false;
        },
        isClosed: function() {
            return this.bIsClosed || false;
        },
        isSticky: function() {
            return this.getOrderId() ? true : false;
        },
        isSubscribed: function() {
            return this.bIsSubscribed || false;
        },
        hasAttachments: function() {
            return this.getAttachments().length > 0;
        },
        hasPoll: function() {
            return parseInt(this.iPollId) ? true : false;
        }
    });
});