define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {
    return Model.extend({
        idAttribute: 'iBlogId',
        sModelType: 'blog',
        getId: function() {
            return this[this.idAttribute];
        },
        canView: function() {
            return this.bCanView || false;
        },
        canCreate: function() {
            return this.bCanView && this.bCanCreate;
        },
        canEdit: function() {
            return this.bCanEdit || false;
        },
        canDelete: function() {
            return this.bCanDelete || false;
        },
        getCategory: function() {
            return this.sCategory || '';
        },
        isPublish: function() {
            return this.bIsPublish || false;
        },
        isApproved: function() {
            return this.bIsApproved || false;
        },
        getTotalView: function() {
            return this.iTotalView || 0;
        },
        isSubscribe: function() {
            return this.bIsSubscribed || false;
        },
        setSubscribe: function() {
            this.bIsSubscribed = true;
        },
        unsetSubscribe: function() {
            this.bIsSubscribed = false;
        },
        canPublish: function() {
            return this.isOwner();
        },
        setPublish: function() {
            this.bIsPublish = true;
        },
        getText: function() {
            return this.sText || '';
        },
        getTextParsed: function() {
            return Helper.prepareHTMLText(this.getText());
        },
        getTextNotParsed: function() {
            return Helper.unescapeHTML(this.sTextNotParsed || '');
        },
        getCleanText: function() {
            return this.getText().replace(/(<([^>]+)>)/ig,"");
        },
        getUserSettingApproveBlogs: function() {
            return this.bUserSettingApproveBlogs || false;
        },
        canComment: function() {
            return (!this.canView() || !this.isApproved() || (!this.bCanComment && !this.bCanPostComment)) ? 0 : 1;
        },
        getAttachments: function() {
            return this.aAttachment || [];
        },
        hasAttachments: function() {
            return this.getAttachments().length > 0;
        },
        getAttachmentPhotos: function() {
            return this.getAttachments().filter(function(att) {
                return att.type == 'image';
            });
        },
        getAttachmentLinks: function() {
            return this.getAttachments().filter(function(att) {
                return att.type == 'link';
            });
        },
        getPrivacy: function() {
            return this.iPrivacy || 0;
        },
        getPrivacyComment: function() {
            return this.iPrivacyComment || 0;
        },
        getSelectedCategories: function() {
            return this.aSelectedCategories || [];
        }
    });
});