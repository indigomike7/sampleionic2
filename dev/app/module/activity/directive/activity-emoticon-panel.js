define([
    'text!tpl/activity/activity-emoticon-panel.html'
], function() {

    return function() {

        return {
            restrict: 'E',
            template: require('text!tpl/activity/activity-emoticon-panel.html')
        };
    }
});