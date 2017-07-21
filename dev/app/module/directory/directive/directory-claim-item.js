define([
    'text!tpl/directory/directory-claim-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryClaimItemCtrl'
        };
    };
});
