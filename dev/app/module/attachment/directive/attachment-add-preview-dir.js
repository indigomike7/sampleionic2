define([
    'text!tpl/attachment/attachment-add-preview-dir.html'
], function(attachmentAddPreviewDirTpl) {

    return function() {
        
        return {
            restrict: 'E',
            template: attachmentAddPreviewDirTpl
        };
    }
});