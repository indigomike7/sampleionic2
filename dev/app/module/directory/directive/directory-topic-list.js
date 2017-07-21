define([
    'text!tpl/directory/directory-topic-list.html',
    'directory/controller/directory-topic-list'
], function(text, Ctrl) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: Ctrl
        };
    };
});