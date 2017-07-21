define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {

    return Model.extend({
        idAttribute: 'iPostId',
        sModelType: 'forum_post',
        canQuote: function() {
            return this.bCanQuote || false;
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
            return this.iForumId || 0;
        },
        getPosterTotalPost: function() {
            return parseInt(this.iTotalPost) || 0;
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
        getThreadId: function() {
            return parseInt(this.iThreadId) || 0;
        },
        getThreadTitle: function() {
            return this.sTitle || '';
        },
        getThreadUrl: function() {
            return '#/app/forum_thread/' + this.getThreadId();
        },
        hasAttachments: function() {
            return this.getAttachments().length > 0;
        }
    });
});