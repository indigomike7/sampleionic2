define([
    'global/base/Model',
    'global/site',
    'global/viewer',
    'moment'
], function(Model, Site, Viewer, Moment) {

    return Model.extend({
        idAttribute: 'iMessageId',
        sModelType: 'cometchat_message',
        getSenderId: function() {
            return this.iSenderId || 0;
        },
        getSenderImageSrc: function() {
            return this.sSenderImage || '';
        },
        getSenderUrl: function() {
            return '#/app/user/' + this.getSenderId();
        },
        getTimeFormatted: function() {
            return Moment(this.getTimestamp()).format('L hh:mmA');
        },
        getMessage: function() {
            var sMessage = this.sMessage || '&nbsp;';
            return sMessage.replace(/<img([^\/]+)src="\//ig, '<img$1src="' + Site.siteHost + '/');
        },
        isViewer: function() {
            return this.getSenderId() == Viewer.get('iUserId');
        },
        getStatus: function() {
            return this.sStatus || '';
        },
        setStatus: function(status) {
            this.sStatus = status || '';
        }
    });
});