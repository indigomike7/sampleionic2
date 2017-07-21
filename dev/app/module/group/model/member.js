define([
    'global/base/Model'
],function(Model){
    return Model.extend({
        idAttribute: 'iUserId',
        sModelType: 'user',
        actions: {},
        getUrl: function(){
            return '#/app/'+this.getType()+'/'+ this.getId();
        },
        getGroupId: function(){
            return this.actions.iGroupId;
        },
        isOwner : function() {
            return this.actions.bIsOwner || 0;
        },
        isMember : function() {
            return this.actions.bIsMember || 0;
        },
        isNeedApprove: function (){
            return this.actions.bIsNeedApprove || 0;
        },
        isOfficer: function(){
            return this.actions.bOfficer || 0;
        },
        isInvited: function(){
            return this.actions.bIsInvited || 0;
        },
        isRequestedMembership: function(){
            return this.actions.bRequestedMembership;
        }
    });
});
