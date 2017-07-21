define([
    'global/base/Model',
], function(Model) {
    return Model.extend({
        idAttribute : 'iPollId',
        sModelType : 'poll',
        bCanView: true,
        getTitle: function() {
            return this.sQuestion || '';
        },
        getVoteCount: function(){
            return this.iTotalVotes || 0;
        },
        getViewCount: function(){
            return this.iTotalView  || 0;
        },
        isClosed: function(){
            return this.bIsClosed || 0;
        },
        hasDescription: function(){
            return this.sDescription? 1:0;
        },
        getOptions: function(){
            var aOptions = this.aAnswer || [];

            aOptions.map(function(aOption) {
                aOption.sTotalVote = aOption.iTotalVotes + (aOption.iTotalVotes == 1 ? ' vote' : ' votes');
                return aOption;
            });

            return aOptions;
        },
        canVote: function(){
            return (this.bCanVote && this.bIsApproved) || false;
        },
        isApproved: function(){
            return this.bIsApproved || false;
        },
        isVoted: function(){
            return this.bIsVoted || false;
        },
        getImageSrc : function() {
            return this.sImagePath || '';
        },
        getVoted: function() {
            return this.aVoted || [];
        },
        canViewResults: function() {
            return this.bCanViewResult || false;
        },
        canViewUsersResults: function() {
            return this.bCanViewUserResult || false;
        },
        getTotalVote: function() {
            return parseInt(this.iTotalVotes) || 0;
        }
    });
}); 