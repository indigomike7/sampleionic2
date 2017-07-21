define([
    'global/base/Model',
    'global/service/site'
], function(Model, Site) {

    return Model.extend({
        idAttribute: 'user_id',
        sModelType: 'user',
        getId: function() {
            return this[this.idAttribute] || this.iUserId;
        },
        getTitle: function() {
            return this.full_name || this.sTitle || '';
        },
        getImageSrc: function() {
            return this.avatar || this.sPhotoUrl || '';
        },
        getStatus: function() {
            return this.status || 'offline';
        },
        isUnread: function() {

            var unreadIds = JSON.parse(localStorage.getItem('ynchatUnreadIds')) || [];

            return unreadIds.indexOf(parseInt(this.getId())) > -1;
        },
        isHidden: function() {
            return this.bIsHidden || false;
        },
        setHidden: function(bool) {
            this.bIsHidden = bool || false;
        },
        getAgent: function() {
            return this.agent || '';
        }
    });
});