define([
    
], function() {

    return function($history, $ionicHistory) {

        var link = function(scope, element, attrs) {

            element.bind('click', function(e) {
                $ionicHistory.nextViewOptions({
                    disableAnimate: true,
                    expire: 300
                });

                $history.back();
            });
        };

        return {
            restrict: 'A',
            link: link
        };
    };
});