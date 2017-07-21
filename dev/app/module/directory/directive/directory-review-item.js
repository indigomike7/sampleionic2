define([
    'text!tpl/directory/directory-review-item.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryReviewItemCtrl'
        };
    };
});
