define([
    'global/base/Model',
    'global/site',
    'global/helper'
],function(Model, Site, Helper){
    return Model.extend({
        idAttribute: 'iPageId',
        sModelType: 'pages',
        bCanLike: true,
        getImageSrc: function(){
            return this.sPhotoUrl || this.sPageImageUrl || '';
        },
        getMemberCount: function(){
            return this.iMemberCount || 0;
        },
        getMembers: function(){
            return this.aMembers || [];
        },
        isMember : function() {
            return this.actions.bIsMember || false;
        },
        isNeedApprove: function (){
            return this.actions.bIsNeedApprove || false;
        },
        isOfficer: function(){
            return this.actions.bOfficer || false;
        },
        isInvited: function(){
            return this.actions.bIsInvited || false;
        },
        isRequestedMembership: function(){
            return this.actions.bRequestedMembership;
        },
        getNumberOfGuest: function() {
            return this.iGuestCount || 0;
        },
        getNumberOfMember: function() {
            return this.iMemberCount|| 0;
        },
        getOfficers: function(){
            return this.aOfficers || [];
        },
        canInvite: function(){
            return this.bCanInvite?1: 0;
        },
        canAddPhoto: function() {
            return this.bCanUploadPhoto? 1 : 0;
        },
        canAddEvent: function() {
            return this.bCanCreateEvent ? 1 : 0;
        },
        getDescription: function() {
            return this.sText || '';
        },
        getCleanDescription: function() {
            return Helper.prepareHTMLText(Helper.stripTags(this.getDescription()));
        }
    });
});
