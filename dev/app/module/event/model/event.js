define([
    'global/viewer',
    'global/base/Model',
    'global/site',
    'moment'
], function(Viewer, Model, Site, Moment) {
    return Model.extend({
        idAttribute: 'iEventId',
        sModelType: 'event',
        getImageSrc: function() {
            return this.sFullPhotoUrl || this.sPhotoUrl || this.sEventBigImageUrl || this.sEventImageUrl || '';
        },
        getTotalMember: function() {
            return parseInt(this.iNumOfMember) || 0;
        },
        getUserTimezone: function() {
            return this.sUserTimezone || 0;
        },
        getStartTimestamp: function() {
            return this.iStartTime * 1000 || 0;
        },
        getStartTimeFormatted: function(format) {
            format = format || 'lll';
            return Moment(this.getStartTimestamp()).zone(-this.getUserTimezone()).format(format);
        },
        getEndTimestamp: function() {
            return this.iEndTime * 1000 || 0;
        },
        getEndTimeFormatted: function(format) {
            format = format || 'lll';
            return Moment(this.getEndTimestamp()).zone(-this.getUserTimezone()).format(format);
        },
        getLocation: function() {
            return this.sLocation || '';
        },
        isMember: function() {
            return this.bIsMember || false;
        },
        getRSVP: function() {
            return parseInt(this.iRsvp);
        },
        setRSVP: function(iRsvp) {
            this.iRsvp = iRsvp;
        },
        isInvited: function() {
            return this.bIsInvited || false;
        },
        isResourceApproval: function() {
            return parseInt(this.bIsResourceApproval) || false;
        },
        isOnRequest: function() {
            return this.bOnRequest || false;
        },
        getHost: function() {
            return this.sHost || '';
        },
        getCategory: function() {
            return this.sCategory || '';
        },
        getCategoryId: function() {
            return this.iCategory || '';
        },
        getPrivacy: function() {
            return this.sViewPrivacy || 'everyone';
        },
        getCommentPrivacy: function() {
            return this.sCommentPrivacy || 'everyone';
        },
        getTotalGuest: function() {
            return parseInt(this.aGuestStatistic.iNumAll) || 0;
        },
        getTotalGoing: function() {
            return parseInt(this.aGuestStatistic.iNumGoing) || 0;
        },
        getTotalMaybe: function() {
            return parseInt(this.aGuestStatistic.iNumMaybe) || 0;
        },
        getTotalNotAttend: function() {
            return parseInt(this.aGuestStatistic.iNumNotAttending) || 0;
        },
        getGoingList: function() {
            return this.aGuestList.going || [];
        },
        getMaybeList: function() {
            return this.aGuestList.maybe || [];
        },
        getNotAttendList: function() {
            return this.aGuestList.notAttend || [];
        },
        canInvite: function() {
            return this.bCanInvite || false;
        }
    });
});
