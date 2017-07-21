define([
    'global/base/Model'
], function(Model) {

    return Model.extend({
        idAttribute: 'iForumId',
        sModelType: 'forum',
        canAddThread: function() {
            return this.bCanAddThread || false;
        },
        getSubForums: function() {
            return this.aSubForum || [];
        },
        getTitle: function() {
        	return this.sName || '';
        },
        getTotalPost: function() {
            return parseInt(this.iTotalPost) || 0;
        },
        getTotalSubForum: function() {
            return parseInt(this.iTotalSubForum) || 0;
        },
        getTotalThread: function() {
            return parseInt(this.iTotalThread) || 0;
        },
        isCategory: function() {
            return this.bIsCategory || false;
        }
    });
});