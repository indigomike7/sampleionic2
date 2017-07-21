define([
    'search/controller/search-result-list',
    'text!tpl/search/search-result-list.html'
], function(Ctrl, text) {

    return function() {
        return {
            restrict: 'E',
            template: text,
            controller: Ctrl
        };
    };
});