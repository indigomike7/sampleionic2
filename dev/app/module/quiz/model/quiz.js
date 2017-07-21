define([
    'global/base/Model',
], function(Model) {
    return Model.extend({
        idAttribute : 'iQuizId',
        sModelType : 'quiz',
        bCanView: true,
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
            return (!this.bIsVoted && this.bIsApproved) || false;
        },
        isApproved: function(){
            return this.bIsApproved || false;
        },
        isVoted: function(){
            return this.bIsVoted || false;
        },
        getImageSrc : function() {
            return this.sImageUrl || '';
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
        getTotalQuestion: function() {
            return this.iQuestionCount || 0;
        },
        getTotalTaker: function() {
            return this.iTakerCount || 0;
        },
        canTake: function() {
            return this.bCanTake || 0;
        },
        hasCustomImage: function() {
            return this.bHasCustomImage || 0;
        }
    });
}); 