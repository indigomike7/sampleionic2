define([
    'text!tpl/activity-attachment/attachment-add-bar-dir.html'
], function() {

    return function() {
        
        return {
            restrict: 'E',
            template: require('text!tpl/activity-attachment/attachment-add-bar-dir.html'),
            controller: 'ActivityAttachmentAddCtrl'
        };
    }
});