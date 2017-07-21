define([
    'photo/controller/photo-list',
    'text!tpl/photo/photo-list.html'
], function(Controller, text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});