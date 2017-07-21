define([
    'text!tpl/activity-attachment/attachment-add-preview-dir.html'
], function() {

    return function() {
        
        return {
            restrict: 'E',
            template: require('text!tpl/activity-attachment/attachment-add-preview-dir.html')
        };
    }
});