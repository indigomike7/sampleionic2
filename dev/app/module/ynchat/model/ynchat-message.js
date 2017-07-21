define([
    'global/base/Model',
    'global/helper',
    'global/viewer',
    'global/site',
    'moment'
], function(Model, Helper, Viewer, Site, Moment) {

    return Model.extend({
        idAttribute: 'iMessageId',
        sModelType: 'ynchat_message',
        getSenderId: function() {
            return this.iSenderId || 0;
        },
        getReceiverId: function() {
            return this.iReceiverId || 0;
        },
        getSenderImageSrc: function() {
            var sImageSrc = this.avatar || '';
            return sImageSrc.match(/^http/ig) ? sImageSrc : (Site.siteUrl + sImageSrc);
        },
        getSenderUrl: function() {
            return '#/app/user/' + this.getSenderId();
        },
        getTimeFormatted: function() {
            return Moment(this.getTimestamp()).format('L hh:mmA');
        },
        getTime: function() {
            return this.sTime || '';
        },
        getDate: function() {
            return this.sDate || '';
        },
        getMessage: function() {
            return sMessage = Helper.prepareHTMLText(this.sText || '');
        },
        getType: function() {
            return this.sType || this.type || 'text';
        },
        isViewer: function() {
            return this.getSenderId() == Viewer.get('iUserId');
        },
        getStatus: function() {
            return this.sStatus || '';
        },
        setStatus: function(status) {
            this.sStatus = status || '';
        },
        getData: function() {
            var data = this.data || '';
            return JSON.parse(data);
        },
        getFiles: function() {
            return this.files || [];
        },
        getAttachment: function() {
            return this.attachment || {};
        }
    });
});