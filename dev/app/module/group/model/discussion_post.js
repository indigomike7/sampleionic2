define([
    'global/base/Model',
    'global/helper',
    'user/model/user'
], function(Model, Helper, Model) {

    return Model.extend({
        idAttribute: 'iPostId',
        sModelType: 'group_post',
        getCreationDate: function() {
            return this.sCreationDate || '';
        },
        getBody: function() {
            return this.sBody || '';
        },
        getBodyParsed: function() {
            return Helper.prepareHTMLText(this.getBody());
        },
        getAttachedPhotoUrl: function() {
            return this.sPhotoUrl || '';
        },
        canEdit: function() {
            return this.bCanEditPost || false;
        },
        canDelete: function() {
            return this.bCanDeletePost || false;
        },
        getTopicId: function() {
            return this.iTopicId || 0;
        }
    });
});