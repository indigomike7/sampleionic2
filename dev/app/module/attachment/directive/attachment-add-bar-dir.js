define([
    'attachment/controller/attachment-add',
    'text!tpl/attachment/attachment-add-bar-dir.html'
], function(AttachmentAddCtrl, attachmentAddBarDirTpl) {

    return function() {
        
        return {
            restrict: 'E',
            template: attachmentAddBarDirTpl,
            controller: AttachmentAddCtrl
        };
    }
});