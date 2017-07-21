define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/quiz/quiz-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, QuizAttachmentTpl) {

	HeadlinePlugin.add([
		'comment_quiz',
	], function(feed, gettext, gettextCatalog) {
	    feed.getItemTitle = function(){
	        return gettextCatalog.getString('quiz');
	    };
	    
        return sprintf(gettextCatalog.getString('%1$s commented on %2$s\'s %3$s'), feed.getPosterLink(), feed.getOwnerLink(), feed.getItemLink(22));
    });

    HeadlinePlugin.add([
        'quiz_new',
		'quiz'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s created a new quiz'), feed.getPosterLink());
    });
    

	AttachmentTplPlugin.add(['quiz'], QuizAttachmentTpl);
});