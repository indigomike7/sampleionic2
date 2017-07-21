define([
    
], function() {

    return function() {

        var link = function(scope, element, attrs) {

            element.bind('click', function(e) {
                scope.toggleRightSideMenu();
            });
        };

        return {
            restrict: 'A',
            link: link
        };
    };
});