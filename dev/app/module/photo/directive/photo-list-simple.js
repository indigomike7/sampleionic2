define([
    'photo/controller/photo-list-simple',
    'text!tpl/photo/photo-list-simple.html'
], function(Controller, text) {

    return function() {

        return {
            restrict: 'E',
            template: text,
            controller: Controller
        };
    };
});