define([], function() {

    return function() {

    	var link = function(scope, element, attrs) {

            element.bind('click', function(e) {
                if (scope.disableClick == 'true') {
                    return;
                }
                scope.showView();
            });
        };

        return {
            restrict: 'A',
            controller: 'PhotoThumbCtrl',
            link: link,
            scope: {
                imgSrc: '@',
                disableZoom: '@',
                disableClick: '@'
            }
        };
    };
});